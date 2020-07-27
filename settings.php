<?php

require_once dirname(__FILE__).'/libs/config.php';

//error_reporting(E_ALL);
//ini_set('display_errors','1');

_require_login();

if(!empty($_POST['action'])) {

	$user = _get_user_array();

	switch ($_POST['action']) {

		case 'change_contacts':
			$error ='';

			if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
				$error .= 'Invalid e-mail. ';
			}

			//eliminate every char except 0-9
			$phone = preg_replace("/[^0-9]/", '', $_POST['phone']);

			if (strlen($phone) < 9){
				$error .= "Invalid phone. ";
			}

			if (strlen($_POST['name']) < 3){
				$error .= "Invalid name. ";
			}

			if(!$error){
				$db->query('UPDATE users SET phone = ?, email=?, name=? WHERE id= #',$phone, $_POST['email'], $_POST['name'], $user['id']);
				$smarty->assign('success', 'Contacts updated');
			}else{
				$smarty->assign('error', $error);
			}

			break;
		case 'change_password':

			if(!PasswordTools::checkPasswordStrength($_POST['password'])){
				$smarty->assign('error', 'Use stronger password');
			}elseif($_POST['password'] != $_POST['password2']){
				$smarty->assign('error','Passwords do not match');
			}else{
				$db->query('UPDATE users SET password = ? WHERE id= #', PasswordTools::getHashedPassword($_POST['password']), $user['id']);
				$smarty->assign('success', 'Password changed');
			}

			break;
		case 'save_other_settings':
			$db->query('UPDATE users SET geolocation_enabled = # WHERE id= #', $_POST['geolocation_enabled'] ? 1 : 0, $user['id']);
			$smarty->assign('success', 'Settings saved');
			break;
	}

}

$user = _get_user_array();

$smarty->assign('user', $user);
$smarty->assign('permissions', _get_permissions());

$nukis = array();
$db->queryall('SELECT * FROM nuki WHERE user_id=#', $nukis,'', $user['id']);
$smarty->assign('nukis', $nukis);

$smarty->display('settings.tpl');

?>