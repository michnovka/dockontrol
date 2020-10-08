<?php

require_once dirname(__FILE__).'/../libs/config.php';
require_once(dirname(__FILE__).'/../libs/api_libs.php');
require_once dirname(__FILE__).'/../openwebnet-php/src/OpenWebNet.php';

if(php_sapi_name() !== 'cli' || isset($_SERVER['HTTP_USER_AGENT']))
	exit;

function OWNOpenDoor($door_id){
	$own = new OpenWebNet('192.168.1.35', 20000, '12345', OpenWebNetDebuggingLevel::NONE);
	$own = $own->GetDoorLockInstance();
	$own->OpenDoor($door_id);
}


$minute_start = date('i');

$actions_allowed = array(
	'open_gate_rw1',
	'open_gate_rw3',
	'open_garage_z1', 'open_entrance_z1b1',
	'open_garage_z2', 'open_entrance_z2b1',
	'open_garage_z3', 'open_entrance_z3b1', 'open_entrance_z3b2',
	'open_garage_z7', 'open_entrance_z7b1', 'open_entrance_z7b2',
	'open_garage_z8',  'open_entrance_z8b1', 'open_entrance_z8b2',
	'open_garage_z9',  'open_entrance_z9b1', 'open_entrance_z9b2',
	'open_entrance_menclova', 'open_entrance_smrckova', 'open_entrance_smrckova_river',
	'open_entrance_menclova_z1','open_entrance_menclova_z3',
	'unlock_elevator_z7b1', 'unlock_elevator_z7b2',
	'unlock_elevator_z8b1', 'unlock_elevator_z8b2',
	'unlock_elevator_z9b1', 'unlock_elevator_z9b2',
);

if(!empty($argv[1])){
	switch ($argv[1]){
		case 'rw1':
			$actions_allowed = array('open_entrance_menclova_z1', 'open_entrance_menclova_z3', 'open_gate_rw1');
			break;
		case 'rw3':
			$actions_allowed = array('open_entrance_menclova', 'open_entrance_smrckova', 'open_entrance_smrckova_river', 'open_gate_rw3');
			break;
		case 'z1':
			$actions_allowed = array('open_garage_z1', 'open_entrance_z1b1' );
			break;
		case 'z2':
			$actions_allowed = array('open_garage_z2', 'open_entrance_z2b1', );
			break;
		case 'z3':
			$actions_allowed = array('open_garage_z3', 'open_entrance_z3b1', 'open_entrance_z3b2', );
			break;
		case 'z7':
			$actions_allowed = array('open_garage_z7', 'open_entrance_z7b1', 'open_entrance_z7b2', 'unlock_elevator_z7b1', 'unlock_elevator_z7b2', );
			break;
		case 'z8':
			$actions_allowed = array('open_garage_z8', 'open_entrance_z8b1', 'open_entrance_z8b2', 'unlock_elevator_z8b1', 'unlock_elevator_z8b2', );
			break;
		case 'z9':
			$actions_allowed = array('open_garage_z9', 'open_entrance_z9b1', 'open_entrance_z9b2', 'unlock_elevator_z9b1', 'unlock_elevator_z9b2', );
			break;
	}
}

$actions_allowed = '"'.implode('","', $actions_allowed).'"';

while($minute_start == date('i')) {

	$now_unixtime = time();
	$now = date('Y-m-d H:i:s', $now_unixtime);

	$actions = null;

	$db->queryall('SELECT action FROM action_queue WHERE time_start <= ? AND executed=0 AND action IN ('.$actions_allowed.') GROUP BY action', $actions, 'action', $now);

	if (empty($actions)) {
		echo date('Y-m-d H:i:s') . " | ----- NO ACTIONS ------ \n";
	} else {
		foreach ($actions as $action) {
			// do action

			echo date('Y-m-d H:i:s') . " | " . $action . "\n";


			switch ($action) {
				case 'open_gate_rw1':
					DoActionRemote('10.8.0.13', 1, 'PULSE', 300000);
					break;
				case 'open_gate_rw3':
					//DoAction(3, 'DOUBLECLICK', 300000, 100000);
					//DoAction(3, 'PULSE', 100000);
					DoActionRemote('192.168.1.196', 3, 'PULSE', 300000);
					break;
				case 'open_garage_z1':
					DoActionRemote('10.8.0.11', 6, 'PULSE', 300000);
					break;
				case 'open_garage_z2':
					DoActionRemote('10.8.0.12', 6, 'PULSE', 300000);
					break;
				case 'open_garage_z3':
					DoActionRemote('10.8.0.13', 2, 'PULSE', 300000);
					break;
				case 'open_garage_z9':
					//DoAction(2, 'PULSE', 100000);
					DoActionRemote('192.168.1.196', 2, 'PULSE', 300000);
					break;
				case 'open_garage_z8':
					DoActionRemote('192.168.1.197', 1, 'PULSE', 300000);
					break;
				case 'open_garage_z7':
					DoActionRemote('192.168.1.195', 1, 'PULSE', 300000);
					break;
				case 'unlock_elevator_z9b2':
					//DoAction(1, 'PULSE', 100000);
					DoActionRemote('192.168.1.196', 1, 'PULSE', 300000);
					break;
				case 'unlock_elevator_z9b1':
					//DoAction(5, 'PULSE', 100000);
					DoActionRemote('192.168.1.196', 5, 'PULSE', 300000);
					break;
				case 'unlock_elevator_z8b1':
					DoActionRemote('192.168.1.194', 3, 'PULSE', 300000);
					break;
				case 'open_entrance_z1b1':
					DoActionRemote('10.8.0.11', 8, 'PULSE', 300000);
					break;
				case 'open_entrance_z2b1':
					DoActionRemote('10.8.0.12', 8, 'PULSE', 300000);
					break;
				case 'open_entrance_z3b1':
					DoActionRemote('10.8.0.13', 3, 'PULSE', 300000);
					break;
				case 'open_entrance_z3b2':
					DoActionRemote('10.8.0.13', 4, 'PULSE', 300000);
					break;
				case 'open_entrance_z9b1':
					OWNOpenDoor(1);
					break;
				case 'open_entrance_z9b2':
					OWNOpenDoor(2);
					break;
				case 'open_entrance_z8b1':
					OWNOpenDoor(4);
					break;
				case 'open_entrance_z8b2':
					OWNOpenDoor(5);
					break;
				case 'open_entrance_z7b1':
					OWNOpenDoor(6);
					break;
				case 'open_entrance_z7b2':
					OWNOpenDoor(7);
					break;
				case 'open_entrance_menclova':
					OWNOpenDoor(0);
					break;
				case 'open_entrance_menclova_z1':
					DoActionRemote('10.8.0.13', 5, 'PULSE', 300000);
					break;
				case 'open_entrance_menclova_z3':
					DoActionRemote('10.8.0.13', 6, 'PULSE', 300000);
					break;
				case 'open_entrance_smrckova':
					OWNOpenDoor(3);
					//DoActionRemote('192.168.1.194', 1, 'PULSE', 100000);
					break;
				case 'open_entrance_smrckova_river':
					DoActionRemote('192.168.1.194', 2, 'PULSE', 300000);
					break;
			}

			$db->query('UPDATE action_queue SET executed=1 WHERE time_start <= ? AND executed=0 AND action = ?', $now, $action);
		}

	}

	sleep(1);
}