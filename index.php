<?
// 
// session_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
$GLOBALS["root_dir"] = "./";

header('Cache-control: private'); // IE 6 FIX
header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT'); 
header('Cache-Control: no-store, no-cache, must-revalidate'); 
header('Cache-Control: post-check=0, pre-check=0', false); 
header('Pragma: no-cache');

include_once("./configs/connect.inc.php");
include_once("./configs/language.inc.php");
require_once("./libs/mer/mer.system.php");
require_once("./libs/mer/mer.service.php");

header("Content-type: text/json; charset=utf-8");

die(JSON::createMessage(-123, JSON_MSG_DATA_EMPTY));
$router = new Router(array(
    "get"=>$_GET,
    "method"=>$_SERVER['REQUEST_METHOD'],
    "params"=>file_get_contents('php://input')
  ));

if ($url = Settings::getActiveConnection($router->profile)) {
	$conn = new Connector($router);
	$msg = $conn->execute($url); 	
//header("Content-type: application/json; charset=utf-8");
	echo $msg;
} else {
  echo JSON::createMessage(-10, JSON_MSG_DATA_EMPTY);
}

?>