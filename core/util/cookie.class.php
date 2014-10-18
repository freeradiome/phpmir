<?php
/**
 * 说明：Cookie管理类
 * 作者：RobertZeng <zeng444@163.com>
 * 日期：2011-09-01
 * 版本：1.0
 */
class Cookie {
	/* 成员变量 */
	public $_cookie_pre = '';
	public $_cookie_expire = '';
	public $_cookie_path = '';
	public $_cookie_domain = '';
	
	/**
	 * 构造函数
	 *
	 * @param string 	$cookie_pre		Cookie前缀
	 * @param int	 	$cookie_expire	过期时间
	 */
	public function __construct($cookie_config=array()){
		
		if ( isset($cookie_config['cookie_pre']) ){
			$this->_cookie_pre = $cookie_config['cookie_pre'];
		}
		if ( isset($cookie_config['cookie_expire']) ){
			$this->_cookie_expire = $cookie_config['cookie_expire'];
		}
		if ( isset($cookie_config['cookie_path']) ){
			$this->_cookie_path = $cookie_config['cookie_path'];
		}
		if ( isset($cookie_config['cookie_domain']) ){
			$this->_cookie_domain = $cookie_config['cookie_domain'];
		}
	}
	
	/**
	 * 获得实例句柄
	 *
	 * @return object
	 */
	public static function getInstance() {
        $args = func_get_args();
        return get_instance_of(__CLASS__,'factory',$args);
    }
	
    /**
     * 创建对象
     *
     * @param mixed $cookie_config	Cookie配置
     * @return unknown
     */
	public function &factory($cookie_config=''){
		if ( empty($cookie_config)&&isset($GLOBALS['cookie_config']) ){
			$cookie_config = $GLOBALS['cookie_config'];
		}
		$className = __CLASS__;
		$o = new $className($cookie_config);
		return $o;
	}
	
	/**
	 * 判断是否存在
	 *
	 * @param string	$name
	 * @return bool
	 */
	public function is_set($name){
		$tag = $this->_cookie_pre.$name;
		return isset($_COOKIE[$tag]);
	}

	/**
	 * 获取某个Cookie值
	 *
	 * @param string $name
	 * @return string
	 */
	public function get($name) {
		$tag = $this->_cookie_pre.$name;
		if ( isset($_COOKIE[$tag]) ){
			return $_COOKIE[$tag];
		}
		else{
			return null;
		}
	}
	
	/**
	 * 设置某个Cookie值
	 *
	 * @param string		$name
	 * @param string		$value
	 * @param string		$expire
	 * @param string		$path
	 * @param string		$domain
	 */
	public function set($name,$value,$expire='',$path='',$domain=''){
		if ( $expire=='' ){
			$expire = $this->_cookie_expire;
		}
		if ( empty($path) ){
			$path = $this->_cookie_path;
		}
		if ( empty($domain) ){
			$domain = $this->_cookie_domain;
		}
		$expire = !empty($expire)?time()+$expire:0;
		$tag = $this->_cookie_pre.$name;
		setcookie($tag, $value,$expire,$path,$domain);
		$_COOKIE[$tag] = $value;
	}
	
	/**
	 * 删除某个Cookie值
	 *
	 * @param string	$name
	 */
	public function delete($name){
		$this->set($name,'',time()-3600);
		$tag = $this->_cookie_pre.$name;
		if ( isset($_COOKIE[$tag]) ){
			unset($_COOKIE[$tag]);
		}
	}
	
	/**
	 * 清空Cookie值
	 *
	 */
	public function clear(){
		foreach ($_COOKIE as $k=>$v){
			setcookie($k, '',time()-3600);
		}
		unset($_COOKIE);
	}
}
//类定义结束
?>