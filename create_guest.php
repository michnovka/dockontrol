<?php

require_once dirname(__FILE__).'/libs/config.php';

//error_reporting(E_ALL);
//ini_set('display_errors','1');

_require_login();


if(!empty($_POST['action'])) {

	$user = _get_user_array();

	if(!$user['can_create_guests']){
		echo 'Not authorized';
		exit;
	}

	switch ($_POST['action']) {

		case 'create':

			$expires_hours = intval($_POST['expires']);
			$remaining_actions = intval($_POST['remaining_actions']);

			if($remaining_actions < -1 || $remaining_actions > 100)
				$remaining_actions = -1;

			if($expires_hours < 1 || $expires_hours > 168)
				$expires_hours = 1;

			$hash = md5($user['id']." SECRET ".microtime()."AA".$expires_hours."XX".$remaining_actions);
			$db->query('INSERT INTO guests SET user_id=#, hash=?, expires=DATE_ADD(NOW(), INTERVAL # HOUR ), remaining_actions=#',$user['id'], $hash, $expires_hours, $remaining_actions);

			$smarty->assign('success_link', 'https://'.$_SERVER['HTTP_HOST'].'/?guest='.$hash);

			break;
	}

}

$user = _get_user_array();


if(!$user['can_create_guests']){
	echo 'Not authorized';
	exit;
}

$smarty->assign('user', $user);
$smarty->assign('permissions', _get_permissions());

$smarty->display('create_guest.tpl');

?>