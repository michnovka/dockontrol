<?php

require_once dirname(__FILE__).'/libs/config.php';

_require_login();
if(!_check_permission('admin')){
	header('Location: /');
	exit;
}

$user = _get_user_array();

$usage_stats_raw = null;
$usage_stats_raw = array();
$usage_stats_periods = array();

$db->queryall('SELECT COUNT(*) c, DATE_FORMAT(time_start, \'%Y-%m\') d, action FROM action_queue WHERE time_start >= DATE_SUB(DATE_FORMAT(NOW(), \'%Y-%m-01\'), INTERVAL 3 MONTH) AND count_into_stats=1 GROUP BY action, d ORDER BY action, d', $usage_stats_raw);

foreach ($usage_stats_raw as $row){
	$usage_stats[$row['action']][$row['d']] = $row['c'];
	$usage_stats_periods[$row['d']] = true;
}

$usage_stats_periods = array_keys($usage_stats_periods);

$smarty->assign('usage_stats', $usage_stats);
$smarty->assign('usage_stats_periods', $usage_stats_periods);

$smarty->assign('user', $user);
$smarty->assign('permissions', _get_permissions());

$smarty->display('stats.tpl');

?>
