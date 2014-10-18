<?php 
define('WEB_ROOT','http://wo.cn/');
define('PUBLIC_ROOT','http://wo.cn/themes/public/');
define('WEB_NAME','一战到底');
//路由设置
define('pathinfo',true); //路由方式
define('CONTROLLER_PATH',ROOT_PATH .'controller/'); //控制器路径
define('MODULE_PATH',ROOT_PATH .'modules/'); //模型层路径
define('VIEWER_PATH',ROOT_PATH.'themes/default/'); //视图路径
//模版的配置
define('tpl_ext','html');

//文字提示
$GLOBALS['text']=array(
	'nodata'=>'暂无内容'
);

//class栏目名绑定的栏目ID
$category_id_map = array(
	'answer'=>40,
	'business'=>31,
	'forecast'=>32,
	'notice'=>39,
	'rule'=>41,
	'service'=>42,
	'user'=>43,
	'sms'=>44
);

?>