<?php

require_once dirname(__FILE__).'/libs/config.php';
require_once dirname(__FILE__).'/libs/api_libs.php';

_require_login();
if(!_check_permission('super_admin')){
	header('Location: /');
	exit;
}

if(!empty($_GET['action']) && $_GET['action']=='update_node'){
	$node = $db->queryfirst('SELECT * FROM dockontrol_nodes WHERE id=#', $_GET['node_id']);
	if(empty($node)){
		echo "Node does not exist";
		exit;
	}

	if($node['status'] != 'online'){
		echo "I can only update ONLINE nodes. This node is ".$node['status'];
		exit;
	}

	$reply = json_decode(CallDOCKontrolNode($node['ip'], $node['api_secret'], 'update'), true);

	if($reply['status'] == 'ok') {
		$smarty->assign('success', 'Updated node #' . $node['id'] . ' ' . $node['name'] . ' from ' . $reply['old_version'] . ' to ' . $reply['new_version']);
		$db->query('UPDATE dockontrol_nodes SET dockontrol_node_version=? WHERE id=#', $reply['new_version'], $node['id']);
	}else
		$smarty->assign('error', $reply['code'].':'.$reply['message']);

}

$nodes = null;
$db->queryall('SELECT * FROM dockontrol_nodes ORDER BY name', $nodes);

$smarty->assign('nodes', $nodes);

$user = _get_user_array();

$smarty->assign('user', $user);
$smarty->assign('permissions', _get_permissions());

$smarty->display('admin_monitor_nodes.tpl');

?>
