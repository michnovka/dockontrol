<?php

require_once dirname(__FILE__).'/../libs/config.php';
require_once dirname(__FILE__).'/../config/cron_config.php';

if(php_sapi_name() !== 'cli' || isset($_SERVER['HTTP_USER_AGENT']))
	exit;

$db->query('DELETE FROM login_logs WHERE time < DATE_SUB(NOW(), INTERVAL # DAY)', $CRON_FONFIG['login_logs_timelife_days']);
$db->query('DELETE FROM login_logs_failed WHERE time < DATE_SUB(NOW(), INTERVAL # DAY)', $CRON_FONFIG['login_logs_failed_timelife_days']);
$db->query('DELETE FROM camera_logs WHERE time < DATE_SUB(NOW(), INTERVAL # DAY)', $CRON_FONFIG['camera_logs_timelife_days']);
$db->query('DELETE FROM nuki_logs WHERE time < DATE_SUB(NOW(), INTERVAL # DAY)', $CRON_FONFIG['nuki_logs_timelife_days']);
$db->query('DELETE FROM webauthn_registrations WHERE last_used_time < DATE_SUB(NOW(), INTERVAL # DAY)', $CRON_FONFIG['webauthn_registrations_unused_timelife_days']);

echo date('Y-m-d H:i:s')." | All done\n";