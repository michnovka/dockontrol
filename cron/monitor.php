<?php

require_once dirname(__FILE__).'/../libs/config.php';
require_once dirname(__FILE__).'/../libs/api_libs.php';

if(php_sapi_name() !== 'cli' || isset($_SERVER['HTTP_USER_AGENT']))
	exit;

/** @var Database4 $db */
$db->queryall('SELECT * FROM dockontrol_nodes', $nodes);

foreach($nodes as $node){

	$ip = $node['ip'];

	$status = 'offline';

	// ping
	$ping = trim(`ping -W 1 -c 4 $ip | tail -1| awk '{print $4}' | cut -d '/' -f 2`);

	if($ping){
		$status = 'pingable';
	}else{
		$ping = null;
	}

	// try to fetch version
	$reply = json_decode(CallDOCKontrolNode($node['ip'], $node['api_secret'], 'version'), true);

	$version = null;
	$uptime = null;
	$os_version = null;
	$kernel_version = null;
	$device = null;

	if($reply){
		if($reply['status'] == 'ok') {
			$uptime = $reply['uptime'];
			$os_version = $reply['os_version'];
			$kernel_version = $reply['kernel_version'];
			$device = $reply['device'];

			$version = $reply['version'];
			$status = 'online';
		}else{
			if($reply['code'] == 403){
				$status = 'invalid_api_secret';
			}
		}
	}

	$db->query('UPDATE dockontrol_nodes SET status=?, ping='.($ping == null ? 'NULL' : floatval($ping)).','.($ping ? ' last_ping_time=NOW(), ' : '').' last_monitor_check_time=NOW() WHERE id=#', $status, $node['id']);

	if($status == 'online'){
		$db->query('UPDATE dockontrol_nodes SET dockontrol_node_version=?, os_version=?, kernel_version=?, uptime=#, device=?, last_monitor_check_time=NOW() WHERE id=#', $version, $os_version, $kernel_version, $uptime, $device, $node['id']);
	}

}

$now = date('Y-m-d H:i:s');
$db->query('INSERT INTO config SET `key`=?, `value`=? ON DUPLICATE KEY UPDATE `value` = ?', 'last_cron_monitor_time', $now, $now);

echo "DONE\n";