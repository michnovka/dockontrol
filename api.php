<?php

set_time_limit(0);

require_once(dirname(__FILE__).'/libs/config.php');
require_once(dirname(__FILE__).'/libs/api_libs.php');
require_once(dirname(__FILE__).'/libs/process_action.php');
require_once(dirname(__FILE__).'/config/API_SECRET.php');

$authentication_error = "Authentication error";
$api_action = 'local_relay';
$user = null;

if(!empty($_GET['secret'])){
	if($_GET['secret'] == $_SECRET) {
		$authentication_error = false;
	}else{
		$authentication_error = "Local relay authentication error";
	}
}elseif(!empty($_GET['username'])){

	$api_action = $_GET['action'];

	$authentication_error = checkAPILoginAuthenticationError($_GET['username'], $_GET['password'], $_SERVER['REMOTE_ADDR'], $api_action, $user);

}

if($authentication_error){
	_log_authentication_error($api_action, $authentication_error);
}

$reply = array();

switch ($api_action){
	case 'local_relay':
		$channel = intval($_GET['channel']);

		if($channel > 8 || $channel < 1){
			APIError("Invalid channel. Min 1, max 8", 1);
			exit;
		}

		$output = DoAction($channel, $_GET['action'], $_GET['duration'], $_GET['pause']);

		$reply['status'] = 'ok';
		$reply['output'] = $output;

		break;
	default:
		$reply = processAction($api_action, $user);
		break;
}

echo json_encode($reply);
exit;
