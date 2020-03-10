<?php
require_once "Mysql.php";
use yxmingy\Mysql;
$db = new Mysql('rm-m5e936c6x8o4g3q3buo.mysql.rds.aliyuncs.com',  'ndt_001', 'aqi275466_', 'tnb');

echo 'Success... ' . $db->host_info . "\n";

var_dump($db->showSingle("DATABASES"));

$db->close();