<?php

require_once dirname(__FILE__).'/libs/config.php';


_require_login();
if(!_check_permission('admin')){
	header('Location: /');
	exit;
}

$user = _get_user_array();

$queue = null;
$queue_executed = null;

$limit = 20;

if(
	!empty($_GET['limit']) && 
	intval($_GET['limit']) > 0 &&
	intval($_GET['limit']) < 1000
){
	$limit = intval($_GET['limit']);
}

$db->queryall('SELECT q.*, CONCAT(u.name, IF(g.id IS NOT NULL, CONCAT(\' (Guest \',g.id,\')\'), \'\')) as name, u.username FROM action_queue q INNER JOIN users u ON u.id = q.user_id LEFT JOIN guests g on q.guest_id = g.id WHERE q.executed=1 ORDER BY q.time_start DESC LIMIT #', $queue_executed, '', $limit);
$db->queryall('SELECT q.*, CONCAT(u.name, IF(g.id IS NOT NULL, CONCAT(\' (Guest \',g.id,\')\'), \'\')) as name, u.username FROM action_queue q INNER JOIN users u ON u.id = q.user_id LEFT JOIN guests g on q.guest_id = g.id WHERE q.executed=0 ORDER BY q.time_start ASC LIMIT #', $queue, '', $limit);

$smarty->assign('queue', $queue);
$smarty->assign('queue_executed', $queue_executed);


$smarty->assign('user', $user);
$smarty->assign('permissions', _get_permissions());

$smarty->display('queue.tpl');

?>
