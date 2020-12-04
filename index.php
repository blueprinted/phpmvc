<?php

require __DIR__ . '/common.php';

// 解析url
$mod = isset($_GET['mod']) ? $_GET['mod'] : 'index';
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'index';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

$mvcfile = APP_MODULE."/{$mod}/{$controller}_{$action}.php";
if (!file_exists($mvcfile)) {
    apimessage(88, "{$mod}/{$controller}->{$action} not exists");
}
include $mvcfile;
