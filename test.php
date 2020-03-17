<?php
require_once "Mysql.php";
use yxmingy\Mysql;
$db = new Mysql('localhost',  'user', 'passwd', 'database');
if(!$db->connected()){
	die($db->getConnectError());
}
echo 'Success... ' . $db->host_info . "\n";
$table = "mp_user";
if(!$db->insert($table,[
	"nick" => "test2",
	"pwd" => "123456",
	"email" => "test2@qq.com",
	"state" => "0",
	"captcha" => "wtf"
])) echo($db->getStmtError().PHP_EOL);
var_dump($db->selectById($table,"email","nick","test2"));

$db->close();