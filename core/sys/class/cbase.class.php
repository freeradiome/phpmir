<?php
/**
 * 控制层过滤器
 *
 */
class cBase{

	//	private  $tpl;
	protected $tpl;

	//预处理例外的class
	protected $_filter_except_class = array();

	//预处理例外的methid
	protected $_filter_except_method = array();

	//需静态化处理的页面方法
	public $_page_cached_method= array();

	//当前路由的实例
	protected $_absolute_class;

	//当前路由的方法
	protected $_absolute_method;

	//实例化一个页面缓存类
	protected $_page_cache;
	

	//页面缓存周期
	protected $_cached_page_time=0;

	public function __construct(){
		
		$this->_absolute_class = strtolower(get_class($this));
		$this->_absolute_method  = trim($_GET['method']);
		//自动页面缓存
		if(  !empty($this->_page_cached_method) &&  in_array($this->_absolute_method,$this->_page_cached_method) ){
			$this->_page_cache = pageCache::getInstance(array(
			'_expire'=> $this->_cached_page_time,
			));
			$this->_page_cache->get_cache_method_page();
			
		}
		if(is_null($this->tpl)){
			$this->tpl =  Tpl::getInstance();
			if (method_exists($this,'filter') ){
				if(!in_array($this->_absolute_class,$this->_filter_except_class) && !in_array($this->_absolute_method,$this->_filter_except_method)){
					$this->filter();
				}
			}
		}
	}

	public  function __destruct(){
		if( !empty($this->_page_cached_method) && in_array($this->_absolute_method,$this->_page_cached_method)  ){
			$this->_page_cache->cache_method_page();
		}
		if( bench ) $this->bench();
	}

	
	
	/**
	 * 返回编译后的模版内容
	 *
	 * @param string $filename 模版文件名
	 * @return bool
	 */
	protected function fetch($filename){

		return $this->tpl->fetch($filename);
	}

	/**
	 * 渲染模版
	 *
	 * @param string $filename 模版文件名
	 */
	protected  function render($filename){

		if($this->tpl->tpl_file_exists($filename)){
			$this->tpl->render($filename);
		}
	}
	/**
	 * 将一组数据提供给模版
	 *
	 * @param array $array
	 */
	protected function datas($array){
		$this->tpl->datas($array);
	}

	/**
	 * 将组数据提供给模版
	 *
	 * @param string $symbol 定义模版变量名
	 * @param string $content 赋值
	 */
	protected function data( $symbol,$content ){
		$this->tpl->data( $symbol,$content );
	}

	/**
	 * 页面测试bench
	 *
	 */
	private function bench(){
		print(' <br />执行时间: '.round($GLOBALS['rt']->get_execute_time(),3).' sec<br />')  ;
		printf(' 内存占用: %01.2f mb', memory_get_usage()/1024/1024);
		print('<br /> 当前控制器: '. $this->_absolute_class .'<br />');
		print(' 当前方法: '. $this->_absolute_method).'<br />';
		 
	}
	
}

?>