<?php

require_once dirname(__FILE__).'/libs/config.php';

_require_login();
if(!_check_permission('admin')){
	header('Location: /');
	exit;
}

if($_POST['create_new_signup_key']){
	$error = false;

	// check apartment_mask
	if(!_check_permission('super_admin')){
		if(!preg_match('/Z([0-9])\.B([0-9])\.?([0-9]{3})?$/i', $_POST['apartment_mask'], $m)){
			$error = 'Invalid mask';
		}elseif(!_check_admin_building_permission($_SESSION['id'], 'Z'.$m[1].'.B'.$m[2])){
			$error = 'Not authorized';
		}else{
			$_POST['apartment_mask'] = strtoupper($_POST['apartment_mask']);
		}
	}

	if($error){
		$smarty->assign('signup_key_error', $error);
	}else {

		$new_key = PasswordTools::generateRandomHash(16, 16);
		$new_key_expires = date('Y-m-d H:i:s', time() + (86400 * 7));

		$db->query('INSERT INTO signup_codes SET hash=?, admin_id=#, expires = ?, apartment_mask=?, created_time=NOW()', $new_key, $_SESSION['id'], $new_key_expires, $_POST['apartment_mask']);

		$smarty->assign('signup_key_created', true);
		$smarty->assign('signup_key_expires', $new_key_expires);
		$smarty->assign('signup_url', 'https://' . $_SERVER['HTTP_HOST'] . '/signup.php?key=' . $new_key);
	}
}

$user = _get_user_array();


$smarty->assign('user', $user);
$smarty->assign('permissions', _get_permissions());

$groups  = null;
$db->queryall('SELECT * FROM groups ORDER BY name', $groups);

$users = null;
$db->queryall('SELECT GROUP_CONCAT(g.name) `groups_names`,u.* FROM users u INNER JOIN user_group ug on u.id = ug.user_id INNER JOIN groups g ON g.id = ug.group_id '.(_check_permission('super_admin') ? '' : ' WHERE apartment REGEXP (SELECT GROUP_CONCAT(CONCAT("^",ab.building) SEPARATOR "|") FROM admin_buildings ab INNER JOIN `groups` g ON g.id = ab.admin_group_id INNER JOIN group_permission gp on g.id = gp.group_id INNER JOIN user_group u on g.id = u.group_id WHERE gp.permission="admin" AND u.user_id='.intval($user['id']).')').' GROUP BY u.id ORDER BY u.id', $users);

$last_command_times = null;
$db->queryall('SELECT MAX(time_created) last_command_time, user_id FROM action_queue GROUP BY user_id', $last_command_times, '%user_id@last_command_time');

foreach ($users as $k => $v){
	$users[$k]['groups'] = explode(',',$v['user_groups']);
	unset($users[$k]['user_groups']);
	$users[$k]['last_command_time'] = $last_command_times[$v['id']][0];
}

$smarty->assign('users', $users);

$smarty->assign('groups', $groups);
$smarty->display('admin_users.tpl');

?>