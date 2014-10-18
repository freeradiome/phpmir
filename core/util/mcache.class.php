<?php
/**
 * memcached
 * 修改历史
 * 日期 作者 修改内容
 * 2010 robert
 * 
 */
class Mcache  {
	/* 成员变量 */
	
	// 句柄
	private $_handler = null;
	//实例句柄
	
	/**
	 * 构造函数
	 *
	 * @param array		$mcache_config
	 */
	public function __construct($mcache_config=''){
		
		if ( !extension_loaded('memcache') ) {    
            throw_exception('系统不支持memcache');
        }
		$this->_handler = new Memcache;
		if ( $mcache_config ){
			if ( count($mcache_config)==1 ){
				$this->connect( current($mcache_config) );
			}
			else {
				foreach ($mcache_config as $k=>$v){
					$this->addServer($v);
				}
			}
		}
	}
	
	/**
	 * 析构函数
	 *
	 */
	public function __destruct(){
		if ( $this->_handler ){
			@$this->_handler->close();
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
     * @param mixed $mcache_config	Memcache配置
     * @return unknown
     */
	public function &factory($mcache_config=''){
	
		if ( empty($mcache_config) ){
			global $mcache_config;
		}
		$className = __CLASS__;
		$o = new $className($mcache_config);
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
			return $this->_handler->connect($config['host'],$config['port']);
		}
		else {
			return false;
		}
	}
	
	/**
	 * 添加Memcache服务器
	 *
	 * @param array $config
	 * @return bool
	 */
	public function addServer($config){
		if ( isset($config['host'])&&isset($config['port']) ){
			return $this->_handler->addServer($config['host'],$config['port']);
		}
		else {
			return false;
		}
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
		}
		$this->debug($key,'set');
		return $this->_handler->set($this->hashKey($key),$value,MEMCACHE_COMPRESSED,$expire);
	}
	
	/**
	 * 获取Memcache存储的数据
	 *
	 * @param string		$key
	 * @return mixed
	 */
	public function get($key){
		if ( !$this->_handler ){
			return false;
		}
		$this->debug($key,'get');
		return $this->_handler->get($this->hashKey($key));
	}
	
	/**
	 * 替换memcached中存在的变量
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
		$this->debug($key,'replace');
		return $this->_handler->replace($this->hashKey($key), $data, MEMCACHE_COMPRESSED, $expire);
	}
	
	/**
	 * 从Memcached服务器上删除一个key和它的值
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function delete($key){
		if ( !$this->_handler ){
			return false;
		}
		
		$this->debug($key,'delete');
		
		return $this->_handler->delete($this->hashKey($key));	
		
	}
	
	/**
	 * 获取Memcache状态
	 *
	 * @return mixed
	 */
	public function stat(){
		if ( !$this->_handler ){
			return false;
		}
		return $this->_handler->getStats();
	}
	
	/**
	 * 获取Memcache版本
	 *
	 * @return mixed
	 */
	public function version(){
		if ( !$this->_handler ){
			return false;
		}
		return $this->_handler->getVersion();
	}
	
	/**
	 * 清空Memcache
	 *
	 * @return bool
	 */
	public function flush(){
		if ( !$this->_handler ){
			return false;
		}
		return $this->_handler->flush();
	}
	
	/**
	 * 调试数据
	 *
	 * @param string $key		键值
	 * @param string $op		操作
	 * @return bool
	 */
	public function debug($key,$op){
		if ( defined('MEM_CACHE_LOG')&&MEM_CACHE_LOG ){
			$path = ROOT_PATH.'log/memcache_log/'.date('Y/m/d');
			mk_dir($path);
			$log_file = $path.'/memcache.log';
			$msg = '';
			switch ($op){
				case 'get':
					$msg = '读取 '.$key.' ';
				break;
				case 'set':
					$msg = '设置 '.$key.' ';
				break;
				case 'replace':
					$msg = '替换 '.$key.' ';
				break;
				case 'delete':
					$msg = '删除 '.$key.' ';
				break;
				default:
					return false;
				break;
			}
			$msg .= chr(10);
			$fp = fopen($log_file,'a+');
			if ( $fp ){
				fwrite($fp,$msg);
				fclose($fp);
				return true;
			}
			return false;
		}
	}
}
?>