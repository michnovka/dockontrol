<?php

require_once dirname(__FILE__).'/lib_db4.php';
require_once dirname(__FILE__).'/lib_password.php';
require_once(dirname(__FILE__).'/../smarty/libs/Smarty.class.php');


error_reporting(0);
ini_set('display_errors', '0');

session_start();

$smarty = new Smarty();

$smarty->setTemplateDir(dirname(__FILE__).'/../smarty/templates');
$smarty->setCompileDir(dirname(__FILE__).'/../smarty/templates_c');
$smarty->setCacheDir(dirname(__FILE__).'/../smarty/cache');
$smarty->setConfigDir(dirname(__FILE__).'/../smarty/configs');

$db = new Database4('localhost', 'dock', 'ObamaIsNotOsama!', 'dock', 'mysqli');

/**
 * @param string $action
 * @param int $user_id
 * @param int $time_start_unixtime
 * @param int $guest_id
 * @throws EDatabase
 */
function _add_to_action_queue($action, $user_id, $time_start_unixtime, $guest_id = null){
	global $db;

	$db->query('INSERT INTO action_queue SET time_created=NOW(), time_start=?, user_id=#, action=?'.($guest_id ? ',guest_id='.intval($guest_id) : ''), date('Y-m-d H:i:s', $time_start_unixtime), $user_id, $action);
}

/**
 * @param string $permission
 * @param null|array $user
 * @return bool
 * @throws EDatabase
 */
function _check_permission($permission, $user = null)
{
	global $db;

	if(!empty($user)){
		_get_permissions($user['id'], false);
	}elseif(empty($_SESSION['permissions'])) {
		_get_permissions();
	}

	return $_SESSION['permissions'][$permission] == 1;
}

/**
 * @param null|int $user_id
 * @param bool $ignore_admin
 * @return array
 * @throws EDatabase
 */
function _get_permissions($user_id = null, $ignore_admin = true)
{
	global $db;

	if(!$user_id)
		$user_id = $_SESSION['id'];

	$_SESSION['permissions'] = $db->queryfirst('SELECT
		   MAX(g.admin) as admin,
		   MAX(g.permission_entrance_z7b1) as entrance_z7b1,
		   MAX(g.permission_entrance_z7b2) as entrance_z7b2,
		   MAX(g.permission_entrance_z8b1) as entrance_z8b1,
		   MAX(g.permission_entrance_z8b2) as entrance_z8b2,
		   MAX(g.permission_entrance_z9b1) as entrance_z9b1,
		   MAX(g.permission_entrance_z9b2) as entrance_z9b2,
		   MAX(g.permission_elevator_z8b1) as elevator_z8b1,
		   MAX(g.permission_elevator_z9b1) as elevator_z9b1,
		   MAX(g.permission_elevator_z9b2) as elevator_z9b2,
		   MAX(g.permission_garage_z7) as garage_z7,
		   MAX(g.permission_garage_z8) as garage_z8,
		   MAX(g.permission_garage_z9) as garage_z9,
		   MAX(g.permission_gate) as gate,
		   MAX(g.permission_entrance_menclova) as entrance_menclova,
		   MAX(g.permission_entrance_smrckova) as entrance_smrckova,
		   MAX(g.permission_entrance_smrckova_river) as entrance_smrckova_river
		FROM `groups` g INNER JOIN user_group ug on g.id = ug.group_id WHERE ug.user_id=#'.($ignore_admin ? ' AND g.id != 1' : ''), $user_id);

	if($ignore_admin)
		$_SESSION['permissions']['admin'] = $db->fetch('SELECT 1 FROM `groups` g INNER JOIN user_group ug on g.id = ug.group_id WHERE ug.user_id=# AND g.id=1', $user_id);

	return $_SESSION['permissions'];
}

function _set_session_params($user){
	$_SESSION['id'] = $user['id'];
}

function _log_login_success($user, $is_from_remember_me = false){
	global $db;
	$browser = get_browser();
	$db->query('INSERT INTO login_logs SET user_id=#, time=NOW(), ip=?, browser=?, platform=?, from_remember_me = #', $user['id'], $_SERVER['REMOTE_ADDR'], $browser->browser, $browser->platform, $is_from_remember_me ? 1 : 0);
	$db->query('UPDATE users SET last_login_time=NOW() WHERE id=#', $user['id']);
}

/**
 * @param bool $log
 * @return bool
 * @throws EDatabase
 */
function _restore_remember_me_cookie($log=true){
	global $db;

	if(!empty($_SESSION['id']))
		return true;

	if(!empty($_COOKIE['rememberme'])){

		list($user_id, $expires, $hash) = explode(':', $_COOKIE['rememberme'], 3);

		if($hash == hash('sha256', $user_id.$expires.'SecretHashWhardjbdiubd*fif') && $expires > time()){

			$user = $db->queryfirst('SELECT * FROM users WHERE id=# AND enabled=1 LIMIT 1', $user_id);

			if(!empty($user)) {
				if($log) {
					_log_login_success($user, true);
				}
				_set_session_params($user);

				return true;
			}

		}

	}

	return false;

}

/**
 * @param int $user_id
 * @param int $duration
 */
function _create_remember_me_cookie($user_id, $duration = 8640000){
	$expires = time() + $duration;
	$cookie_value = $user_id.':'.$expires.':'.hash('sha256', $user_id.$expires.'SecretHashWhardjbdiubd*fif');

	setcookie('rememberme', $cookie_value, $expires);
}

/**
 * @param null|int $user_id
 * @return array|mixed
 * @throws EDatabase
 */
function _get_user_array($user_id = null){
	global $db;

	if(!$user_id)
		$user_id = $_SESSION['id'];

	return $db->queryfirst('SELECT * FROM users WHERE id=# LIMIT 1', $user_id);
}

/**
 * @param string $redirect_to
 * @return bool
 * @throws EDatabase
 */
function _require_login($redirect_to = '/login.php'){

	if(empty($_SESSION['id']))
		_restore_remember_me_cookie();

	if(empty($_SESSION['id'])){
		if($redirect_to === false){
			return false;
		}else {
			header('Location: ' . $redirect_to);
			exit;
		}
	}

	return true;
}

/**
 * @param string $guest_hash
 * @param string $redirect_to
 * @return array|false
 * @throws EDatabase
 */
function _require_guest_login($guest_hash, $redirect_to = '/login.php?guest_error=1'){

	global $db;

	if(!empty($_SESSION['id'])){
		header('Location: /');
		exit;
	}

	$guest = $db->queryfirst('SELECT * FROM guests WHERE hash=? AND expires > NOW() AND remaining_actions != 0 LIMIT 1', $guest_hash);

	if(empty($guest)){
		if($redirect_to) {
			header('Location: ' . $redirect_to);
			exit;
		}else{
			return false;
		}
	}

	return $guest;
}

$_CONFIG = null;
$config_raw = null;

$db->queryall('SELECT * FROM config', $config_raw);

foreach ($config_raw as $c){
	$_CONFIG[$c['key']] = $c['value'];
}