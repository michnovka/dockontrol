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
							'allow_widget' => true,
							'icon' => 'PHN2ZyBpZD0iTGF5ZXJfMSIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgNDY0IDQ2NCIgaGVpZ2h0PSI1MTIiIHZpZXdCb3g9IjAgMCA0NjQgNDY0IiB3aWR0aD0iNTEyIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGQ9Im0zOTAgNDQ4djhjMCA0LjQyLTMuNTggOC04IDhoLTMwMGMtNC40MiAwLTgtMy41OC04LTh2LThjMC0xMy4yNSAxMC43NS0yNCAyNC0yNHYtNDE2YzAtNC40MiAzLjU4LTggOC04aDI1MmM0LjQyIDAgOCAzLjU4IDggOHY0MTZjMTMuMjUgMCAyNCAxMC43NSAyNCAyNHoiIGZpbGw9IiNmZmJkN2IiLz48cGF0aCBkPSJtMzY2IDQyNGMxMy4yNTUgMCAyNCAxMC43NDUgMjQgMjR2OGMwIDQuNDE4LTMuNTgyIDgtOCA4aC0zMDBjLTQuNDE4IDAtOC0zLjU4Mi04LTh2LThjMC0xMy4yNTUgMTAuNzQ1LTI0IDI0LTI0eiIgZmlsbD0iIzQyNDM0ZCIvPjxwYXRoIGQ9Im0zMjAgMzMxdjQyYzAgNC40MTgtMy41ODIgOC04IDhoLTE2MGMtNC40MTggMC04LTMuNTgyLTgtOHYtNDJjMC00LjQxOCAzLjU4Mi04IDgtOGgxNjBjNC40MTggMCA4IDMuNTgyIDggOHoiIGZpbGw9IiNmZmFhNjQiLz48cGF0aCBkPSJtMzIwIDUwdjE1MGMwIDQuNDE4LTMuNTgyIDgtOCA4aC0xNjBjLTQuNDE4IDAtOC0zLjU4Mi04LTh2LTE1MGMwLTQuNDE4IDMuNTgyLTggOC04aDE2MGM0LjQxOCAwIDggMy41ODIgOCA4eiIgZmlsbD0iI2UzZjdmYyIvPjxwYXRoIGQ9Im0xOTYgMjU5YzAgNC40Mi0zLjU4IDgtOCA4aC0yMS4wMWMtLjI2IDguMzItNy4xMSAxNS0xNS40OSAxNS04LjU1IDAtMTUuNS02Ljk1LTE1LjUtMTUuNSAwLTEwLjA2MiA5LjM3LTE2LjI3IDE1Ljc1LTE1LjQ5LjEyNS0uMDE1IDM2LjEyNC0uMDEgMzYuMjUtLjAxIDQuNDIgMCA4IDMuNTggOCA4eiIgZmlsbD0iI2ZmZiIvPjxwYXRoIGQ9Im0zMjAgNTB2MTUwYzAgNC40MTgtMy41ODIgOC04IDhoLTE2MGMtNC40MTggMC04LTMuNTgyLTgtOHYtMTIuNzE5YzAtNC45MiA0LjQwNi04LjY5NiA5LjI2MS03Ljg5NSA4NS4wOTIgMTQuMDM3IDE0NS4yMDctNTIuMTA4IDE0NC4wNjYtMTI5LjI0Ni0uMDY2LTQuNDcyIDMuNTE5LTguMTM5IDcuOTkxLTguMTM5aDYuNjgyYzQuNDE4LS4wMDEgOCAzLjU4MSA4IDcuOTk5eiIgZmlsbD0iI2NhZjFmYyIvPjxwYXRoIGQ9Im0zNjYgOHYxNGMwIDQuNDE4LTMuNTgyIDgtOCA4aC0yMjJjLTQuNDE4IDAtOCAzLjU4Mi04IDh2Mzg2aC0zMHYtNDE2YzAtNC40MTggMy41ODItOCA4LThoMjUyYzQuNDE4IDAgOCAzLjU4MiA4IDh6IiBmaWxsPSIjZmZkM2E2Ii8+PHBhdGggZD0ibTM2Ni42NjUgNDM5Ljk4OWMtMjguMjQ5LS4wMy05NC4yMTcuMDExLTI0OS42NjUuMDExLTExLjE4IDAtMjAuNTggNy42NTMtMjMuMjQ0IDE4LjAwNC0uOTIgMy41NzItNC4yNDkgNS45OTYtNy45MzcgNS45OTZoLTMuODE5Yy00LjQxOCAwLTgtMy41ODItOC04di04YzAtMTMuMjU1IDEwLjc0NS0yNCAyNC0yNGgyNjhjMTIuMDg4IDAgMTAuODY1IDE2IC42NjUgMTUuOTg5eiIgZmlsbD0iIzU4NTk2NiIvPjwvc3ZnPg==',
							'icon_png' => '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'
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
						'icon' => null,
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
