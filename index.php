<?php

require_once dirname(__FILE__).'/libs/config.php';
require_once dirname(__FILE__).'/libs/process_action.php';

//error_reporting(E_ALL);
//ini_set('display_errors','1');

if(!empty($_POST['action'])){

	if(!_require_login(false)){
	    die(json_encode(array('status' => 'relogin')));
    }

	$user = _get_user_array();

	$result = processAction($_POST['action'], $user);

	die(json_encode($result));
}

_require_login();

$user = _get_user_array();

$smarty->assign('user', $user);
$smarty->assign('permissions', _get_permissions($_SESSION['id'], !array_key_exists('admin', $_GET)));

$smarty->assign('admin_limited_view', !array_key_exists('admin', $_GET));

$smarty->display('index.tpl');

?>