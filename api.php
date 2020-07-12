<?php

set_time_limit(0);

require_once(dirname(__FILE__).'/libs/api_libs.php');
require_once(dirname(__FILE__).'/config/API_SECRET.php');

$channel = intval($_GET['channel']);

function APIError($message, $code){
        echo json_encode(array('status' => 'error', 'code' => $code, 'message' => $message));
}

if($channel > 8 || $channel < 1){
        APIError("Invalid channel. Min 1, max 8", 1);
        exit;
}

if($_GET['secret'] != $_SECRET){
	APIError("Authentication error", 403);
	exit;
}

$output = DoAction($channel, $_GET['action'], $_GET['duration'], $_GET['pause']);

$reply = array();

$reply['status'] = 'ok';
$reply['output'] = $output;

echo json_encode($reply);
exit;
