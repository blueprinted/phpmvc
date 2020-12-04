<?php

define("APP_NAME", "sginputjbp");
define("APP_AUTHKEY", APP_NAME.'~!');
define("COOKIE_DOMAIN", isset($_SERVER['HTTP_HOST']) ? (preg_match('/^\d{1,3}(\.\d{1,3}){3}$/', $_SERVER['HTTP_HOST']) || strtolower($_SERVER['HTTP_HOST']) == 'localhost' ? $_SERVER['HTTP_HOST'] : ".{$_SERVER['HTTP_HOST']}") : '');
define("COOKIE_PREFIX", APP_NAME.'_');
define("TIME_SHOW_FORMAT", 'Y/m/d H:i:s');
define("TABLE_PREFIX", APP_NAME.'_');

define("ENABLE_VERIFYCODE_LOGIN", true); // 是否开启登录验证码
define("VERIFYCODE_VAR_LOGIN", 'login'); // 登录验证码的session变量名

define("ENABLE_VERIFYCODE_REGISTER", true); // 是否开启注册验证码
define("VERIFYCODE_VAR_REGISTER", 'register'); // 注册验证码的session变量名

define("ENABLE_INVITE_REGISTER", false); // 是否需要邀请才能注册
