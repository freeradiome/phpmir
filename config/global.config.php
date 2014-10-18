<?php



//默认数据库设置
define('default_db_type','mysql');
define('default_db_host','192.168.82.30'); 
define('default_db_username','root');
define('default_db_password','root');
define('default_db_database','wo');
define('default_db_charset','utf8');


//缓存设置
define('MEM_CACHE',true); //是否开启memche
define('DEFAULT_MEMCACHE_HOST','localhost');
define('DEFAULT_MEMCACHE_PORT',11211);

//队列服务设置
define('REDIS_CACHE',true); //是否开启REDIS
define('DEFAULT_REDIS_HOST','192.168.87.45');
define('DEFAULT_REDIS_PORT',6379);


//控制器页面缓存配置
define('PAGE_CACHE_TYPE','file'); //memcache,redis,file

//debug设定
define('debug',true); //是否开启错误调式
define('log',true); //是否开启日志
define('log_size',1024); //日志大小

//性能测试开关
define('bench',false); 

//cookie设置
$GLOBALS['cookie_config'] = array(
'cookie_pre'=>'',
'cookie_path'=>'/',
'cookie_domain'=>''
);


?>