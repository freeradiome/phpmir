<?php
/**
 * ��ά��������
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

	//��ά�����ɽӿ�
	private $_api_url = "https://chart.googleapis.com/chart";

	//��ά����������
	private $_type = 'qr';
	
	//��ά����
	public $_width = 81;

	//��ά��߶�
	public $_height = 80;

	//��ά��߾�
	public $_margin = 0;

	//��ά��ĵȼ�
	public $_level =  'L';//�ĸ��ȼ���L-Ĭ�ϣ�����ʶ������ʧ��7%�����ݣ�M-����ʶ������ʧ15%�����ݣ�Q-����ʶ������ʧ25%�����ݣ�H-����ʶ������ʧ30%������

	//�Ƿ�������
	public $_is_cached = true;

	//���泬ʱʱ��
	public $_time_out = 30;

	//����·������
	public $_cache_path = './';

	//�Ƿ�����hashĿ¼
	public $_is_hash_folder = true;

	//����hashĿ¼�ĸ��Ӷ�
	public $_folder_hash_degree = 1; //1��2λ

	/**
	 * ��ʼ��
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
	 * ������֤��
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
	 * ����ָ����ά��
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
	 * ���ɻ����ļ�����·��������
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
	 * ץȡ�ļ�
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
	 * ���ɽӿڵ������ַ
	 *
	 * @param array $query
	 * @return str
	 */
	private function get_url($query){
		return $this->_api_url.'?'.http_build_query($query);
	}

	
}
?>