<?php

require_once dirname(__FILE__).'/libs/config.php';

//error_reporting(E_ALL);
//ini_set('display_errors','1');

_require_login();

$edit_nuki = array();

if(!empty($_POST['action'])){
	if($_POST['action'] == 'change_pin') {

		if(!_check_nuki_permission($_GET['id']))
			exit;

		$result = array(
			'status' => 'ok'
		);

		// check pin quality
		if(!preg_match('/^([0-9]{4,10})$/i', $_POST['pin'])){
			$result['status'] = 'error';
			$result['error_pin'] = '1';
		}

		$password1 = $db->fetch('SELECT password1 FROM nuki WHERE user_id=# AND id=# LIMIT 1',$_SESSION['id'], $_GET['id']);

		if($password1 != $_POST['password1']){
			$result['error_password1'] = '1';
			$result['status'] = 'error';
		}

		if($result['status'] != 'error'){
			// do update
			$db->query('UPDATE nuki SET pin = # WHERE id=# AND user_id=#', $_POST['pin'], $_GET['id'], $_SESSION['id']);
		}

		echo json_encode($result);
		exit;
	}elseif($_POST['action'] == 'save'){
		$error = array();

		if(strlen($_POST['name']) < 1) {
			$error['name'] = 'Invalid name';
		}

		$edit_user['name'] = htmlspecialchars($_POST['name']);

		if(strlen($_POST['username']) < 1) {
			$error['username'] = 'Invalid username';
		}

		$edit_user['username'] = htmlspecialchars($_POST['username']);

		if($_POST['password1'] || !$_POST['id']){
			if(strlen($_POST['password1']) < 1){
				$error['password1'] = 'Use stronger password';
			}
		}

		$edit_user['password1'] = htmlspecialchars($_POST['password1']);

		if(!filter_var($_POST['dockontrol_nuki_api_server'], FILTER_VALIDATE_URL)) {
			$error['dockontrol_nuki_api_server'] = 'Invalid API server URL';
		}

		$edit_user['dockontrol_nuki_api_server'] = htmlspecialchars($_POST['dockontrol_nuki_api_server']);

		$edit_user['can_lock'] = !empty($_POST['can_lock']);

		if(empty($error)){
			$smarty->assign('success', 'Successfully saved');

			if(!$_POST['id']){
				// create new user
				$db->query('INSERT INTO nuki SET password1=?,name=?,dockontrol_nuki_api_server=?,username=?,can_lock=#,user_id=#', $_POST['password1'],$_POST['name'], $_POST['dockontrol_nuki_api_server'], $_POST['username'], $_POST['can_lock'] ? 1 : 0, $_SESSION['id']);
				$_GET['id'] = $_POST['id'] = $db->lastinsertid();
			}else{
				// update user
				$db->query('UPDATE nuki SET name=?,dockontrol_nuki_api_server=?,username=?,can_lock=# WHERE id=# AND user_id=#', $_POST['name'], $_POST['dockontrol_nuki_api_server'], $_POST['username'], $_POST['can_lock'] ? 1 : 0, $_POST['id'], $_SESSION['id']);

				if($_POST['password1']) {
					// save pwd
					$db->query('UPDATE nuki SET password1 = ? WHERE id=# AND user_id=#', $_POST['password1'], $_POST['id'], $_SESSION['id']);
				}
			}

		}else{
			$smarty->assign('error_message', implode('. ', $error));
			$smarty->assign('error', $error);
		}

	}elseif($_POST['action'] == 'delete'){
		$db->query('DELETE FROM nuki WHERE id=# AND user_id=#', $_POST['id'], $_SESSION['id']);
		header('Location: /settings.php');
		exit;
	}
}
$user = _get_user_array();

$smarty->assign('user', $user);
$smarty->assign('permissions', _get_permissions());

$nuki = $db->queryfirst('SELECT * FROM nuki WHERE user_id=# AND id=# LIMIT 1',$user['id'], $_GET['id']);


if(!empty($_GET['id'])) {
	if (empty($nuki)) {
		header('Location: /settings.php');
		exit;
	}

	$edit_nuki = $nuki;
}

$smarty->assign('nuki', $nuki);
$smarty->assign('edit_nuki', $edit_nuki);

$smarty->display('nuki_edit.tpl');

?>