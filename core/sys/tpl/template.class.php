<?php 
/**
 * 说明：模板引擎
 * 作者：RobertZeng <zeng444@163.com>
 * 日期：2011-08-27
 * 版本：1.0
 */
class Template{

	//模板所在文件夹
	public $_tpl_path;

	//模板文件后缀名
	public $_tpl_ext = 'html';

	//渲染文件
	public $_include_file = '';


	//等待渲染的数据
	private $_tpl_date = array();

	//是否编译模板
	public $_tpl_compile = false;
	
	//错误信息
	private $_error_message  = array('tpl_not_exists'=>'模板文件不存在');


	public function __construct( $config=''){

		if( !empty($config) && is_array($config) ){
			if(isset($config['tpl_path'])){
				$this->_tpl_path = $config['tpl_path'];
			}
			if(isset($config['tpl_ext'])) {
				$this->_tpl_ext =  $config['tpl_ext'];
			}
			if(isset($config['tpl_compile'])) {
				$this->_tpl_compile =  $config['tpl_compile'];
			}
		}
	}

	/**
	 * 设置模板文件夹路径
	 *
	 * @param string $path
	 */
	public function tpl_path($path){
		if( !$this->tpl_file_exists($path) ){
			throw_exception( $this->_error_message['tpl_not_exists'],get_class($this));
		}
		$this->_tpl_path = $path;
	}

	/**
	 * 分配一个数据
	 *
	 * @param string $symbol
	 * @param string $content
	 * @return bool
	 */
	public function data( $symbol,$content ){
		if(!$this->is_symbol_type($symbol) ){
			throw_exception('类型不正确');
		}
		$this->_tpl_date[$symbol]= $content;
		return true;
	}

	/**
	 * 分配一组数据
	 *
	 * @param unknown_type $array
	 */
	public function datas($array){
		if(is_array($array)) {
			if(!empty($this->_tpl_date)){
				$this->_tpl_date = array_merge( $this->_tpl_date,$array );
			}else {
				$this->_tpl_date = $array;
			}
			return true;
		}
	}
	/**
	 * 获取编译后的文件信息
	 *
	 * @param string $filename
	 * @return string
	 */
	public function fetch($filename){
		
			if(  empty($filename) ){
			return false;
		}
		$this->_include_file = $this->compose_real_path($filename);
		if(!is_array($this->_tpl_date)){
			throw_exception( $this->_error_message['tpl_not_exists'],get_class($this) );
		}
		extract($this->_tpl_date);
		if( !is_readable( $this->_include_file ) ){
			throw_exception( $this->_error_message['tpl_not_exists'] );
		}
		if(!$this->_tpl_compile){
			include_once($this->_include_file);
		}else{
			//编译实例
				include_once($this->_include_file);
		}
		$html = ob_get_contents();
		ob_clean();
		return $html;
	}

	/**
 	 * 渲染模板
 	 *
 	 * @param string $tpl
 	 * @return bool
 	 */

	public function render($filename){
		if(  empty($filename) ){
			return false;
		}
		$this->_include_file = $this->compose_real_path($filename);
		
		if(!is_array($this->_tpl_date)){
			throw_exception( $this->_error_message['tpl_not_exists'],get_class($this) );
		}
		
		extract($this->_tpl_date);
	
		if( !is_readable( $this->_include_file ) ){
			throw_exception( $this->_error_message['tpl_not_exists'] );
		}
		
		if(!$this->_tpl_compile){
			include_once($this->_include_file);
		}else{
			//编译实例
			include($this->_include_file);
		}
		return true;

	}
	
	/**
	 * 包含一个模版片段
	 *
	 * @param string $filename 模版名
	 */
	public static function import($filename,$data=array()){
		
		if(!empty($filename)){
			$inclide_path =VIEWER_PATH. $filename.'.'.tpl_ext;
			if(!empty($data)) extract($data);
			if(file_exists($inclide_path)) include_once( $inclide_path) ;
		}
	}

	/**
	 * 检查模板文件是否可读
	 *
	 * @param string $path
	 * @return bool
	 */
	public function tpl_file_exists($filename){
		
		if( empty($filename) ){
			return false;
		}
		
		$filename = $this->compose_real_path($filename);
		if(is_readable($filename)){
			return true;
		}
	}

	/**
	 * 需要渲染的变量数
	 *
	 */
	public function data_count(){
		if( is_array($this->_tpl_date)){
			return sizeof($this->_tpl_date);
		}
	}

	/**
	 * 获取模板文件大小
	 *
	 * @param string $filename
	 * @return int
	 */
	public function tpl_length($filename){
		if( !empty($filename) ){
			return false;
		}
		$filename = $this->compose_real_path($filename);
		if( !is_readable($filename) ){
			throw_exception( $this->_error_message['tpl_not_exists'] );
		}
		if( $filesize = @filesize($filename) ){
			return sprintf("%u",$filesize );
		}
	}

	/**
	 * 返回模板文件信息
	 *
	 * @param string $filename
	 * @return array
	 */
	public function tpl_info($filename){
		if( !empty($filename) ){
			return false;
		}
		$file_path = $this->compose_real_path($filename);
		clearstatcache();
		if( !is_readable($file_path) ){
			throw_exception( $this->_error_message['tpl_not_exists'] );
		}
		return array(
		'file_name'=>$filename.'.'.$this->_ext,
		'file_path'=>$file_path,
		'file_size'=> $this->tpl_length($filename),
		'file_at_time'=>date('Y-m-d H:i:s', fileatime($file_path)),
		'file_modity_time'=> date('Y-m-d H:i:s',filemtime($file_path)),
		'file_create_time'=>date('Y-m-d H:i:s',filectime($file_path))
		);
	}

	/**
	 * 检查变量符号
	 *
	 * @param string $str
	 * @return bool
	 */
	private function is_symbol_type($str){
		
		if(!empty($str) && is_string($str) && !intval($str)){
			return true;
		}
	}

	/**
	 * 组合文件路径
	 *
	 * @param string $filename
	 * @return string
	 */
	function compose_real_path( $filename ){
		return $this->_tpl_path.$filename.'.'.$this->_tpl_ext;
	}

}
?>