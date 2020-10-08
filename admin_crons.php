<?php

require_once dirname(__FILE__).'/libs/config.php';

_require_login();
if(!_check_permission('super_admin')){
	header('Location: /');
	exit;
}

$crontab_example = '';

$cron_groups = null;
$db->queryall('SELECT DISTINCT cron_group FROM actions ORDER BY cron_group', $cron_groups, 'cron_group');

$current_dir = dirname(__FILE__);

foreach ($cron_groups as $cron_group){
	$crontab_example .= '* * * * * php '.$current_dir.'/crons/action_queue.php '.$cron_group."\n";
}

$crontab_example .= "\n";
$crontab_example .= '0 2 * * * php '.$current_dir."/crons/db_cleanup.php\n\n";
$crontab_example .= '*/5 * * * * php '.$current_dir."/crons/monitor.php\n";

$smarty->assign('crontab_example', $crontab_example);


$user = _get_user_array();

$smarty->assign('user', $user);
$smarty->assign('permissions', _get_permissions());

$smarty->display('admin_crons.tpl');

?>
