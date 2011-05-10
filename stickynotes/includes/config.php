<?php
define('MODX_API_MODE', true);
include '../../../../index.php';

define('STICKY_BASE_URL', 'assets/templates/manager/stickynotes/');
define('STICKY_BASE_PATH', MODX_BASE_PATH . 'assets/templates/manager/stickynotes/');

$table = $modx->db->config['table_prefix'] ."stickynotes";
$modx->db->query("
	CREATE TABLE IF NOT EXISTS `$table` (
		`id` INT(9) NOT NULL AUTO_INCREMENT,
		`title` VARCHAR(255) NOT NULL DEFAULT '',
		`comment` TEXT NOT NULL DEFAULT '',
		`finished` TINYINT(1) NOT NULL DEFAULT '0',
		`archived` TINYINT(1) NOT NULL DEFAULT '0',
		`deleted` TINYINT(1) NOT NULL DEFAULT '0',
		`progress` TINYINT(3) NOT NULL DEFAULT '0',
		`user` INT(10) NOT NULL,
		`to_user` INT(10) NULL,
		`createdon` INT(12) NOT NULL,
		`finishedon` INT(12) NOT NULL,
		`must_be_finishedon` INT(12) NOT NULL,
		PRIMARY KEY  (`id`)
	) TYPE=MyISAM;
");

if(!function_exists('sticky_json')) {
	if(!function_exists('json_encode')) {
		include_once './services_json.php';
		function sticky_json($str) {
			$json = new Services_JSON();
			return $json->encode($str);
		}
	} else {
		function sticky_json($str) {
			return json_encode($str);
		}
	}
}

if(!function_exists('sticky_getPanel')) {
	function sticky_getPanel($data) {
		if($data['deleted'] == 1) { $panel = 'stk-trash'; }
		else {
			if($data['archived'] == 1) { $panel = 'stk-archive'; }
			else {
				if($data['finished'] == 1) { $panel = 'stk-finished'; }
				else {
					if(isset($data['to_user'])) { $panel = 'stk-todo'; }
					else { $panel = 'stk-main'; }
				}
			}
		}
		return $panel;
	}
}
?>
