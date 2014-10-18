<?php 
/**
 * 说明：路由器
 * 作者：RobertZeng <zeng444@163.com>
 * 日期：2011-09-01
 * 版本：1.0
 */
class Route{

	//对象名
	public $_class_name='index';

	//方法名
	public $_method_name='index';

	//对象是否首字母大写
	public $_class_upper = true;

	//方法是否首字母大写
	public $_method_upper = false;

	//当前实例
	private $_obj = null;

	//是否开启pathinfo
	public $_pathinfo = true;

	public $_pathinfo_string ;

	public $_rules;

	public $_class_query ='class';

	public $_method_query ='method';


	public function __construct($config){

		if(isset($config['pathinfo'])){
			$this->_pathinfo = $config['pathinfo'];
		}
		
		if(isset($config['class'])){
			$this->_class_name = $config['class'];
			
			if($this->_pathinfo) $_GET[$this->_class_query] = $config['class'];
		
		}
		if(isset($config['method'])){
			$this->_method_name = $config['method'];
			if($this->_pathinfo) $_GET[$this->_method_query] = $config['method'];
		}
		if(isset($config['class_name'])){
			$this->_class_query = $config['class'];
		}
		if(isset($config['method_name'])){
			$this->_method_query = $config['method_name'];
		}

		
		if(empty($this->_class_query) || empty($this->_method_query)){
			throw_exception('error');
		}
		if(isset($config['rules'])){
			$this->_rules = $config['rules'];
		}
		
		if($this->_pathinfo){
			
			$this->get_object_params_by_pathinfo();
		}else {
			$this->get_object_params_by_query();
		}
		if(empty($this->_class_name) || empty($this->_method_name)){
			throw_exception('error');
		}
	}




	/**
	 * 控制器模型加载器
	 *
	 * @return unknown
	 */
	public function load_controller(){
		$path = CONTROLLER_PATH.$this->_class_name.'.php';
		if( file_exists($path) ){
			return require($path) ;
		}
	}

	/**
	 * 初始化对象
	 *
	 * @return unknown
	 */
	public function go(){

		if ( !$this->load_controller() ){
			throw_exception('加载模型文件失败，路径错误');
		}
		$class = ($this->_class_upper)?ucfirst($this->_class_name):$this->_class_name;
		if(!class_exists($class,false)){
			throw_exception('不存在此实例');
		}
		
		if( $this->_obj==null){
			$this->_obj = new $class();
			$method = ($this->_method_upper)?ucfirst($this->_method_name):$this->_method_name;
			if ( method_exists( $this->_obj ,$method ) ){
				$this->_obj->$method();
				return false;
			}else{
				throw_exception('实例中不存在此方法');
			}
		}

	}
	/**
	 * 用pathinfo获取对象及其方法
	 *
	 */
	private function get_object_params_by_pathinfo(){
		if(!isset($_SERVER[ 'PATH_INFO'])){
			return;
		}
		
		$pathinfo = $this->_pathinfo_string = $_SERVER[ 'PATH_INFO'];
		$pathinfo = $this->rules($pathinfo);
		$query = explode('/',$pathinfo);
		array_shift($query);
		if(isset($query[0]) && !empty($query[0])){
			$class =  $_GET[$this->_class_query] = $query[0];
		}else{
			$class =  $_GET[$this->_class_query] = $this->_class_name;
		}
		if ( empty($query[1]) ){
			$method = $_GET[$this->_method_query] = $this->_method_name;
		}else{
			$method = $_GET[$this->_method_query] = $query[1];
		}
		if(!empty($method)){
			$this->_method_name	= strtolower(($method));
		}
		if(!empty($class)){
			$this->_class_name = strtolower($class);
		}
	}




	/**
	 * 用普通方式获取对象及其方法
	 *
	 */
	private function get_object_params_by_query(){

		
		
		$class = trim($_GET[$this->_class_query]);
		if(!$class){
			$class = $this->_class_name;
			$_GET[$this->_class_query] = $class;
		}
	
		$method = trim($_GET[$this->_method_query]);
		if(!$method){
			$method = $this->_method_name;
			$_GET[$this->_method_query] = $method;
		}
		
		
	

		if(empty($class)){
			throw_exception('不能为空');
		}
		if(empty($method)){
			throw_exception('不能为空');
		}
		
		$this->_method_name	= strtolower($method);
		$this->_class_name = strtolower($class);

	}

		/**
	 * 配置别名路径
	 */
	public function rules($pathinfo){

		if(!empty($this->_rules)){
			$query = array_keys($this->_rules);
			$replace = array_values($this->_rules);
			$pathinfo = $_SERVER[ 'PATH_INFO'] = preg_replace($query,$replace,$pathinfo);
		}
		return $pathinfo;
	}
	
	public function debug(){
		$route_type = ($this->_pathinfo) ? 'pathinfo':'get';
		echo "路由模式：$route_type<br />";
		if($this->_pathinfo){
			echo "pathinfo：$this->_pathinfo_string<br />";
		}
		echo "对象：{$this->_class_name}<br />";
		echo "方法：{$this->_method_name}<br />";
		echo "GET参数：";
		$_get_info = $_GET;
		echo $this->_class_query;
		unset($_get_info[$this->_class_query]);
		unset($_get_info[$this->_class_query]);
		var_dump($_get_info);
	}

}

?>