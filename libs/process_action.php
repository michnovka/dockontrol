<?php

/**
 * @param string $action
 * @param array $user
 * @param array|null $guest
 * @return string[]
 * @throws EDatabase
 */
function processAction($action, $user, $guest = null){

	global $db;

	$status = '';
	$message = '';

	$guest_id = null;

	if($guest){
		$guest_id = $guest['id'];
	}

	switch($action){

		case 'enter':
			// gate, 10sec sleep, garage
			if(!_check_permission('gate', $user) || !_check_permission($user['default_garage'].'garage', $user)){
				$message = 'Not authorized';
			} else {
				_add_to_action_queue('open_gate', $user['id'], time(), $guest_id);
				_add_to_action_queue('open_garage_' . $user['default_garage'], $user['id'], time() + 10, $guest_id);
				//sleep(1);

				if(_check_permission('z9b2elevator', $user) && $user['apartment'] == 'Z9.B2.501'){
					_add_to_action_queue('unlock_elevator_z9b2', $user['id'], time() + 45, $guest_id);
					_add_to_action_queue('unlock_elevator_z9b2', $user['id'], time() + 60, $guest_id);
					_add_to_action_queue('unlock_elevator_z9b2', $user['id'], time() + 75, $guest_id);
				}

				if(_check_permission('z9b1elevator', $user) && $user['apartment'] == 'Z9.B1.501'){
					_add_to_action_queue('unlock_elevator_z9b1', $user['id'], time() + 45, $guest_id);
					_add_to_action_queue('unlock_elevator_z9b1', $user['id'], time() + 60, $guest_id);
					_add_to_action_queue('unlock_elevator_z9b1', $user['id'], time() + 75, $guest_id);
				}

				$message = 'Gate and garage opened';
				$status = 'ok';
			}
			break;

		case 'exit':
			if(!_check_permission('gate', $user) || !_check_permission($user['default_garage'].'garage', $user)){
				$message = 'Not authorized';
			} else {
				_add_to_action_queue('open_garage_' . $user['default_garage'], $user['id'], time(), $guest_id);
				_add_to_action_queue('open_gate', $user['id'], time() + 23, $guest_id);
				_add_to_action_queue('open_gate', $user['id'], time() + 27, $guest_id);
				//sleep(1);
				$message = 'Garage and gate opened';
				$status = 'ok';
			}
			break;

		case 'open_gate':
		case 'open_gate_1min':
			// gate
			if(!_check_permission('gate', $user) ){
				$message = 'Not authorized';
			} else {
				$now = time();

				_add_to_action_queue('open_gate', $user['id'], $now, $guest_id);
				//sleep(1);
				$message = 'Gate opened';

				$gate_1min = $action  == 'open_gate_1min';

				if($gate_1min){

					for($i = 5; $i < 60; $i+=5) {
						_add_to_action_queue('open_gate', $user['id'], $now + $i, $guest_id);
					}

					$message = 'Garage opened for 1 min';
				}

				$status = 'ok';
			}
			break;

		case 'unlock_elevator_z9b1':
		case 'unlock_elevator_z9b2':

			preg_match('/^unlock_elevator_(z[0-9]b[0-9])$/', $action, $m);

			// elevator
			if(!_check_permission($m[1].'elevator', $user)){
				$message = 'Not authorized';
			} else {
				_add_to_action_queue('unlock_elevator_'.$m[1], $user['id'], time(), $guest_id);
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

			preg_match('/^open_garage_z([0-9])(_1min)?/', $action, $m);

			$garage_number = $m[1];
			$garage_1min = !!$m[2];

			// garage z9
			if(!_check_permission('z'.$garage_number.'garage', $user)){
				$message = 'Not authorized';
			} else {

				$now = time();

				_add_to_action_queue('open_garage_z'.$garage_number, $user['id'], $now, $guest_id);
				$message = 'Garage opened';

				if($garage_1min){

					for($i = 4; $i < 60; $i+=4) {
						_add_to_action_queue('open_garage_z'.$garage_number, $user['id'], $now + $i, $guest_id);
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

			preg_match('/^open_entrance_(z[0-9]b[0-9])$/i', $action, $m);

			$entrance_name = $m[1];

			if(!_check_permission($entrance_name, $user)){
				$message = 'Not authorized';
			} else {
				_add_to_action_queue($action, $user['id'], time(), $guest_id);
				$message = 'Entrance opened';
				$status = 'ok';
			}
			break;
		case 'open_entrance_menclova':
		case 'open_entrance_smrckova':

			preg_match('/^open_entrance_(.*)$/i', $action, $m);

			$entrance_name = $m[1];

			if(!_check_permission('entrance_'.$entrance_name, $user)){
				$message = 'Not authorized';
			} else {
				_add_to_action_queue($action, $user['id'], time(), $guest_id);
				$message = 'Entrance opened';
				$status = 'ok';
			}
			break;
		default:
			$status = 'error';
			$message = 'Unknown action';
	}

	if($guest && $status != 'error' && $guest['remaining_actions'] > 0){
		// subtract one action
		$db->query('UPDATE guests SET remaining_actions = # WHERE id=#', --$guest['remaining_actions'], $guest['id']);
	}

	$result = array('status' => $status, 'message' => $message);
	
	return $result;
}