<?php 
class Curl{
	
	//抓取文件超时时间
	public $timeout = 20;
	
	private $_ch;

	public function __construct(){
		$this->_ch = curl_init();
	}

	public function __destruct(){
		curl_close($this->_ch);
	}
	
	/**
	 * 抓取远程文件
	 *
	 * @param string $url
	 * @return html
	 */
	public function get_file_contents($url){
		//链接的URL
		curl_setopt($this->_ch, CURLOPT_URL,$url);
		//伪造HEADER
		curl_setopt($this->_ch,CURLOPT_HTTPHEADER,array('Accept-Language: zh-cn','Connection: Keep-Alive', 'Cache-Control: no-cache' ));//
		curl_setopt($this->_ch,CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)");
		//超时
		curl_setopt($this->_ch, CURLOPT_TIMEOUT, $this->timeout ); //抓取20秒
		//输出header
		curl_setopt($this->_ch, CURLOPT_HEADER,false);
		//是否抓取跳转后的页面
		//curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION,true);
		//输出内容
		curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
		$html = curl_exec($this->_ch);
		// 检查是否有错误发生
		if(curl_errno($this->_ch)){
			return false;
		}
		return  $html;
	}
}
?>