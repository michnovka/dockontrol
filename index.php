<?php

require_once dirname(__FILE__).'/libs/config.php';

//error_reporting(E_ALL);
//ini_set('display_errors','1');

if(!empty($_POST['action'])){

	if(!_require_login(false)){
	    die(json_encode(array('status' => 'relogin')));
    }

	$user = _get_user_array();

	$status = '';
	$message = '';
	$repeat_times = 0;
	$repeat_miliseconds = 0;

	switch($_POST['action']){

		case 'enter':
		    // gate, 10sec sleep, garage
			if(!_check_permission('gate') || !_check_permission($user['default_garage'].'garage')){
				$message = 'Not authorized';
			} else {
				_add_to_action_queue('open_gate', $_SESSION['id'], time());
				_add_to_action_queue('open_garage_' . $user['default_garage'], $_SESSION['id'], time() + 10);
				//sleep(1);

				if(_check_permission('z9b2elevator') && $user['apartment'] == 'Z9.B2.501'){
					_add_to_action_queue('unlock_elevator_z9b2', $_SESSION['id'], time() + 45);
					_add_to_action_queue('unlock_elevator_z9b2', $_SESSION['id'], time() + 60);
					_add_to_action_queue('unlock_elevator_z9b2', $_SESSION['id'], time() + 75);
				}

				$message = 'Gate and garage opened';
				$status = 'ok';
			}
			break;

		case 'exit':
			if(!_check_permission('gate') || !_check_permission($user['default_garage'].'garage')){
				$message = 'Not authorized';
			} else {
				_add_to_action_queue('open_garage_' . $user['default_garage'], $_SESSION['id'], time());
				_add_to_action_queue('open_gate', $_SESSION['id'], time() + 23);
				_add_to_action_queue('open_gate', $_SESSION['id'], time() + 27);
				//sleep(1);
				$message = 'Garage and gate opened';
				$status = 'ok';
			}
			break;

		case 'open_gate':
		case 'open_gate_1min':
			// gate
			if(!_check_permission('gate') ){
				$message = 'Not authorized';
			} else {
				$now = time();

				_add_to_action_queue('open_gate', $_SESSION['id'], $now);
				//sleep(1);
				$message = 'Gate opened';

				$gate_1min = $_POST['action']  == 'open_gate_1min';

				if($gate_1min){

					for($i = 5; $i < 60; $i+=5) {
						_add_to_action_queue('open_gate', $_SESSION['id'], $now + $i);
					}

					$message = 'Garage opened for 1 min';
				}

				$status = 'ok';
			}
			break;

		case 'unlock_elevator_z9b2':
			// elevator
			if(!_check_permission('z9b2elevator')){
				$message = 'Not authorized';
			} else {
				_add_to_action_queue('unlock_elevator_z9b2', $_SESSION['id'], time());
				//sleep(1);
				$message = 'Elevator unlocked';
				$status = 'ok';
			}
			break;

		case 'open_garage_z9_1min':
		case 'open_garage_z8_1min':
		case 'open_garage_z7_1min':
		case 'open_garage_z7':
		case 'open_garage_z8':
		case 'open_garage_z9':

			preg_match('/^open_garage_z([0-9])(_1min)?/', $_POST['action'], $m);

			$garage_number = $m[1];
			$garage_1min = !!$m[2];

		    // garage z9
			if(!_check_permission('z'.$garage_number.'garage')){
				$message = 'Not authorized';
			} else {

				$now = time();

				_add_to_action_queue('open_garage_z'.$garage_number, $_SESSION['id'], $now);
				$message = 'Garage opened';

				if($garage_1min){

					for($i = 4; $i < 60; $i+=4) {
						_add_to_action_queue('open_garage_z'.$garage_number, $_SESSION['id'], $now + $i);
					}

					$message = 'Garage opened for 1 min';
				}

				$status = 'ok';
			}
			break;

		case 'open_entrance_z7b1':
		case 'open_entrance_z7b2':
		case 'open_entrance_z8b1':
		case 'open_entrance_z8b2':
		case 'open_entrance_z9b1':
		case 'open_entrance_z9b2':

			preg_match('/^open_entrance_(z[0-9]b[0-9])$/i', $_POST['action'], $m);

			$entrance_name = $m[1];

			if(!_check_permission($entrance_name)){
				$message = 'Not authorized';
			} else {
				_add_to_action_queue($_POST['action'], $_SESSION['id'], time());
				$message = 'Entrance opened';
				$status = 'ok';
			}
			break;
		case 'open_entrance_menclova':
		case 'open_entrance_smrckova':

			preg_match('/^open_entrance_(.*)$/i', $_POST['action'], $m);

			$entrance_name = $m[1];

			if(!_check_permission('entrance_'.$entrance_name)){
				$message = 'Not authorized';
			} else {
				_add_to_action_queue($_POST['action'], $_SESSION['id'], time());
				$message = 'Entrance opened';
				$status = 'ok';
			}
			break;
	}

	$result = array('status' => $status, 'message' => $message);

	if(!empty($repeat_times)){
		$result['repeat_times'] = $repeat_times;
		$result['repeat_miliseconds'] = $repeat_miliseconds;
	}

	die(json_encode($result));
}

_require_login();

$user = _get_user_array();

$smarty->assign('user', $user);
$smarty->assign('permissions', _get_permissions());

$smarty->display('index.tpl');

?>