<?php 
class Runtime{

	//查询消耗时间
	public $_runtime = 0;
	
	//日志路径
	public $_file_path = 'log/';

	//查询开始时间
	public $_begin_time = 0;
	
	//当前实例
	public static $_instance = null;


	
	/**
	 * 取得唯一实例
	 *
	 * @param unknown_type $config
	 * @return unknown
	 */
	static public function getInstance(){
		if(self::$_instance==null){
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	function __construct(){
		$this->_begin_time = $this->get_execute_time();
	}

	public   function  write($message){
		$path = $this->_file_path . date('Y-m-d',time()).'.txt' ;
		error_log($this->tpl($message), 3, $path);
	}


	public function tpl($message){
		$content = "message:".date('m-d H:i:s')."\n";
		$content .= "message:".$message."\n";
		$content .= "l-n-time:".$this->get_execute_time()."\n";
		$content .= str_repeat('=',20)."\n";
		return $content;
	}



	/**
	 * 调试信息
	 *
	 */
	public function get_execute_time(){
		$_end_time = $this->micro_time();
		$execute_time =  $_end_time-$this->_begin_time;
		$this->_begin_time = $_end_time;
		return $execute_time;
	}

	/**
	 * 获取微妙时间轴
	 *
	 * @return unknown
	 */
	public function  micro_time(){
		return array_sum(explode(' ',microtime()));
	}

}
//$log=Log::getInstance();

?>