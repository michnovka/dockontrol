<?php

require_once dirname(__FILE__).'/libs/config.php';

_require_login();
if(!_check_permission('admin')){
	header('Location: /');
	exit;
}

$user = _get_user_array();


$db->queryall('SELECT COUNT(*) c, action FROM action_queue WHERE time_start >= DATE_FORMAT(NOW(), \'%Y-%m-01\') AND count_into_stats=1 GROUP BY action ORDER BY action', $usage_stats);

$smarty->assign('usage_stats', $usage_stats);

$smarty->assign('user', $user);
$smarty->assign('permissions', _get_permissions());

$smarty->display('stats.tpl');

?>
