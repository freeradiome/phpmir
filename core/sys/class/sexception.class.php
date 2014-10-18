<?php 
/**
 * 说明：系统异常处理
 * 作者：RobertZeng <zeng444@163.com>
 * 日期：2011-09-01
 * 版本：1.0
 */
class sException extends Exception {

	/**
	 * 抛出异常
	 *
	 * @param array $error_array
	 * @param string $errstr
	 * @param int $errno
	 */
	public static function __exception_handler($e){
		if(debug==true){
			$strace = $e->getTrace();
			$errstr = $e->getMessage();
			$errno = -1000 ;
			self::__display( $strace, $errstr , $errno );
			exit();
		}
	}

	/**
	 * 抛出错误
	 *
	 * @param int $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param int $errline
	 */
	public static function __error_handler($errno,$errstr,$errfile,$errline){
		if( debug==true ){
			if( $errno==E_NOTICE||$errno==E_WARNING||$errno==E_STRICT ){
//							if( $errno==E_WARNING||$errno==E_STRICT ){
				return;
			}
			$strace = debug_backtrace();
			array_shift($strace);
			self::__display( $strace, $errstr , $errno );
			exit();
		}else{
			//echo '没有开启调试!';
			//die();
		}
	}


	/**
	 * 记录错误或者异常到日志
	 *
	 * @param unknown_type $message
	 * @return unknown
	 */
	public static function save_error_log($error_array,$errstr,$errno){
		return true;
	}


	/**
	 * 渲染模板
	 *
	 * @param array $error_array
	 * @param string $errstr
	 * @param int $errno
	 */
	public static function __display($error_array,$errstr,$errno){

		define( 'debug_tpl',CORE_PATH.'sys/class/exceptionfile/error.html' ); //错误日志调试
		if(defined('logged') && logged){
			self::save_error_log($error_array,$errstr,$errno);
		}
		if( defined('debug_tpl') && file_exists(debug_tpl) ){
			include_once( debug_tpl );
		}else {
				
			die('没有定义处理异常的渲染模板！');
		}

	}



}

?>