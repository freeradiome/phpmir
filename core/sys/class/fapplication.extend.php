<?php
/**
 * 说明：文件数据操作
 * 作者：RobertZeng <zeng444@163.com>
* 日期：2011-7-19
 */
class fApplication {

	//绑定的数据文件夹
	public $_folder_path ;

	//绑定的数据文件名
	protected $_file_name ;

	//带路径的数据文件名
	protected $_file_full_path ;
	
	protected $_file_ext_name ='php';

	function __construct(){
		$this->_folder_path = ROOT_PATH.$this->_folder_path;
		$this->check_folder_path();
	}

	/**
	 * 列出模型文件夹中的数据
	 *
	 * @param bool $is_sort 是否对数据文件排序
	 * @return int 返回存入文件中的字节数
	 */
	function lists($is_sort=false){
		if ($handle = opendir($this->_folder_path)) {
			$result = array();
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					$result[] = $this->_folder_path.$file.$ext_name; ;
				}
			}
			closedir($handle);
			if($is_sort) asort($result);
			return $result;
		}

	}

	/**
	 * 添加一个文档(只有文件名不存在才添加)
	 *
	 * @param array $array 数据数组（一维或二维）
	 * @param string $file 存入的文件名
	 * @return int 返回存入文件中的字节数
	 */
	function add($array,$file=''){
		if(!is_array($array)){
			throw_exception('存取数据必须为数组');
		}
		$file = ($file)?$file:$this->_file_name;
		$this->bind_file_full_path($file);
		if( file_exists( $this->_file_full_path ) ){
			throw_exception('数据文件已经存在');
		}
		$data =  file_put_contents($this->_file_full_path,$this->array_to_string($array));
		if(!$data){
			throw_exception('数据写入失败');
		}
		return $data;
	}


	
	/**
	 * 检查指定数据文件是否存在
	 *
	 * @param string $filename 文件名
	 * @return bool
	 */
	function is_datafile_exist($file){
		$file = ($file)?$file:$this->_file_name;
		$this->bind_file_full_path($file);
		if( file_exists( $this->_file_full_path ) ){
			return true;
		}
	}
	
	/**
	 * 更新一个文件数组
	 *
	 * @param array $array 数据数组（一维或二维）
	 * @param string $file 存入的文件名
	 * @return int 返回存入文件中的字节数
	 */
	function update($array,$file=''){
		if(!is_array($array)){
			throw_exception('存取数据必须为数组');
		}
		$file = ($file)?$file:$this->_file_name;
		$this->bind_file_full_path($file);
		$data =  file_put_contents($this->_file_full_path,$this->array_to_string($array));
		if(!$data){
			throw_exception('数据写入失败');
		}
		return $data;
	}


	/**
	 * 更新一个文件
	 *
	 * @param string $string 字符数据
	 * @param string $file 存入的文件名
	 * @return int 返回存入文件中的字节数
	 */
	function set($string,$file=''){
		if(empty($string )){
			throw_exception('存取数据不能为空');
		}
		$file = ($file)?$file:$this->_file_name;
		$this->bind_file_full_path($file);
		$data =  file_put_contents($this->_file_full_path, $string );
		if(!$data){
			throw_exception('数据写入失败');
		}
		return $data;
	}

	/**
	 * 读取一个文件数据
	 *
	 * @param string $file 文件名
	 * @return array 返回数据
	 */
	function get($file=''){
		$file = ($file)?$file:$this->_file_name;
		$this->bind_file_full_path($file);
		if( !file_exists( $this->_file_full_path ) ){
			return '';
		}
		$data = file_get_contents($this->_file_full_path);
		if( !$data ){
			return '';
		}
		return $data;
	}

	/**
	 * 读取一个文件数据
	 *
	 * @param string $file 文件名
	 * @return array 返回一个数组数据
	 */
	function view($file=''){
		$file = ($file)?$file:$this->_file_name;
		$this->bind_file_full_path($file);
		if( !file_exists( $this->_file_full_path ) ){
			return array();
		}
		$data = file_get_contents($this->_file_full_path);
		if( !$data ){
			return array();
		}
		return 	$this->string_to_array($data);
	}


	/**
	 * 删除一个文件数组
	 *
	 * @param string $file 删除的文件名
	 * @return bool  返回布尔值
	 */
	function delete($file=''){
		$file = ($file)?$file:$this->_file_name;
		$this->bind_file_full_path($file);
		return @unlink($this->_file_full_path);
	}


	/**
	 * 绑定FULL文件路径
	 *
	 * @param string $filename 文件名
	 */
	protected function bind_file_full_path($filename){
		if(!$filename){
			throw_exception('没有定义数据文件名！');
		}
		$ext_name = ($this->_file_ext_name)?'.'.$this->_file_ext_name:'';
		$this->_file_full_path  = $this->_folder_path.$filename.$ext_name;
	}

	/**
	 * 检查数据文件夹是否可用
	 *
	 * @return bool
	 */
	protected function  check_folder_path(){
		if( !$this->_folder_path ){
			throw_exception('没有绑定数据文件夹！');
		}
		if( !is_readable($this->_folder_path) ){
			@mkdir($this->_folder_path, 0777);
		}
		return true;
	}

	/**
	 * 将数组数据转换为字符
	 *
	 * @param array $array 数组数据
	 * @return string  字符串
	 */
	protected function array_to_string($array){
		return serialize($array);
	}

	/**
	 * 将字符数据转换为数组
	 *
	 * @param string $string 字符串
	 * @return array 数组数据
	 */
	protected function string_to_array($string){
		return  unserialize($string);
	}

}
?>