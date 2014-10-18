<?php
/**
 * 二维码生成器
 * author:zengweiqi
 * version:1.0
 * date:2012-12-17
 * 
 * description:
 * $qr = new QR(array{'width'=>100,'height'=>100,'is_cached'=>ture});
 * $qr->generate('http://www.163.com')
 * 
 */
class QR{

	//二维码生成接口
	private $_api_url = "https://chart.googleapis.com/chart";

	//二维码生成类型
	private $_type = 'qr';
	
	//二维码宽度
	public $_width = 81;

	//二维码高度
	public $_height = 80;

	//二维码边距
	public $_margin = 0;

	//二维码的等级
	public $_level =  'L';//四个等级，L-默认：可以识别已损失的7%的数据；M-可以识别已损失15%的数据；Q-可以识别已损失25%的数据；H-可以识别已损失30%的数据

	//是否开启缓存
	public $_is_cached = true;

	//缓存超时时间
	public $_time_out = 30;

	//缓存路径设置
	public $_cache_path = './';

	//是否生成hash目录
	public $_is_hash_folder = true;

	//生成hash目录的复杂度
	public $_folder_hash_degree = 1; //1到2位

	/**
	 * 初始化
	 *
	 * @param array $params
	 */
	public function __construct($params=array()){

		if(isset($params['width'])){
			$this->_width = $params['width'];
		}
		if(isset($params['height'])){
			$this->_height = $params['height'];
		}
		if(isset($params['margin'])){
			$this->_margin = $params['margin'];
		}
		if(isset($params['is_cached'])){
			$this->_is_cached = $params['is_cached'];
		}
		if(isset($params['time_out'])){
			$this->_time_out = $params['time_out'];
		}
		if(isset($params['is_hash_folder'])){
			$this->_is_hash_folder = $params['is_hash_folder'];
		}
		if(isset($params['level'])){
			$this->_level = $params['level'];
		}
		if(isset($params['cache_path'])){
			$this->_cache_path = $params['_cache_path'];
		}
		if(isset($params['folder_hash_degree'])){
			$this->_folder_hash_degree = $params['folder_hash_degree'];
		}

	}

	/**
	 * 生成验证码
	 *
	 * @param str $text
	 * @return array
	 */
	public function generate($text){
		if(!$text){
			return false;
		}
		$url = $this->get_url(array(
		'cht'=>$this->_type,
		'chs'=>$this->_width.'x'.$this->_height,
		'chl'=> $text
		));
		$url .='&chld='.$this->_level.'|'.$this->_margin;
		if($this->_is_cached){
			$url =  $this->cache($url);
		}else{
			$url = array('url'=>$url);
		}
		return $url;
	}


	/**
	 * 下载指定二维码
	 *
	 * @param unknown_type $url
	 */
	private function cache($url){
		$save_info = $this->generator_cache_path($url);

		$html = $this->file_get_contents_with_timeout($url,$this->_time_out);

		if($html){
			if(   !file_exists($save_info['path'])  ){
				if(  !@mkdir($save_info['path'], 0777)  ){
					return array('error'=>-1);
				}
			}
			$file_pahh = $save_info['path'].$save_info['file'];
			if( file_exists($file_pahh)){
				return $save_info;
			}
			if(  !@file_put_contents($file_pahh,$html) ){
				return array('error'=>-2);
			}
			return $save_info;
		}
	}


	/**
	 * 生成缓存文件保存路径和名称
	 *
	 * @param string $file
	 * @return string
	 */
	private function generator_cache_path($file){
		$file=md5($file);
		$path = $this->_cache_path;
		if(  $this->_is_hash_folder ){
			$folder=substr($file,0,$this->_folder_hash_degree).'/';
			$path = $path.$folder;
		}
		return array(
		'path'=>$path,
		'file'=>$file.'.jpg'
		);

	}

	/**
	 * 抓取文件
	 *
	 * @param str $url
	 * @param int $timeout
	 * @return int
	 */
	private function file_get_contents_with_timeout($url,$timeout){
		$context = stream_context_create( array(  'http'=>array( 'method'=>"GET", 'timeout'=>$timeout )) );
		$html =file_get_contents($url, false, $context);
		return $html;

	}
	/**
	 * 生成接口的请求地址
	 *
	 * @param array $query
	 * @return str
	 */
	private function get_url($query){
		return $this->_api_url.'?'.http_build_query($query);
	}

	
}
?>