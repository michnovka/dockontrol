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

function _add_to_action_queue($action, $user_id, $time_start_unixtime){
	global $db;

	$db->query('INSERT INTO action_queue SET time_created=NOW(), time_start=?, user_id=#, action=?', date('Y-m-d H:i:s', $time_start_unixtime), $user_id, $action);
}

function _check_permission($permission)
{
	global $db;

	if (empty($_SESSION['permissions'])) {
		_get_permissions();
	}

	return $_SESSION['permissions'][$permission] == 1;
}

function _get_permissions($user_id = null)
{
	global $db;

	if(!$user_id)
		$user_id = $_SESSION['id'];

	$_SESSION['permissions'] = $db->queryfirst('SELECT
		   MAX(g.admin) as admin,
		   MAX(g.z7b1) as z7b1,
		   MAX(g.z7b2) as z7b2,
		   MAX(g.z8b1) as z8b1,
		   MAX(g.z8b2) as z8b2,
		   MAX(g.z9b1) as z9b1,
		   MAX(g.z9b2) as z9b2,
		   MAX(g.z9b2elevator) as z9b2elevator,
		   MAX(g.z9b1elevator) as z9b1elevator,
		   MAX(g.z7garage) as z7garage,
		   MAX(g.z8garage) as z8garage,
		   MAX(g.z9garage) as z9garage,
		   MAX(g.gate) as gate,
		   MAX(g.entrance_menclova) as entrance_menclova,
		   MAX(g.entrance_smrckova) as entrance_smrckova
		FROM `groups` g INNER JOIN user_group ug on g.id = ug.group_id WHERE ug.user_id=#', $user_id);

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

function _create_remember_me_cookie($user_id, $duration = 8640000){
	$expires = time() + $duration;
	$cookie_value = $user_id.':'.$expires.':'.hash('sha256', $user_id.$expires.'SecretHashWhardjbdiubd*fif');

	setcookie('rememberme', $cookie_value, $expires);
}

function _get_user_array($user_id = null){
	global $db;

	if(!$user_id)
		$user_id = $_SESSION['id'];

	return $db->queryfirst('SELECT * FROM users WHERE id=# LIMIT 1', $user_id);
}

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

$_CONFIG = null;
$config_raw = null;

$db->queryall('SELECT * FROM config', $config_raw);

foreach ($config_raw as $c){
	$_CONFIG[$c['key']] = $c['value'];
}