<?php

require_once dirname(__FILE__).'/libs/config.php';

_require_login();
if(!_check_permission('admin')){
	header('Location: /');
	exit;
}

$edit_user = array();
$user_groups = array();

if(!empty($_POST['action'])){
	if($_POST['action'] == 'save'){
		$error = array();

		// check for username
		if(!preg_match('/^([a-z0-9]{4,32})$/i',$_POST['username'])){
			$error['username'] = 'Invalid username';
		}elseif($db->fetch('SELECT 1 FROM users WHERE username=? AND id!=#', $_POST['username'], $_POST['id'])){
			$error['username'] = 'Username taken';
		}

		$edit_user['username'] = htmlspecialchars($_POST['username']);


		if($_POST['password'] || !$_POST['id']){
			if(!PasswordTools::checkPasswordStrength($_POST['password'])){
				$error['password'] = 'Use stronger password';
			}
		}

		$edit_user['password'] = htmlspecialchars($_POST['password']);


		if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
			$error['email'] = 'Invalid e-mail';
		}

		$edit_user['email'] = htmlspecialchars($_POST['email']);

		//eliminate every char except 0-9
		$_POST['phone'] = preg_replace("/[^0-9]/", '', $_POST['phone']);

		if (strlen($_POST['phone']) < 9){
			$error['phone'] = "Invalid phone";
		}

		$edit_user['phone'] = htmlspecialchars($_POST['phone']);
		$edit_user['default_garage'] = htmlspecialchars($_POST['default_garage']);

		if (strlen($_POST['name']) < 3){
			$error['name'] = "Invalid name";
		}

		if (trim($_POST['apartment']) && !preg_match('/Z[0-9]\.B[0-9]\.[0-9]{3}/i',$_POST['apartment'])){
			$error['apartment'] = "Use format ZX.BY.NNN for apartment";
		}

		$edit_user['name'] = htmlspecialchars($_POST['name']);
		$edit_user['enabled'] = !empty($_POST['enabled']);

		if (empty($_POST['groups'])){
			$error['groups'] = "Choose at least one group";
		}

		$user_groups = $_POST['groups'];

		if(empty($error)){
			$smarty->assign('success', 'Successfully saved');

			// save all except pwd
			if(!$_POST['id']){
				// create new user
				$db->query('INSERT INTO users SET username=?, password=?,name=?,created=NOW(),enabled=#,has_camera_access=#,apartment=?,default_garage=?,email=?,phone=?', $_POST['username'],PasswordTools::getHashedPassword($_POST['password']), $_POST['name'], $_POST['enabled'] ? 1 : 0, $_POST['has_camera_access'] ? 1 : 0, $_POST['apartment'],$_POST['default_garage'], $_POST['email'], $_POST['phone']);
				$_GET['id'] = $_POST['id'] = $db->lastinsertid();
			}else{
				// update user
				$db->query('UPDATE users SET username=?, name=?,enabled=#,has_camera_access=#,apartment=?,default_garage=?,email=?,phone=? WHERE id=#', $_POST['username'], $_POST['name'], $_POST['enabled'] ? 1 : 0,  $_POST['has_camera_access'] ? 1 : 0, $_POST['apartment'],$_POST['default_garage'], $_POST['email'], $_POST['phone'], $_POST['id']);

				if($_POST['password']) {
					// save pwd
					$db->query('UPDATE users SET password = ? WHERE id= #', PasswordTools::getHashedPassword($_POST['password']), $_POST['id']);
				}
			}

			// save groups
			$db->query('DELETE FROM user_group WHERE user_id=#', $_POST['id']);
			foreach ($_POST['groups'] as $g){
				$db->query('INSERT INTO user_group SET user_id=#, group_id=#', $_POST['id'], $g);
			}

		}else{
			$smarty->assign('error_message', implode('. ', $error));
			$smarty->assign('error', $error);
		}

	}elseif($_POST['action'] == 'delete'){
		$db->query('DELETE FROM users WHERE id=#', $_POST['id']);
		header('Location: admin_users.php');
		exit;
	}
}

$user = _get_user_array();

$smarty->assign('user', $user);
$smarty->assign('permissions', _get_permissions());

if($_GET['id'])
	$edit_user = _get_user_array($_GET['id']);

$smarty->assign('edit_user', $edit_user);

$groups  = null;
$db->queryall('SELECT * FROM groups ORDER BY name', $groups);

$smarty->assign('groups', $groups);

if($_GET['id']) {
	$db->queryall('SELECT group_id FROM user_group WHERE user_id=#', $user_groups, 'group_id', $edit_user['id']);
}

$smarty->assign('user_groups', $user_groups);

$smarty->display('admin_edit_user.tpl');

?>