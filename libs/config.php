<?php

const DOCKONTROL_VERSION = '2021.10.21.1';

require_once dirname(__FILE__).'/lib_db4.php';
require_once dirname(__FILE__).'/lib_password.php';
require_once(dirname(__FILE__).'/../smarty/libs/Smarty.class.php');
require_once(dirname(__FILE__).'/../config/database.php');
require_once(dirname(__FILE__).'/../config/API_SECRET.php');
require_once(dirname(__FILE__).'/../config/phone_control_config.php');
require_once(dirname(__FILE__).'/../config/cron_config.php');

error_reporting(0);
ini_set('display_errors', '0');

session_start();

$smarty = new Smarty();

$smarty->setTemplateDir(dirname(__FILE__).'/../smarty/templates');
$smarty->setCompileDir(dirname(__FILE__).'/../smarty/templates_c');
$smarty->setCacheDir(dirname(__FILE__).'/../smarty/cache');
$smarty->setConfigDir(dirname(__FILE__).'/../smarty/configs');

if(
	preg_match('/tizen/i', $_SERVER['HTTP_USER_AGENT']) &&
	preg_match('/samsung/i', $_SERVER['HTTP_USER_AGENT'])
)
	$smarty->assign('__samsung_watch', true);

$db = new Database4(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME, DATABASE_TYPE);

/**
 * @param string $action
 * @param int $user_id
 * @param int $time_start_unixtime
 * @param int $guest_id
 * @param bool $count_into_stats
 * @throws EDatabase
 */
function _add_to_action_queue($action, $user_id, $time_start_unixtime, $guest_id = null, $count_into_stats = true){
	global $db;

	// todo: remove
	if($user_id != 50)
		$db->query('INSERT INTO action_queue SET time_created=NOW(), time_start=?, user_id=#, count_into_stats=#, action=?'.($guest_id ? ',guest_id='.intval($guest_id) : ''), date('Y-m-d H:i:s', $time_start_unixtime), $user_id, $count_into_stats ? 1 : 0, $action);
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
	
	if(empty($permission))
		return true;

	if(!empty($user)){
		_get_permissions($user['id'], false);
	}elseif(empty($_SESSION['permissions'])) {
		_get_permissions();
	}

	return $_SESSION['permissions'][$permission] == 1;
}

/**
 * @param int $admin_id
 * @param int $user_id
 * @return bool
 * @throws EDatabase
 */
function _check_admin_permission_for_user($admin_id, $user_id){
	global $db;

	if(_check_permission('super_admin')) return true;

	return $db->fetch('SELECT 1 FROM users u INNER JOIN user_group ug on u.id = ug.user_id INNER JOIN groups g ON g.id = ug.group_id WHERE u.id=# '.(_check_permission('super_admin') ? '' : ' AND apartment REGEXP (SELECT GROUP_CONCAT(CONCAT("^",ab.building) SEPARATOR "|") FROM admin_buildings ab INNER JOIN `groups` g ON g.id = ab.admin_group_id INNER JOIN group_permission gp on g.id = gp.group_id INNER JOIN user_group u on g.id = u.group_id WHERE gp.permission="admin" AND u.user_id='.intval($admin_id).')').' GROUP BY u.id ORDER BY u.id', $user_id) ? true : false;
}

/**
 * @param int $admin_id
 * @param string $building
 * @return mixed|null
 * @throws EDatabase
 */
function _check_admin_building_permission($admin_id, $building){
	global $db;
	return $db->fetch("SELECT ? REGEXP (SELECT GROUP_CONCAT(CONCAT('^',ab.building) SEPARATOR '|') FROM admin_buildings ab INNER JOIN `groups` g ON g.id = ab.admin_group_id INNER JOIN group_permission gp on g.id = gp.group_id INNER JOIN user_group u on g.id = u.group_id WHERE gp.permission='admin' AND u.user_id=#)", $building, $admin_id) ? true : false;
}

/**
 * @param int $nuki_id
 * @param null|array $user
 * @return bool
 * @throws EDatabase
 */
function _check_nuki_permission($nuki_id, $user = null)
{
	global $db;
	$user_id = $_SESSION['id'];

	if(!empty($user))
		$user_id = $user['id'];

	return $db->fetch('SELECT 1 FROM nuki WHERE user_id=# AND id=# LIMIT 1', $user_id, $nuki_id) ? true : false;
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

	$permissions = null;
	$db->queryall('SELECT DISTINCT gp.permission FROM group_permission gp INNER JOIN `groups` g ON g.id = gp.group_id INNER JOIN user_group ug on g.id = ug.group_id WHERE ug.user_id=#'.($ignore_admin ? ' AND g.id NOT IN (SELECT group_id FROM group_permission WHERE permission="admin" OR permission="super_admin")' : ''), $permissions, 'permission', $user_id);

	unset($_SESSION['permissions']);

	foreach ($permissions as $permission){
		$_SESSION['permissions'][$permission] = true;
	}

	if($ignore_admin) {
		$_SESSION['permissions']['admin'] = $db->fetch('SELECT 1 FROM group_permission gp INNER JOIN `groups` g ON gp.group_id = g.id INNER JOIN user_group ug on g.id = ug.group_id WHERE ug.user_id=# AND gp.permission="admin" LIMIT 1', $user_id);
		$_SESSION['permissions']['super_admin'] = $db->fetch('SELECT 1 FROM group_permission gp INNER JOIN `groups` g ON gp.group_id = g.id INNER JOIN user_group ug on g.id = ug.group_id WHERE ug.user_id=# AND gp.permission="super_admin" LIMIT 1', $user_id);
	}

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
 * @param string $garage
 * @return int|null
 */
function getRWFromGarage($garage){
	$gate_rw = null;

	if(in_array($garage, array('z7','z8','z9')))
		$gate_rw = 3;
	elseif(in_array($garage, array('z4','z5','z6')))
		$gate_rw = 2;
	elseif(in_array($garage, array('z1','z2','z3')))
		$gate_rw = 1;

	return $gate_rw;
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

$smarty->assign('_CONFIG', $_CONFIG);

$smarty->assign('DOCKONTROL_VERSION', DOCKONTROL_VERSION);