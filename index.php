<?php

require_once dirname(__FILE__).'/libs/config.php';
require_once dirname(__FILE__).'/libs/process_action.php';

//error_reporting(E_ALL);
//ini_set('display_errors','1');

if(!empty($_POST['action'])){

	$user = null;

	if(empty($_GET['guest'])) {
		if(!_require_login(false)){
			die(json_encode(array('status' => 'relogin')));
		}
		$user = _get_user_array();
	}else{
		$guest = _require_guest_login($_GET['guest'], false);

		if(empty($guest)){
			die(json_encode(array('status' => 'relogin')));
		}

		$user = _get_user_array($guest['user_id']);
		$user['has_camera_access'] = 0;
	}

	$result = processAction($_POST['action'], $user, $guest);

	die(json_encode($result));
}

$user = null;

if(empty($_GET['guest'])) {
	_require_login();
	$user = _get_user_array();
}else{
	$guest = _require_guest_login($_GET['guest']);
	$user = _get_user_array($guest['user_id']);
	$user['has_camera_access'] = 0;
	$smarty->assign('guest', $guest);
}

$smarty->assign('user', $user);
$smarty->assign('permissions', _get_permissions($user['id'], !array_key_exists('admin', $_GET)));


$gates = array();
$db->queryall('SELECT * FROM buttons WHERE type=\'gate\' ORDER BY sort_index', $gates);

$entrances = array();
$db->queryall('SELECT * FROM buttons WHERE type=\'entrance\' ORDER BY sort_index', $entrances);

$elevators = array();
$db->queryall('SELECT * FROM buttons WHERE type=\'elevator\' ORDER BY sort_index', $elevators);

$smarty->assign('gates', $gates);
$smarty->assign('entrances', $entrances);
$smarty->assign('elevators', $elevators);

$smarty->assign('admin_limited_view', !array_key_exists('admin', $_GET));

$smarty->display('index.tpl');

?>