<?php

if(php_sapi_name() !== 'cli' || isset($_SERVER['HTTP_USER_AGENT']))
	exit;

require_once dirname(__FILE__).'/../libs/config.php';
require_once dirname(__FILE__).'/../libs/fetch_camera_picture.php';

$minute_start = date('i');

error_reporting(E_ALL);
ini_set('display_errors','1');

$camera_name = $argv[1];
$camera = $db->queryfirst('SELECT stream_url, stream_login, name_id FROM cameras WHERE name_id=? LIMIT 1', $camera_name);

while($minute_start == date('i')) {
	if (empty($camera)) {
		echo "No camera called " . $camera_name;
	} else {
		$photo_data = fetchCameraPicture($camera['stream_url'], $camera['stream_login']);
		echo md5($photo_data);
		$db->query('UPDATE cameras SET data_jpg=??, last_fetched = NOW() WHERE name_id=?', $photo_data, $camera['name_id']);
		echo "DONE\n";
	}
}