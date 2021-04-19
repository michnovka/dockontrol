<?php

require_once dirname(__FILE__).'/libs/config.php';
require_once dirname(__FILE__).'/libs/process_action.php';

/** @var Database4 $db */

$user = null;
$guest = null;

if(!empty($_POST['action'])){

	if(empty($_GET['guest'])) {
		if(!_require_login(false)){
			die(json_encode(array('status' => 'relogin')));
		}
		$user = _get_user_array();
	}else{
		$guest = _require_guest_login($_GET['guest'], false);

		if(empty($guest)){
			die(json_encode(array('status' => 'relogin')));
		}

		$user = _get_user_array($guest['user_id']);
		$user['has_camera_access'] = 0;
	}

	$totp = null;
	$totp_nonce = null;
	$pin = null;

	if(!empty($_POST['pin']))
		$pin = $_POST['pin'];

	if(!empty($_POST['totp']))
		$totp = $_POST['totp'];

	if(!empty($_POST['totp_nonce']))
		$totp_nonce = $_POST['totp_nonce'];

	$result = array('status' => 'error');

	if(empty($guest) && $_POST['action'] == 'check_pin') {
		$nuki = $db->queryfirst('SELECT * FROM nuki WHERE user_id=# AND id=# LIMIT 1', $user['id'], $_POST['nuki_id']);

		if(empty($nuki)){
			$result['status'] = 'error';
			$result['message'] = 'Unauthorized';
		}elseif(intval($db->fetch('SELECT COUNT(*) FROM nuki_logs WHERE status=\'incorrect_pin\' AND time > DATE_SUB(NOW(), INTERVAL 5 MINUTE) AND nuki_id=#', $nuki['id'])) > 5){
			$result['status'] = 'error';
			$result['message'] = 'Too many PIN attempts. Try in 5 mins';
		}elseif(!$nuki['pin'] || $nuki['pin'] != $pin){
			$result['status'] = 'error';
			$result['message'] = 'Incorrect PIN';
			$db->query('INSERT INTO nuki_logs SET time=NOW(), nuki_id=#, status=\'incorrect_pin\', action=?', $nuki['id'], 'pin_check');
		}else{
			$result['status'] = 'ok';
		}
	}else {
		$result = processAction($_POST['action'], $user, $guest, $totp, $totp_nonce, $pin);
	}
	die(json_encode($result));
}

if(empty($_GET['guest'])) {
	_require_login();
	$user = _get_user_array();
}else{
	$guest = _require_guest_login($_GET['guest']);
	$user = _get_user_array($guest['user_id']);
	$user['has_camera_access'] = 0;
	$smarty->assign('guest', $guest);
}

$smarty->assign('user', $user);
$smarty->assign('permissions', _get_permissions($user['id'], !array_key_exists('admin', $_GET)));

$gates = array();
$db->queryall('SELECT * FROM buttons WHERE type=\'gate\' AND (user_id=# OR user_id IS NULL) ORDER BY sort_index', $gates,'', $user['id']);

$entrances = array();
$db->queryall('SELECT * FROM buttons WHERE type=\'entrance\' AND (user_id=# OR user_id IS NULL) ORDER BY sort_index', $entrances,'', $user['id']);

$elevators = array();
$db->queryall('SELECT * FROM buttons WHERE type=\'elevator\' AND (user_id=# OR user_id IS NULL) ORDER BY sort_index', $elevators,'', $user['id']);

$multi = array();
$db->queryall('SELECT * FROM buttons WHERE type=\'multi\' AND (user_id=# OR user_id IS NULL) ORDER BY sort_index', $multi,'', $user['id']);


$nuki = array();

if(empty($guest))
	$db->queryall('SELECT * FROM nuki WHERE user_id=#', $nuki,'', $user['id']);

$name_conflicts = array();

foreach($gates as $row){
	if(_check_permission($row['permission']))
		$name_conflicts[$row['name']]++;
}

foreach($entrances as $row){
	if(_check_permission($row['permission']))
		$name_conflicts[$row['name']]++;
}

foreach($elevators as $row){
	if(_check_permission($row['permission']))
		$name_conflicts[$row['name']]++;
}

foreach($multi as $row){
	if(_check_permission($row['permission']))
		$name_conflicts[$row['name']]++;
}

$smarty->assign('gates', $gates);
$smarty->assign('entrances', $entrances);
$smarty->assign('elevators', $elevators);
$smarty->assign('multi', $multi);

$smarty->assign('name_conflicts', $name_conflicts);

if(!empty($nuki))
	$smarty->assign('nuki', $nuki);

$smarty->assign('admin_limited_view', !array_key_exists('admin', $_GET));

$smarty->display('index.tpl');

?>
