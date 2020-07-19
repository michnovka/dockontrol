<?php

set_time_limit(0);

require_once(dirname(__FILE__).'/libs/config.php');
require_once(dirname(__FILE__).'/libs/api_libs.php');
require_once(dirname(__FILE__).'/libs/process_action.php');
require_once(dirname(__FILE__).'/config/API_SECRET.php');

function APIError($message, $code){
        echo json_encode(array('status' => 'error', 'code' => $code, 'message' => $message));
}

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

	// check IP bruteforce
	$is_bruteforce = $db->fetch('SELECT IF(COUNT(*) > 10, 1, 0) FROM api_calls_failed WHERE ip=? AND time > DATE_SUB(NOW(), INTERVAL 5 MINUTE )', $_SERVER['REMOTE_ADDR']);

	if($is_bruteforce){
		$authentication_error = 'Too many tries. Repeat in 5 mins';
	}else {
		// check username and pwd
		$user = $db->queryfirst('SELECT * FROM users WHERE username=? AND enabled=1 LIMIT 1', $_GET['username']);

		if(empty($user) || !PasswordTools::checkPassword($_GET['password'], $user['password'])){
			$authentication_error = 'Invalid username or password';
		}else{
			$db->query('INSERT INTO api_calls SET user_id=#, time=NOW(), ip=?, api_action = ?', $user['id'], $_SERVER['REMOTE_ADDR'], $api_action);
			$authentication_error = false;
		}
	}

}

if($authentication_error){
	try {
		$db->query('INSERT INTO api_calls_failed SET username=?, time=NOW(), ip=?, api_action=?', '', $_SERVER['REMOTE_ADDR'], $api_action);
	}catch(EDatabase $e){
		// do nothing
	}

	APIError($authentication_error, 403);
	exit;
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
