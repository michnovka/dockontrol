<?php

set_time_limit(0);

require_once(dirname(__FILE__).'/libs/config.php');
require_once(dirname(__FILE__).'/libs/api_libs.php');
require_once(dirname(__FILE__).'/libs/process_action.php');
require_once(dirname(__FILE__).'/config/API_SECRET.php');

/** @var Database4 $db */

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

			foreach ($buttons as $button){
				if($permissions[$button['permission']]){

					$action_prefix = 'open_';

					if($button['type'] == 'elevator')
						$action_prefix = 'unlock_';

					$reply['allowed_actions'][] = array(
						'id' => $button['id'],
						'action' => $action_prefix.$button['id'],
						'type' => $button['type'],
						'name' => $button['name'],
						'has_camera' => !empty($button['camera1']),
						'allow_widget' => true
					);
				}
			}

		} catch (EDatabase $e) {
			$reply['status'] = 'error';
			$reply['message'] = 'Database error';
		}

		break;

	default:
		$reply = processAction($api_action, $user);
		break;
}

echo json_encode($reply);
exit;
