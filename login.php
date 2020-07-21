<?php

require_once dirname(__FILE__).'/libs/config.php';

if(empty($_SESSION['id']))
	_restore_remember_me_cookie();

if(!empty($_SESSION['id'])){
	header('Location: /');
	exit;
}

if(!empty($_GET['username']))
	$smarty->assign('username', $_GET['username']);

if(!empty($_GET['guest_error']))
	$smarty->assign('error', "Expired or incorrect guest token.");

if(!empty($_POST['action']) && $_POST['action'] == 'log_in'){
	//log in

	$error = null;

	$user = null;

	// check IP bruteforce
	$is_bruteforce = $db->fetch('SELECT IF(COUNT(*) > 10, 1, 0) FROM login_logs_failed WHERE ip=? AND time > DATE_SUB(NOW(), INTERVAL 5 MINUTE )', $_SERVER['REMOTE_ADDR']);

	if($is_bruteforce){
		$error = 'Too many tries. Repeat in 5 mins';
	}else {
		// check username and pwd
		$user = $db->queryfirst('SELECT * FROM users WHERE username=? AND enabled=1 LIMIT 1', $_POST['username']);

		if(empty($user) || !PasswordTools::checkPassword($_POST['password'], $user['password'])){
			$error = 'Invalid username or password';
		}
	}

	$browser = get_browser();

	if(!$error && !empty($user)){
		// log success login
		_log_login_success($user);
		// create remember me cookie
		_create_remember_me_cookie($user['id']);
		_set_session_params($user);

		header('Location: /');
		exit;
	} else {
		// log failed login
		$db->query('INSERT INTO login_logs_failed SET username=?, time=NOW(), ip=?, browser=?, platform=?', $_POST['username'], $_SERVER['REMOTE_ADDR'], $browser->browser, $browser->platform);

		$smarty->assign('error', $error);
	}

	$smarty->assign('username', htmlspecialchars($_POST['username']));
}

$smarty->assign('logged_out', !empty($_GET['logged_out']));

$smarty->display('login.tpl');

//echo PasswordTools::getHashedPassword('whct1645');