<?php

require_once dirname(__FILE__).'/libs/config.php';

if(empty($_SESSION['id']))
	_restore_remember_me_cookie();

if(!empty($_SESSION['id'])){
	header('Location: /');
	exit;
}
$signup_code = $db->queryfirst('SELECT * FROM signup_codes WHERE hash=? AND expires > NOW() LIMIT 1', $_GET['key']);

if(empty($signup_code)){
	echo "Invalid signup key";
	exit;
}

$edit_user = array('apartment' => $signup_code['apartment_mask']);

if(!empty($_POST['action'])){
	if($_POST['action'] == 'save'){
		$error = array();

		// check for username
		if(!preg_match('/^([a-z0-9\.]{4,32})$/i',$_POST['username'])){
			$error['username'] = 'Invalid username';
		}elseif($db->fetch('SELECT 1 FROM users WHERE username=?', $_POST['username'])){
			$error['username'] = 'Username taken';
		}

		$edit_user['username'] = htmlspecialchars($_POST['username']);


		if(!PasswordTools::checkPasswordStrength($_POST['password'])){
			$error['password'] = 'Use stronger password';
		}elseif($_POST['password'] != $_POST['password2']){
			$error['password2'] = 'Passwords do not match';
		}


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

		if (strlen($_POST['name']) < 3){
			$error['name'] = "Invalid name";
		}

		if (!preg_match('/^Z([0-9])\.B([0-9])\.([0-9]{3})$/i',$_POST['apartment'], $apartment_parts)){
			$error['apartment'] = "Use format ZX.BY.NNN for apartment";
		}elseif(!preg_match('/^'.$signup_code['apartment_mask'].'/i', $_POST['apartment'])){
			$error['apartment'] = "The apartment code is not allowed. Use format ZX.BY.NNN and allowed mask is ".$signup_code['apartment_mask'];
		}

		$edit_user['apartment'] = htmlspecialchars($_POST['apartment']);
		$edit_user['name'] = htmlspecialchars($_POST['name']);

		if(empty($error)){

			$default_garage = 'z9';

			$group_id = null; // z9.b2

			if(!empty($apartment_parts)){
				$default_garage = 'z'.$apartment_parts[1];
				$group_id = $db->fetch('SELECT id FROM groups WHERE name = ?', 'Z'.$apartment_parts[1].'.B'.$apartment_parts[2]);
			}

			if(!$group_id){
				$smarty->assign('error_message', 'Invalid apartment code, use ZX.BY.NNN');
			}else {

				// create new user
				$db->query('INSERT INTO users SET username=?, password=?,name=?,created=NOW(),enabled=#,apartment=?,default_garage=?,email=?,phone=?,created_by=#', $_POST['username'], PasswordTools::getHashedPassword($_POST['password']), $_POST['name'], 1, $_POST['apartment'], $default_garage, $_POST['email'], $_POST['phone'], $signup_code['admin_id']);
				$user_id = $db->lastinsertid();

				// save groups
				$db->query('INSERT INTO user_group SET user_id=#, group_id=#', $user_id, $group_id);

				$db->query('UPDATE signup_codes SET signups_count = signups_count+1 WHERE hash = ?', $signup_code['hash']);

				header('Location: login.php?username=' . urlencode($_POST['username']));
				exit;
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

$smarty->assign('edit_user', $edit_user);

$smarty->display('signup.tpl');

?>