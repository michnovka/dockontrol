<?php

set_time_limit(0);

require_once(dirname(__FILE__).'/libs/config.php');
require_once(dirname(__FILE__).'/libs/api_libs.php');
require_once(dirname(__FILE__).'/libs/process_action.php');
require_once(dirname(__FILE__).'/config/API_SECRET.php');

/** @var Database4 $db */
/** @var string $_SECRET */

$authentication_error = "Authentication error";
$api_action = 'local_relay';
$user = null;

$secret = !empty($_POST['secret']) ? $_POST['secret'] : (!empty($_GET['secret']) ? $_GET['secret'] : null);
$username = !empty($_POST['username']) ? $_POST['username'] : (!empty($_GET['username']) ? $_GET['username'] : null);
$action = !empty($_POST['action']) ? $_POST['action'] : (!empty($_GET['action']) ? $_GET['action'] : null);
$password = !empty($_POST['password']) ? $_POST['password'] : (!empty($_GET['password']) ? $_GET['password'] : null);
$duration = !empty($_POST['duration']) ? $_POST['duration'] : (!empty($_GET['duration']) ? $_GET['duration'] : null);
$pause = !empty($_POST['pause']) ? $_POST['pause'] : (!empty($_GET['pause']) ? $_GET['pause'] : null);
$channel = !empty($_POST['channel']) ? $_POST['channel'] : (!empty($_GET['channel']) ? $_GET['channel'] : null);

if(!empty($secret)){
	if($secret == $_SECRET) {
		$authentication_error = false;
	}else{
		$authentication_error = "Local relay authentication error";
	}
}elseif(!empty($username)){

	$api_action = $action;

	try {
		$authentication_error = checkAPILoginAuthenticationError($username, $password, $_SERVER['REMOTE_ADDR'], $api_action, $user);
	} catch (EDatabase $e) {
		$authentication_error = 'Database error';
	}

}

if($authentication_error){
	_log_authentication_error($api_action, $authentication_error);
}

$reply = array();

switch ($api_action){
	case 'local_relay':
		$channel = intval($channel);

		if($channel > 8 || $channel < 1){
			APIError("Invalid channel. Min 1, max 8", 1);
			exit;
		}

		$output = DoAction($channel, $action, $duration, $pause);

		$reply['status'] = 'ok';
		$reply['output'] = $output;

		break;

	case 'app_login':

		try {
			// at this point user is authorized, so enumerate actions that can be performed
			$reply['status'] = 'ok';
			$reply['allowed_actions'] = array();
			$reply['config'] = array(
				// timeout in seconds
				'timeout' => 10
			);

			$permissions = _get_permissions($user['id']);

			$buttons = array();
			$db->queryall('SELECT * FROM buttons ORDER BY `type`="gate" DESC, `type`="entrance" DESC, `type`="elevator" DESC, sort_index', $buttons);

			if(!empty($buttons)) {
				foreach ($buttons as $button) {
					if ($permissions[$button['permission']]) {

						$action_prefix = 'open_';

						if ($button['type'] == 'elevator')
							$action_prefix = 'unlock_';

						$reply['allowed_actions'][] = array(
							'id' => $button['id'],
							'action' => $action_prefix . $button['id'],
							'type' => $button['type'],
							'name' => $button['name'],
							'has_camera' => !empty($button['camera1']),
							'allow_widget' => true
						);
					}
				}
			}

			$nukis = null;
			$db->queryall('SELECT * FROM nuki WHERE user_id=#', $nukis,'', $user['id']);

			if(!empty($nukis)){
				foreach ($nukis as $nuki){

					$reply['allowed_actions'][] = array(
						'id' => 'nuki_unlock_'.$nuki['id'],
						'action' => 'nuki_unlock_'.$nuki['id'],
						'type' => 'nuki',
						'name' => 'Unlock '.$nuki['name'],
						'has_camera' => false,
						'allow_widget' => true
					);

					if($nuki['can_lock']){
						$reply['allowed_actions'][] = array(
							'id' => 'nuki_lock_'.$nuki['id'],
							'action' => 'nuki_lock_'.$nuki['id'],
							'type' => 'nuki',
							'name' => 'Lock '.$nuki['name'],
							'has_camera' => false,
							'allow_widget' => true
						);
					}
				}
			}


		} catch (EDatabase $e) {
			$reply['status'] = 'error';
			$reply['message'] = 'Database error';
		}

		break;

	default:
		try {
			$reply = processAction($api_action, $user);
		} catch (EDatabase $e) {
			$reply['status'] = 'error';
			$reply['message'] = 'Database error';
		}
		break;
}

echo json_encode($reply);
exit;
