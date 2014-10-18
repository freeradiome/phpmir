<?php
/**
 * redis
 * 修改历史
 * 日期 作者 修改内容
 * 2010 robert
 * 
 */
class Mredis  {
	/* 成员变量 */
	
	// 句柄
	private $_handler = null;
	//实例句柄
	
	/**
	 * 构造函数
	 *
	 * @param array		$redis_config
	 */
	public function __construct($redis_config=''){
		if ( !extension_loaded('redis') ) {    
            throw_exception('系统不支持redis');
        }
		$this->_handler = new Redis;
		if ( $redis_config ){
			if ( count($redis_config)==1 ){
				$this->connect( current($redis_config) );
			}
		}
	}
	
	/**
	 * 析构函数
	 *
	 */
	public function __destruct(){
		if ( $this->_handler ){
			@$this->_handler->quit;
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
     * @param mixed $redis_config	redis配置
     * @return unknown
     */
	public function &factory($redis_config=''){
		if ( empty($redis_config) ){
			global $redis_config;
		}
		$className = __CLASS__;
		$o = new $className($redis_config);
		return $o;
	}
	
	/**
	 * 根据配置信息，连接服务器
	 * @access public
	 *
	 * @param array		$config
	 * @return bool
	 */
	public function connect($config){
		if ( isset($config['host'])&&isset($config['port']) ){
			return $this->_handler->connect($config['host'],$config['port'],$config['timeout']=0);
		}
		else {
			return false;
		}
	}
	/**
	 * 查看是否连接，返回PONG代表已连接
	 * @access public
	 * @return bool
	 */
	public function ping(){
		return $this->_handler->ping();	
	}
	/**
	 * md5 hash 函数
	 * 为防止key过长导致无法记录
	 *
	 * @param string	$key
	 * @return string
	 */
	public function hashKey($key){
		return md5($key);
	}
	
	/**
	 * 设置保存数据
	 *
	 * @param string	$key
	 * @param string	$value
	 * @param int		$expire
	 * @return mixed
	 */
	public function set($key, $value, $expire = 0){
		if ( !$this->_handler ){
			return false;
		}elseif($expire){
			return $this->_handler->setex ($this->hashKey($key),$expire,$value);
		}else{
			return $this->_handler->set($this->hashKey($key),$value,$expire);	
		}
	}
	
	/**
	 * 获取redis存储的数据
	 *
	 * @param string		$key
	 * @return mixed
	 */
	public function get($key){
		if ( !$this->_handler ){
			return false;
		}
		return $this->_handler->get($this->hashKey($key));
	}
	/**
	 * 替换redis中存在的变量
	 *
	 * @param string	$key
	 * @param string	$data
	 * @param int		$expire
	 * @return mixed
	 */
	public function replace($key, $data, $expire=0){
		if ( !$this->_handler ){
			return false;
		}
		return $this->_handler->getSet($this->hashKey($key), $data);
	}
	/**
	 * 从redis服务器上删除一个key和它的值
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function delete($key){
		if ( !$this->_handler ){
			return false;
		}			
		return $this->_handler->del($this->hashKey($key));	
		
	}
	/**
	 * 获取redis版本信息
	 *
	 * @return mixed
	 */
	public function version(){
		if ( !$this->_handler ){
			return false;
		}
		return $this->_handler->info();
	}
	/**
	 * 将数据同步保存到磁盘
	 *
	 * @return mixed
	 */
	public function save(){
		if ( !$this->_handler ){
			return false;
		}
		return $this->_handler->save();
	}
	/**
	 * 将数据异步保存到磁盘
	 *
	 * @return mixed
	 */
	public function bgsave(){
		if ( !$this->_handler ){
			return false;
		}
		return $this->_handler->bgsave();
	}
	/**
	 * 将数据异步保存到磁盘
	 *
	 * @return mixed
	 */
	public function lastSave(){
		if ( !$this->_handler ){
			return false;
		}
		return $this->_handler->lastSave();
	}

}
?>