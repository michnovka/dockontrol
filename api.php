<?php

set_time_limit(0);

require_once(dirname(__FILE__).'/libs/config.php');
require_once(dirname(__FILE__).'/libs/api_libs.php');
require_once(dirname(__FILE__).'/libs/process_action.php');

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
$hash = !empty($_POST['hash']) ? $_POST['hash'] : (!empty($_GET['hash']) ? $_GET['hash'] : null);

if(!empty($hash)){
	$api_action = 'phone_control';
	
	if(hash('sha256', $_POST['caller_number'].'|'.$_POST['time'].'|'.$_PHONE_CONTROL_SECRET) == $hash) {
		$authentication_error = false;
	}else{
		$authentication_error = "Phone control authentication error";
	}
}elseif(!empty($secret)){
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
	case 'phone_control':
		// fetch user by phone
		$phone = preg_replace("/[^0-9]/", '', $_POST['caller_number']);
		
		$user = $db->queryfirst('SELECT u.* FROM phone_control p INNER JOIN users u on p.user_id = u.id WHERE p.phone = # LIMIT 1', $phone);
		
		if(!empty($user)){
			$reply['action'] = 'hung-up';
			// open garage, gate, garage
			
			
			if(!defined('_IS_API'))
				define('_IS_API', true);
			
			$gate_rw = getRWFromGarage($user['default_garage']);
			
			_add_to_action_queue('open_garage_'.$user['default_garage'], $user['id'], time());
			_add_to_action_queue('open_gate_rw'.$gate_rw, $user['id'], time()+5);
			_add_to_action_queue('open_garage_'.$user['default_garage'], $user['id'], time()+10);
			
		}
		
		$db->query('INSERT INTO phone_control_log SET user_id='.(!empty($user) ? intval($user['id']) : 'NULL' ).', time=NOW(), phone=#', $phone);
		
		break;
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

				$name_conflicts = array();

				foreach($buttons as $button){
					if($permissions[$button['permission']])
						$name_conflicts[$button['name']]++;
				}

				foreach ($buttons as $button) {
					if ($permissions[$button['permission']]) {

						$reply['allowed_actions'][] = array(
							'id' => $button['id'],
							'action' => $button['action'],
							'type' => $button['type'],
							'name' => $button['name'].($name_conflicts[$button['name']] > 1 ? ' '.$button['name_specification'] : ''),
							'has_camera' => !empty($button['camera1']),
							'allow_widget' => true,
						);
					}
				}
			}

			$nukis = null;
			$db->queryall('SELECT * FROM nuki WHERE user_id=#', $nukis,'', $user['id']);

			if(!empty($nukis)){
				foreach ($nukis as $nuki){

					$reply['allowed_actions'][] = array(
						'id' => 'nuki_'.$nuki['id'],
						'action' => null,
						'type' => 'nuki',
						'name' => $nuki['name'],
						'can_lock' => $nuki['can_lock'] ? true : false,
						'has_camera' => false,
						'allow_widget' => false,
					);
				}
			}


		} catch (EDatabase $e) {
			$reply['status'] = 'error';
			$reply['message'] = 'Database error';
		}

		break;

	default:
		try {

			if(!defined('_IS_API'))
				define('_IS_API', true);

			$reply = processAction($api_action, $user);
		} catch (EDatabase $e) {
			$reply['status'] = 'error';
			$reply['message'] = 'Database error';
		}
		break;
}

echo json_encode($reply);
exit;
