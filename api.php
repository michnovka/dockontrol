<?php

set_time_limit(0);

require_once(dirname(__FILE__).'/libs/config.php');
require_once(dirname(__FILE__).'/libs/api_libs.php');
require_once(dirname(__FILE__).'/libs/process_action.php');

/** @var Database4 $db */

$authentication_error = "Authentication error";
$api_action = 'local_relay';
$user = null;

function parseAPIParam($name){
	return !empty($_POST[$name]) ? $_POST[$name] : (!empty($_GET[$name]) ? $_GET[$name] : null);
}

$secret = parseAPIParam('secret');
$username = parseAPIParam('username');
$action = parseAPIParam('action');
$password = parseAPIParam('password');
$duration = parseAPIParam('duration');
$pause = parseAPIParam('pause');
$channel = parseAPIParam('channel');
$hash = parseAPIParam('hash');

$totp = parseAPIParam('totp');
$totp_nonce = parseAPIParam('totp_nonce');
$pin = parseAPIParam('pin');

if(!empty($hash)){
	$api_action = 'phone_control';
	
	if(hash('sha256', $_POST['caller_number'].'|'.$_POST['time'].'|'.PHONE_CONTROL_SECRET) == $hash) {
		$authentication_error = false;
	}else{
		$authentication_error = "Phone control authentication error";
	}
}elseif(!empty($secret)){
	if($secret == API_SECRET) {
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
			
			try{
				_add_to_action_queue('open_gate_rw'.$gate_rw, $user['id'], time());
				_add_to_action_queue('open_garage_'.$user['default_garage'], $user['id'], time()+10);
			} catch (EDatabase $e) {
				$reply['status'] = 'error';
				$reply['message'] = 'Database error';
			}
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

	case 'api_list':
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
				
				$reply['allowed_actions'][] = array(
					'id' => -2,
					'action' => 'enter',
					'type' => 'carenter',
					'name' => 'Car Enter',
					'has_camera' => false,
					'allow_widget' => true,
					'allow_1min_open' => false,
					'icon' => 'enter',
				);
				
				$reply['allowed_actions'][] = array(
					'id' => -1,
					'action' => 'exit',
					'type' => 'carexit',
					'name' => 'Car Exit',
					'has_camera' => false,
					'allow_widget' => true,
					'allow_1min_open' => false,
					'icon' => 'exit',
				);
				
				$name_conflicts = array();

				foreach($buttons as $button){
					if($permissions[$button['permission']])
						$name_conflicts[$button['name']]++;
				}

				foreach ($buttons as $button) {
					if ($permissions[$button['permission']]) {

						$row = array(
							'id' => $button['id'],
							'action' => $button['action'],
							'type' => $button['type'],
							'name' => $button['name'].($name_conflicts[$button['name']] > 1 ? ' '.$button['name_specification'] : ''),
							'has_camera' => !empty($button['camera1']),
							'allow_widget' => true,
							'allow_1min_open' => $button['allow_1min_open'] ? true : false,
							'icon' => $button['icon'],
						);
						
						if($row['has_camera']) {
							$row['cameras'] = [];
							
							$row['cameras'][] = $button['camera1'];

							for($i = 2; $i <= 4; $i++) {
								if (!empty($button['camera'.$i]))
									$row['cameras'][] = $button['camera'.$i];
							}
						}
						
						$reply['allowed_actions'][] = $row;
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
						'icon' => 'nuki',
						'nuki_pin_required' => !empty($nuki['pin']) ? true : false,
					);
				}
			}
			
			if($api_action == 'api_list'){
				foreach ($reply['allowed_actions'] as $action){
					if(!empty($action['action'])) {
						echo $action['action'] . "\n";
						
						if($action['allow_1min_open'])
							echo $action['action'] . "_1min\n";
						
					}
				}
				
				exit;
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
			
			$reply = processAction($api_action, $user, null, $totp, $totp_nonce, $pin);
		} catch (EDatabase $e) {
			$reply['status'] = 'error';
			$reply['message'] = 'Database error';
		}
		break;
}

header('Content-type: text/json');
echo json_encode($reply);
exit;
