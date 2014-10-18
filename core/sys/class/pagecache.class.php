<?php
/**
 * 自动对页面进行整页缓存
 * robert zeng 2012-08-08
 *
 */
class pageCache{

	//默认过期时间
	public $_expire = 100;

	//使用的缓存类型
	public $_cache_type = 'file';

	//缓存对象实例
	public $_cache_object;

	//缓存的页面KEY
	public $_cached_page_key;

	//是否抓取新缓存
	public $_is_cached =false;

	//缓存类型
	public  $_cache_type_lists = array(
	'memcache'=>'getMemCache',
	'redis'=>'getRedis',
	'file'=>'getFileCache'
	);

	//取得唯一句柄
	static public $_instance=null;

	//对象初始化
	function __construct($params){
		if( PAGE_CACHE_TYPE ){
			$this->_cache_type = PAGE_CACHE_TYPE ;
		}
		if(isset($params['_expire'])){
			$this->_expire = $params['_expire'];
		}
		

		if( !array_key_exists($this->_cache_type, $this->_cache_type_lists) ){
			throw_exception('不支持的页面缓存类型');
		}
		$this->_cached_page_key = self::generate_page_hash_key($_GET);
		call_user_func(array($this,$this->_cache_type_lists[$this->_cache_type]));
	}


	/**
	 * 取得唯一实例
	 *
	 * @param unknown_type $config
	 * @return unknown
	 */
	static public function getInstance($config){

		if(self::$_instance==null){
			self::$_instance = new self($config);
		}
		return self::$_instance;
	}

	/**
	 * 建立一个文件缓存实例
	 *
	 */
	protected  function getFileCache(){

		$this->_cache_object = Fcache::getInstance();
		return $this->_cache_object;
	}

	/**
	 * 建立一个redis实例
	 *
	 */
	protected  function getRedis(){
		if(! REDIS_CACHE ) {
			throw_exception('mem页面缓冲功能需要开启redis');
		}
		global $redis_config;		
		$this->_cache_object = Mredis::getInstance($redis_config);	
		return $this->_cache_object;
	}

	/**
	 * 建立一个memcaced连接
	 *
	 */
	protected  function getMemCache(){
		if(! MEM_CACHE ) {
			throw_exception('mem页面缓冲功能需要开启memcache');
		}
		global $mcache_config;
		$this->_cache_object = Mcache::getInstance($mcache_config);
		return $this->_cache_object;
	}

	/**
	 * 从缓存对象设置一个key值
	 *
	 * @param string $key 键值
	 * @param string $value 值
	 * @param int $expire 缓存周期 file缓存时无效
	 * @return string
	 */
	function set($key,$value,$expire){
		$expire = ($expire)?$expire:$this->_expire;
		return  $this->_cache_object->set($key,$value,$expire);
	}

	/**
	 * 从缓存对象获取一个key值
	 *
	 * @param string $key 键值
	 * @return string
	 */
	function get($key){
		return $this->_cache_object->get($key);
	}


	/**
	 * 读取缓存中的页面数据
	 *
	 */
	public  function get_cache_method_page(){
		
		$html = $this->get( $this->_cached_page_key );
		if( $html ) {
			$this->_is_cached =false;
			echo $html;
			exit();
		}else {
			$this->_is_cached =true;
			ob_start();
		}

	}

	/**
	 * 静态缓存删除
	 *
	 * @param array $class_method 页面路由的对象和方法
	 * @param array $query _get参数,数组key,value一一对应
	 * @return bool
	 */
	static public function delete($class_method=array(),$query=array()){
		if(empty($class_method)) throw_exception('请填写缓存页面的路由地址');
		$params = array_merge($query,array(
		'class'=>$class_method['0'],
		'method'=>$class_method['1']
		));
		$obj = self::getInstance();
		$key = self::generate_page_hash_key($params);
		return $obj->_cache_object->delete($key);
	}
	
	/**
	 * 缓存一个页面数据
	 *
	 */
	public  function cache_method_page(){
		if($this->_is_cached){
			$html=ob_get_contents();
			ob_clean();
			if( $html ) {
				$this->set($this->_cached_page_key,$html,$this->_expire);
				echo $html;
				exit();
			}
		}
	}

	/**
	 * 按当前地址生成KEY
	 *
	 * @return string
	 */
	protected function generate_page_hash_key($query){

		return md5( base64_encode(http_build_query($query)) );
	}

}

?>