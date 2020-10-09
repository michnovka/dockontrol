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

			default:
				preg_match('/^(.*)(_1min)?$/iU', $action, $m);

				$is_1min = !!$m[2];

				$button = $db->queryfirst('SELECT * FROM buttons WHERE action=? LIMIT 1', $m[1]);

				if(empty($button)) {
					$status = 'error';
					$message = 'Unknown action';
				}else{
					if (!_check_permission($button['permission'], $user)) {
						$message = 'Not authorized';
					}elseif($is_1min && !$button['allow_1min_open']){
						$message = 'Not allowed to open for 1 min';
					}else{
						$now = time();

						_add_to_action_queue($button['action'], $user['id'], $now, $guest_id);

						$message = $button['name'].' opened';

						if ($is_1min) {

							for ($i = 5; $i < 60; $i += 5) {
								_add_to_action_queue($button['action'], $user['id'], $now + $i, $guest_id, false);
							}

							$message = $button['name'].' opened for 1 min';
						}

						$status = 'ok';
					}
				}
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