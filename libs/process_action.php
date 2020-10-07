<?php

require_once dirname(__FILE__).'/lib_totp.php';

/**
 * @param string $action
 * @param array $user
 * @param array|null $guest
 * @param string|null $totp
 * @param int|null $totp_nonce
 * @param int|null $pin
 * @return string[]
 * @throws EDatabase
 */
function processAction($action, $user, $guest = null, $totp = null, $totp_nonce = null, $pin = null){

	global $db;

	$status = 'error';
	$message = '';

	$guest_id = null;

	if($guest){
		$guest_id = $guest['id'];
	}

	if(empty($guest) && preg_match('/^nuki_(unlock|lock)_([0-9]+)$/i', $action, $m)){
		// process nuki
		$nuki = $db->queryfirst('SELECT * FROM nuki WHERE user_id=# AND id=# LIMIT 1', $user['id'], $m[2]);

		if(empty($nuki)){
			$status = 'error';
			$message = 'Unauthorized';
		}else{
			if($nuki['pin'] && !$pin){
				$status = 'pin_required';
				$message = 'PIN required';
			} elseif(intval($db->fetch('SELECT COUNT(*) FROM nuki_logs WHERE status=\'incorrect_pin\' AND time > DATE_SUB(NOW(), INTERVAL 5 MINUTE) AND nuki_id=#', $nuki['id'])) > 5){
				$status = 'error';
				$message = 'Too many PIN attempts. Try in 5 mins';
			} elseif($nuki['pin'] && $nuki['pin'] != $pin){
				$status = 'pin_required';
				$message = 'Incorrect PIN';

				$db->query('INSERT INTO nuki_logs SET time=NOW(), nuki_id=#, status=\'incorrect_pin\', action=?', $nuki['id'], $action == 'lock' ? 'lock' : 'unlock');
			} else {

				$action = 'unlock';

				// check if can perform lock action
				if ($m[1] == 'lock')
					$action = 'lock';

				if ($action == 'lock' && !$nuki['can_lock']) {
					$status = 'error';
					$message = 'Unauthorized to lock';
				} else {

					// perform action
					$secret1 = str_pad(GoogleAuthenticator::hex_to_base32(substr(hash('sha256', $nuki['password1']), 0, 20)), 16, 'A', STR_PAD_LEFT).str_pad(GoogleAuthenticator::hex_to_base32(substr(hash('sha256', $totp_nonce),0,10)), 8, 'A', STR_PAD_LEFT);

					$totp1 = GoogleAuthenticator::get_totp($secret1);
					$totp2 = $totp;

					$query_data = array(
						'username' => $nuki['username'],
						'totp1' => $totp1,
						'totp2' => $totp2,
						'nonce' => $totp_nonce,
						'action' => $action,
					);

					$url = $nuki['dockontrol_nuki_api_server'] . '?' . http_build_query($query_data);

					$ch = curl_init();
					$headers = array(
						'Accept: application/json',
						'Content-Type: application/json',

					);
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

					$reply = json_decode(curl_exec($ch), true);


					$status = $reply['status'];
					$message = $reply['message'];

				}

				$db->query('INSERT INTO nuki_logs SET time=NOW(), nuki_id=#, status=?, action=?', $nuki['id'], $status == 'ok' ? 'ok' : 'error', $action == 'lock' ? 'lock' : 'unlock');

			}
		}
	}else{

		switch ($action) {

			case 'enter':
				$gate_rw = getRWFromGarage($user['default_garage']);

				if(!$gate_rw){
					$message = 'No default garage selected';
				}elseif (!_check_permission('gate_rw'.$gate_rw, $user) || !_check_permission('garage_' . $user['default_garage'], $user)) {
					$message = 'Not authorized';
				} else {

					_add_to_action_queue('open_gate_rw'.$gate_rw, $user['id'], time(), $guest_id);
					_add_to_action_queue('open_garage_' . $user['default_garage'], $user['id'], time() + 10, $guest_id);
					//sleep(1);

					if (_check_permission('elevator_z9b2', $user) && $user['apartment'] == 'Z9.B2.501') {
						_add_to_action_queue('unlock_elevator_z9b2', $user['id'], time() + 45, $guest_id);
						_add_to_action_queue('unlock_elevator_z9b2', $user['id'], time() + 100, $guest_id, false);
					}

					if (_check_permission('elevator_z9b1', $user) && $user['apartment'] == 'Z9.B1.501') {
						_add_to_action_queue('unlock_elevator_z9b1', $user['id'], time() + 45, $guest_id);
						_add_to_action_queue('unlock_elevator_z9b1', $user['id'], time() + 100, $guest_id, false);
					}

					if (_check_permission('elevator_z8b1', $user) && $user['apartment'] == 'Z8.B1.601') {
						_add_to_action_queue('unlock_elevator_z8b1', $user['id'], time() + 45, $guest_id);
						_add_to_action_queue('unlock_elevator_z8b1', $user['id'], time() + 100, $guest_id, false);
					}

					$message = 'Gate and garage opened';
					$status = 'ok';
				}
				break;

			case 'exit':
				$gate_rw = getRWFromGarage($user['default_garage']);

				if(!$gate_rw){
					$message = 'No default garage selected';
				}elseif (!_check_permission('gate_rw'.$gate_rw, $user) || !_check_permission('garage_' . $user['default_garage'], $user)) {
					$message = 'Not authorized';
				} else {
					_add_to_action_queue('open_garage_' . $user['default_garage'], $user['id'], time(), $guest_id);
					_add_to_action_queue('open_gate_rw'.$gate_rw, $user['id'], time() + 23, $guest_id);
					_add_to_action_queue('open_gate_rw'.$gate_rw, $user['id'], time() + 28, $guest_id, false);
					//sleep(1);
					$message = 'Garage and gate opened';
					$status = 'ok';
				}
				break;

			case 'open_gate_rw1':
			case 'open_gate_rw1_1min':
			case 'open_gate_rw3':
			case 'open_gate_rw3_1min':
				preg_match('/^open_gate_rw([0-9])(_1min)?$/i', $action, $gate);

				$gate_rw = $gate[1];
				$gate_1min = !!$gate[2];

				// gate
				if (!_check_permission('gate_rw'.$gate_rw, $user)) {
					$message = 'Not authorized';
				} else {
					$now = time();

					$action_for_queue = 'open_gate_rw'.$gate_rw;

					_add_to_action_queue($action_for_queue, $user['id'], $now, $guest_id);

					$message = 'Gate opened';

					if ($gate_1min) {

						for ($i = 5; $i < 60; $i += 5) {
							_add_to_action_queue($action_for_queue, $user['id'], $now + $i, $guest_id, false);
						}

						$message = 'Gate opened for 1 min';
					}

					$status = 'ok';
				}
				break;

			case 'unlock_elevator_z8b1':
			case 'unlock_elevator_z9b1':
			case 'unlock_elevator_z9b2':

				preg_match('/^unlock_elevator_(z[0-9]b[0-9])$/', $action, $m);

				// elevator
				if (!_check_permission('elevator_' . $m[1], $user)) {
					$message = 'Not authorized';
				} else {
					_add_to_action_queue('unlock_elevator_' . $m[1], $user['id'], time(), $guest_id);
					//sleep(1);
					$message = 'Elevator unlocked';
					$status = 'ok';
				}
				break;

			case 'open_garage_z1':
			case 'open_garage_z1_1min':
			case 'open_garage_z2':
			case 'open_garage_z2_1min':
			case 'open_garage_z3':
			case 'open_garage_z3_1min':
			case 'open_garage_z7':
			case 'open_garage_z7_1min':
			case 'open_garage_z8':
			case 'open_garage_z8_1min':
			case 'open_garage_z9':
			case 'open_garage_z9_1min':

				preg_match('/^open_garage_z([0-9])(_1min)?/', $action, $m);

				$garage_number = $m[1];
				$garage_1min = !!$m[2];

				// garage z9
				if (!_check_permission('garage_z' . $garage_number, $user)) {
					$message = 'Not authorized';
				} else {

					$now = time();

					_add_to_action_queue('open_garage_z' . $garage_number, $user['id'], $now, $guest_id);
					$message = 'Garage opened';

					if ($garage_1min) {

						for ($i = 4; $i < 60; $i += 4) {
							_add_to_action_queue('open_garage_z' . $garage_number, $user['id'], $now + $i, $guest_id, false);
						}

						$message = 'Garage opened for 1 min';
					}

					$status = 'ok';
				}
				break;

			case 'open_entrance_z1b1':
			case 'open_entrance_z2b1':
			case 'open_entrance_z3b1':
			case 'open_entrance_z3b2':
			case 'open_entrance_z7b1':
			case 'open_entrance_z7b2':
			case 'open_entrance_z8b1':
			case 'open_entrance_z8b2':
			case 'open_entrance_z9b1':
			case 'open_entrance_z9b2':
			case 'open_entrance_menclova':
			case 'open_entrance_smrckova':
			case 'open_entrance_smrckova_river':
			case 'open_entrance_menclova_z1':
			case 'open_entrance_menclova_z3':

				preg_match('/^open_entrance_(.*)$/i', $action, $m);

				$entrance_name = $m[1];

				if (!_check_permission('entrance_' . $entrance_name, $user)) {
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
	}

	if($guest && $status != 'error' && $guest['remaining_actions'] > 0){
		// subtract one action
		$db->query('UPDATE guests SET remaining_actions = # WHERE id=#', --$guest['remaining_actions'], $guest['id']);
	}

	$result = array('status' => $status, 'message' => $message);

	// todo: remove when production
	if(defined('_IS_API') && _IS_API)
		sleep(2);

	return $result;
}