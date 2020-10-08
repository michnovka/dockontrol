<?php

require_once dirname(__FILE__).'/libs/config.php';

_require_login();
if(!_check_permission('admin')){
	header('Location: /');
	exit;
}

if($_GET['change_signup_key']){
	$new_key = PasswordTools::generateRandomHash(16, 16);
	$db->query('INSERT INTO config SET `key`=?, `value`=? ON DUPLICATE KEY UPDATE `value` = ?', 'signup_key', $new_key, $new_key);
	header('Location:admin_users.php?signup_key_changed=1#signup_url');
	exit;
}

$user = _get_user_array();


$smarty->assign('user', $user);
$smarty->assign('permissions', _get_permissions());

$groups  = null;
$db->queryall('SELECT * FROM groups ORDER BY name', $groups);

$users = null;
$db->queryall('SELECT GROUP_CONCAT(g.name) `groups_names`,u.* FROM users u INNER JOIN user_group ug on u.id = ug.user_id INNER JOIN groups g ON g.id = ug.group_id GROUP BY u.id ORDER BY u.id', $users);

$last_command_times = null;
$db->queryall('SELECT MAX(time_created) last_command_time, user_id FROM action_queue GROUP BY user_id', $last_command_times, '%user_id@last_command_time');

foreach ($users as $k => $v){
	$users[$k]['groups'] = explode(',',$v['user_groups']);
	unset($users[$k]['user_groups']);
	$users[$k]['last_command_time'] = $last_command_times[$v['id']][0];
}

$smarty->assign('users', $users);

$smarty->assign('groups', $groups);
$smarty->assign('signup_key_changed', !empty($_GET['signup_key_changed']));

$smarty->assign('signup_url', 'https://'.$_SERVER['HTTP_HOST'].'/signup.php?key='.$_CONFIG['signup_key']);
$smarty->display('admin_users.tpl');

?>