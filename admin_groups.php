<?php

require_once dirname(__FILE__).'/libs/config.php';

/** @var Database4 $db */

_require_login();
if(!_check_permission('super_admin')){
	header('Location: /');
	exit;
}

if(!empty($_POST['save'])){

	$groups = null;
	$db->queryall('SELECT * FROM `groups`', $groups);

	$permissions = null;
	$db->queryall('SELECT * FROM `permissions`', $permissions);

	foreach($groups as $g){
		foreach ($permissions as $p){
			$key = 'group_'.$g['id'].'_permission_'.$p['name'];
			$sql = '';
			if(empty($_POST[$key])){
				// delete
				$sql = 'DELETE FROM group_permission WHERE group_id=# AND permission=?';
			}else{
				// insert
				$sql = 'INSERT IGNORE INTO group_permission SET group_id=#, permission=?';
			}

			$db->query($sql, $g['id'], $p['name']);

		}
	}

}

$group_permission_raw = null;
$db->queryall('SELECT * FROM group_permission', $group_permission_raw);

$group_permission = array();

foreach ($group_permission_raw as $gp){
	$group_permission[$gp['group_id']][$gp['permission']] = true;
}

$groups = null;
$db->queryall('SELECT * FROM `groups` ORDER BY name ASC', $groups);

$permissions = null;
$db->queryall('SELECT * FROM `permissions`', $permissions);

$smarty->assign('group_permission', $group_permission);
$smarty->assign('available_groups', $groups);
$smarty->assign('available_permissions', $permissions);


$user = _get_user_array();

$smarty->assign('user', $user);
$smarty->assign('permissions', _get_permissions());

$smarty->display('admin_groups.tpl');

?>