<?php
///**
// * loading
// *
// * @param string $className
// */
//function __autoload__($className){
//
//	global $class_config;
//	$className = strtolower($className);
//	if( isset($class_config[$className] ) ){
//		$classFile = $class_config[$className];
//	}else{
//		$classFile = 'util/'.$className.'.class.php';
//	}
//	if(file_exists(CORE_PATH.$classFile)){
//		require_once(CORE_PATH.$classFile);
//	}
//}
//

/**
 * 读取参数
 *
 * @param string		$name 变量名
 * @param string		$type 过滤成的类型
 * @return mixed
 */
function _g($name, $type = ''){


	if ( isset($_GET[$name]) ){
		$ret = $_GET[$name];
	}
	else if ( isset($_POST[$name]) ){
		$ret = $_POST[$name];
	}
	else if ( isset($_REQUEST[$name]) ){
		$ret = $_REQUEST[$name];
	}
	else{
		$ret = false;
	}
	if ($ret !== false && $type != ''){
		if ( $type == 'int' ){
			$ret = intval($ret);
		}
		else if ( $type == 'str' ){
			$ret = strval($ret);
		}
		else{
			settype($ret, $type);
		}
	}
	return $ret;
}
/**
 * 自动载入方式2
 *
 * @param string $className 方法名e a 
 */
function __autoload($className){
	$class_config = require( CORE_PATH.'classmap/class.config.php' );
	if( isset($class_config[$className]) ){
		$include_file = CORE_PATH.$class_config[$className];
	}else{
		$classFile = strtolower($className).'.class.php';
		$include_file = MODULE_PATH.$classFile;
	}
	if(!file_exists($include_file)){
		throw_exception("对不起你访问的对象$className,路径：$include_file 不存在");
	}
	require_once($include_file);
}



/**
 * 获得实例对象
 *
 * @param string $className
 * @param string $method
 * @param array $args
 * @return object
 */
function get_instance_of($className,$method='',$args=array()){
	if( class_exists($className) ){
		$obj = new $className;
		if(!empty($args)){
			$new = call_user_func_array(array(&$obj,$method),$args);
		}else{
			$new = $obj->$method();
		}
		
		return $new ;
	}else{
		throw_exception('此类不存在');
	}
}


/**
 * 对数据进行批量XSS及TRIM过滤
 *
 * @param mixed $data 数组或者字符
 * @return mixed 过滤后的数组或字符
 */
function trim_xss($data){
	if(is_array($data)){
		$data = array_map('trim',$data);
		$data = array_map('htmlspecialchars',$data);
		$data = array_map('mysql_escpage',$data);
		return $data;
	}
	return 	mysql_escape_string(htmlspecialchars(trim($data)));

}

/**
 * 过滤字符串
 *
 * @param string $str
 * @return string
 */
function mysql_escpage($str) {
	return mysql_escape_string($str);//_real
}
/**
 * 格式化表值
 *
 * @param string $str 表值
 * @return unknown
 */
function format_table_value($str){
	return "'$str'";
}
/**
 * 格式化表名
 *
 * @param string $str 表名
 * @return unknown
 */
function format_table_key($str){
	return "`$str`";
}


/**
 * 抛出异常
 *
 * @param string $message
 * @param string $name
 */
function throw_exception($message,$name='s'){
	$className = $name.'exception';
	if(class_exists($className)){
		throw new $className($message);
	}
}




/**
 * 生成并输出到指定路由的URL地址
 *
 */
function run($class,$method='index',$query_build=array(),$base= WEB_ROOT,$basefile = 'index.php',$query = array('class'=>'class','method'=>'method')){
	
	$url = get_router($class,$method,$query_build,$base,$basefile,$query);
	echo $url;
}

/**
 * get_router的别名方法
 * zeng444@163.com
 *
 * @param unknown_type $class 控制器
 * @param unknown_type $method 方法
 * @param unknown_type $query_build _GET参数
 * @param unknown_type $base 网站根目录
 * @param unknown_type $basefile 路由文件名
 * @param unknown_type $query 控制器的变量名
 * @return unknown 路由的URL地址
 */
function url($class,$method,$query_build,$base= WEB_ROOT,$basefile = 'index.php',$query = array('class'=>'class','method'=>'method')){
	return get_router($class,$method,$query_build,$base,$basefile ,$query );
}
/**
 * 按pathinfo开关生成URL地址
 * zeng444@163.com
 *
 * @param unknown_type $class 控制器
 * @param unknown_type $method 方法
 * @param unknown_type $query_build _GET参数
 * @param unknown_type $base 网站根目录
 * @param unknown_type $basefile 路由文件名
 * @param unknown_type $query 控制器的变量名
 * @return unknown 路由的URL地址
 */
function get_router($class,$method='index',$query_build,$base= WEB_ROOT,$basefile = 'index.php',$query = array('class'=>'class','method'=>'method')){
	
	if(!$class || $class[0]=='/') return $base;
	$query_str = (empty($query_build)) ? '' : http_build_query($query_build);
    if(pathinfo){
		$query_str = ($query_str)?'?'.$query_str:'';
		$url = "$class/$method"."$query_str";
    } else {
        if($method) {
            $method_url = '&' . $query['method'] . '=' . $method;
        }
		$query_str = ($query_str) ? '&'.$query_str : '';
		$url = $basefile . '?' . $query['class'] . '=' . $class . $method_url . $query_str;
	}
	return   $base.$url;
}

/**
 * 跳转到上一页地址
 *
 */
function url_back(){
	$url =$_SERVER["HTTP_REFERER"];
	redirect($url,false);
}

/**
 * URL重定向
 *
 * @param string 	$url	要定向的URL地址
 * @param string 	$route	是否开启pathinfo短格式地址模式
 * @param integer 	$time	定向的延迟时间，单位为秒
 * @param string 	$msg	提示信息
 */
function redirect($url,$route=true,$time=0,$msg=''){

	if($route){
		$route = explode('/',$url);
//		$url_this = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
//		$url_this =dirname($url_this).'/';
		$url_this = WEB_ROOT;
		$url = get_router($route[0],$route[1],array(),$url_this);
	}

	//多行URL地址支持
	$url = str_replace(array("\n", "\r"), '', $url);
	if(empty($msg)) {
		$msg = "系统将在{$time}秒之后自动跳转到{$url}！";
	}
	if ( !headers_sent() ) {
		// 如果http头未被发送的话
		header("Content-Type:text/html; charset='UTF-8'");
		if(0===$time) {
			header("Location: ".$url);
		}
		else {
			header("refresh:{$time};url={$url}");
			echo($msg);
		}
		exit();
	}
	else {
		$str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
		if( $time!=0 ) {
			$str .= $msg;
		}
		exit($str);
	}
}


?>