<?php

/**
 * @param int $channel
 * @param string $action
 * @param int $duration in microseconds
 * @param int $pause in microseconds
 * @return string
 */
function DoAction($channel, $action, $duration = 0, $pause = 0){
    switch($action){
        case 'ON':
                $output = `sudo /var/www/html/Relay.sh CH$channel ON`;
                break;
        case 'OFF':
                $output = `sudo /var/www/html/Relay.sh CH$channel OFF`;
                break;

        case 'PULSE':
                $duration = intval($duration);
                $output = `sudo /var/www/html/Relay.sh CH$channel ON`;
                $output .= "\nSleeping $duration microseconds...\n";
                usleep($duration);
                $output .= `sudo /var/www/html/Relay.sh CH$channel OFF`;

                break;

        case 'DOUBLECLICK':
                $duration = intval($duration);
                $pause = intval($pause);
                $output = `sudo /var/www/html/Relay.sh CH$channel ON`;
                $output .= "\nSleeping $duration microseconds...\n";
                usleep($duration);
                $output .= `sudo /var/www/html/Relay.sh CH$channel OFF`;
                $output .= "\nSleeping $pause microseconds...\n";
                usleep($pause);
                $output .= `sudo /var/www/html/Relay.sh CH$channel ON`;
                $output .= "\nSleeping $duration microseconds...\n";
                usleep($duration);
                $output .= `sudo /var/www/html/Relay.sh CH$channel OFF`;

                break;

        default:   
                $output = "Unknown action";
    }

    return $output;
}

/**
 * @param string $remote_host
 * @param int $channel
 * @param string $action
 * @param int $duration in microseconds
 * @param int $pause in microseconds
 * @return string
 */
function DoActionRemote($remote_host, $channel, $action, $duration = 0, $pause = 0){
    $url = 'https://'.$remote_host.'/api.php?secret=PASSWORD736dbdyUDBN__d&channel='.intval($channel).'&action='.urlencode($action).'&duration='.intval($duration).'&pause='.intval($pause);

    $output = file_get_contents($url, false, stream_context_create(
    	array(
    		'ssl' => array(
    			'verify_peer' => false,
    			'verify_peer_name' => false,
				'allow_self_signed' => true
			),
			'http' => array(
				'timeout' => 10
			)
		)
	));

    return $output;
}

/**
 * @param string $message
 * @param int $code
 */
function APIError($message, $code){
	echo json_encode(array('status' => 'error', 'code' => $code, 'message' => $message));
}


/**
 * @param $username
 * @param $password
 * @param $api_action
 * @param null $user
 * @return false|string
 * @throws EDatabase
 */
function checkAPILoginAuthenticationError($username, $password, $ip, $api_action, &$user = null){

	global $db;

	$authentication_error = true;

	// check IP bruteforce
	$is_bruteforce = $db->fetch('SELECT IF(COUNT(*) > 10, 1, 0) FROM api_calls_failed WHERE ip=? AND time > DATE_SUB(NOW(), INTERVAL 5 MINUTE )', $ip);

	if($is_bruteforce){
		$authentication_error = 'Too many tries. Repeat in 5 mins';
	}else {
		// check username and pwd
		$user = $db->queryfirst('SELECT * FROM users WHERE username=? AND enabled=1 LIMIT 1', $username);

		if(empty($user) || !PasswordTools::checkPassword($password, $user['password'])){
			$authentication_error = 'Invalid username or password';
		}else{
			$db->query('INSERT INTO api_calls SET user_id=#, time=NOW(), ip=?, api_action = ?', $user['id'], $_SERVER['REMOTE_ADDR'], $api_action);
			$authentication_error = false;
		}
	}

	return $authentication_error;
}

function _log_authentication_error($api_action, $authentication_error){

	global $db;

	try {
		$db->query('INSERT INTO api_calls_failed SET username=?, time=NOW(), ip=?, api_action=?', '', $_SERVER['REMOTE_ADDR'], $api_action);
	}catch(EDatabase $e){
		// do nothing
	}

	APIError($authentication_error, 403);
	exit;
}