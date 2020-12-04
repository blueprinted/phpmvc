<?php

//DB配置文件

global $_DBCFG;

$_DBCFG['master_db'] = array(
	'host' => 'localhost',
	'port' => '3306',
	'user' => 'root',
	'pass' => '',
	'dbname' => 'test',
	'charset' => 'utf8mb4',
);

if(ENVIRONMENT != 'product') {
	foreach($_DBCFG as $key => $value) {
		$_DBCFG[$key] = array(
			'host' => '127.0.0.1',
			'port' => '3306',
			'user' => 'root',
			'pass' => '',
			'dbname' => 'test',
			'charset' => 'utf8mb4',
		);
	}
}
