<?php
/**
 * file
 * 修改历史
 * 日期 作者 修改内容
 * 2010 robert
 * 
 */
class Fcache  {

	private $_default_expire=3600;
	
	protected $_cache_dir = 'cache/pagecahce/';

	function __construct(){

		if( !file_exists(ROOT_PATH.$this->_cache_dir) ){
			@mkdir(ROOT_PATH.$this->_cache_dir,0777);
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

		$className = __CLASS__;
		$o = new $className();
		return $o;
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
		$path = $this->get_full_path($key);
		return $this->file_put_contents($path,$value,$expire);
	}

	/**
	 * 获取存储的数据
	 *
	 * @param string		$key
	 * @return mixed
	 */
	public function get($key){

		$path = $this->get_full_path($key);
		return  $this->file_get_contents($path);
	}

	public function delete($key){

		$path = $this->get_full_path($key);

		if(file_exists($path)){

			return @unlink($path);
		}
	}

	private function get_full_path($key){
		return ROOT_PATH.$this->_cache_dir.md5($key);
	}
	
	/**
	 * 写入到文件
	 *
	 */
	private function file_put_contents($path,$value,$expire){
		$data = array(
			'expire'=>($expire)?$expire:$this->_default_expire,
			'time'=>time(),
			'content'=>$value
		);
		return file_put_contents($path,serialize($data));
	}
	
	/**
	 * 读出到文件
	 *
	 */
	private function file_get_contents($path){
		$data='';
		if( file_exists($path)){
			$data = @file_get_contents($path);
		}
		if( $data ) {
			$data = unserialize($data);
//			echo  time() - $data['time'].'<br />'.$data['expire'].'<br />';
			if(time() - $data['time'] > $data['expire']){
				if(file_exists($path)) @unlink($path);
				return false;
			}
			return $data['content'];
		}
		
	}

}