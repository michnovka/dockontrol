<?php

require_once dirname(__FILE__).'/libs/config.php';

_require_login();
if(!_check_permission('admin')){
	header('Location: /');
	exit;
}

$user = _get_user_array();

$cameras = null;

$db->queryall('SELECT c.name_id FROM cameras c INNER JOIN permissions p on c.permission_required = p.name INNER JOIN group_permission gp on p.name = gp.permission INNER JOIN user_group ug on gp.group_id = ug.group_id WHERE user_id=# GROUP BY c.name_id ORDER BY c.name_id', $cameras, 'name_id', $_SESSION['id']);

$smarty->assign('cameras', $cameras);

$smarty->assign('user', $user);
$smarty->assign('permissions', _get_permissions());

$smarty->display('admin_cameras.tpl');

?>
