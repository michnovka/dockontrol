<?php


require_once dirname(__FILE__).'/libs/lib_qrcode.php';

$size = 5;
if(!empty($_GET['size']))
	$size = intval($_GET['size']);

QRcode::png($_GET['content'], false, QR_ECLEVEL_H, $size);