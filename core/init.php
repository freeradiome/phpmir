<?php 
ob_start();
//设置输出和日期
header("Content-Type: text/html; charset=utf-8");  //设置页面编码
//告诉客户端浏览器不使用缓存  
header("Cache-Control:no-cache, must-revalidate");  
//参数（与以前的服务器兼容）,即兼容HTTP1.0协议  
header("Pragma:no-cache");      

date_default_timezone_set('Asia/Shanghai');		//设置时区

//获取网站路径
define('ROOT_PATH', str_replace('core/init.php', '', str_replace('\\', '/', __FILE__)));
define('CORE_PATH',ROOT_PATH .'core/');
//关闭Magic_quotes_gpc
if ( get_magic_quotes_gpc() ){
	//die('你需要关闭Magic_quotes_gpc，否则可能会引起未知的错误');
	//@ini_set('display_errors',        1);
	//@ini_set('magic_quotes_gpc',1);
}
require(CORE_PATH.'sys/function.php');
$GLOBALS['rt'] = Runtime::getInstance();
require(ROOT_PATH.'config/db.config.php');
require(CORE_PATH.'function/function.php');

set_error_handler(array('sException','__error_handler'));
set_exception_handler(array('sException','__exception_handler'));
?>