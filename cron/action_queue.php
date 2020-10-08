<?php

require_once dirname(__FILE__).'/../libs/config.php';
require_once(dirname(__FILE__).'/../libs/api_libs.php');
require_once dirname(__FILE__).'/../openwebnet-php/src/OpenWebNet.php';

/** @var array $_CONFIG */
/** @var Database4 $db */

if(php_sapi_name() !== 'cli' || isset($_SERVER['HTTP_USER_AGENT']))
	exit;

function OWNOpenDoor($door_id){
	global $_CONFIG;

	$own = new OpenWebNet($_CONFIG['openwebnet_ip'], $_CONFIG['openwebnet_port'], $_CONFIG['openwebnet_password'], OpenWebNetDebuggingLevel::NONE);
	$own = $own->GetDoorLockInstance();
	$own->OpenDoor($door_id);
}


$minute_start = date('i');

$actions_allowed = array();

if(!empty($argv[1])){
	$db->queryall('SELECT name FROM actions WHERE cron_group=?', $actions_allowed, 'name', $argv[1]);
}else{
	$db->queryall('SELECT name FROM actions', $actions_allowed, 'name');

}

$actions_allowed = '"'.implode('","', $actions_allowed).'"';

while($minute_start == date('i')) {

	$now_unixtime = time();
	$now = date('Y-m-d H:i:s', $now_unixtime);

	$actions = null;

	$now = date('Y-m-d H:i:s');
	$db->query('INSERT INTO config SET `key`=?, `value`=? ON DUPLICATE KEY UPDATE `value` = ?', 'last_cron_action_queue_time', $now, $now);

	$db->queryall('SELECT q.action, a.dockontrol_node_id, a.type, a.channel, dn.ip, dn.api_secret FROM action_queue q INNER JOIN actions a on q.action = a.name LEFT JOIN dockontrol_nodes dn on a.dockontrol_node_id = dn.id WHERE q.time_start <= ? AND q.executed=0 AND q.action IN ('.$actions_allowed.') GROUP BY q.action', $actions, '', $now);

	if (empty($actions)) {
		echo date('Y-m-d H:i:s') . " | ----- NO ACTIONS ------ \n";
	} else {
		foreach ($actions as $action) {
			// do action

			echo date('Y-m-d H:i:s') . " | " . $action['action'];

			switch ($action['type']) {
				case 'openwebnet':
					echo "OWN CH".$action['channel'];
					OWNOpenDoor($action['channel']);
					break;
				case 'dockontrol_node_relay':
					echo "DOCKontrol node ".$action['ip']." CH".$action['channel'];
					DoActionRemote($action['ip'], $action['channel'], 'PULSE', 300000);
					$db->query('UPDATE dockontrol_nodes SET last_command_executed_time=NOW() WHERE id=#', $action['dockontrol_node_id']);
					break;
				default:
					echo "UNKNOWN TYPE ".$action['type'];

			}

			echo "\n";

			$db->query('UPDATE action_queue SET executed=1 WHERE time_start <= ? AND executed=0 AND action = ?', $now, $action['action']);
		}

	}

	sleep(1);
}