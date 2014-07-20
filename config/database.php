<?php

/**
 * 数据库相关配置，该配置不同步生产，需管理员手动设置
 */

class DATABASE_CONFIG {

	var $default = array('driver' => 'mysqli',
		'connect' => 'mysql_connect',
		'host' => 'localhost',
		'login' => 'duosq',
		'password' => '123456',
		'database' => 'duosq',
		'prefix' => '');

	var $promotion = array('driver' => 'mysqli',
		'connect' => 'mysql_connect',
		'host' => 'localhost',
		'login' => 'duosq',
		'password' => '123456',
		'database' => 'duosq_promotion',
		'prefix' => '');
}

?>
