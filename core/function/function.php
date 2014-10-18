<?php 
/**
 * 输出提示信息
 *
 * @param string $message 提示信息内容 
 * @param string $title 输出模版时的提示标题（输出模版时有效）
 * @param unknown_type 输出模版时的模版页（输出模版时有效）
 */
function show_msg($message='',$title='',$html = 'message'){

	$tpl = Tpl::getInstance();
	$tpl->datas(array('title'=>$title,'message'=>$message));
	$tpl->render($html);
	exit();

}

/**
 * 获取客户端IP地址
 *
 * @return string
 */
function get_client_ip(){
	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
	$ip = getenv("HTTP_CLIENT_IP");
	else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
	$ip = getenv("HTTP_X_FORWARDED_FOR");
	else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
	$ip = getenv("REMOTE_ADDR");
	else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
	$ip = $_SERVER['REMOTE_ADDR'];
	else
	$ip = "unknown";
	return($ip);
}

/**
 * 补充函数用于字符地址转路由地址参数
 * 作者 时间
 * robert 2012-07-04
 * @param unknown_type $string
 */
function _run($string,$array=array()){
	$string= explode('/',$string);
//	$url_this = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
//	$url_this =dirname($url_this).'/';
	$url_this =WEB_ROOT;
	$url = run($string[0],$string[1],$array,$url_this);
	echo $url;
}


/**
 * 曾维骐添加给获取字符真实长度
 *
 * @param string $str
 * @return string
 */
function strRealLen($str,$len,$bool=true,$ext='.'){

	if(!empty($str)){
		$i = 0;
		$tlen = 0;
		$tstr = '';
		while ($tlen < $len) {
			$chr = mb_substr($str, $i, 1, 'utf8');
			$chrLen = ord($chr) > 127 ? 2 : 1;
			if ($tlen + $chrLen > $len) break;
			$tstr .= $chr;
			$tlen += $chrLen;
			$i ++;
		}
		if ($tstr != $str && $bool==true) {
			$tstr .= $ext;
		}
		return $tstr;
	}
}


/**
 * 二维转一维
 *
 * @param unknown_type $array
 * @return unknown
 */
function key_array_to_array($array){
	return  $array['mobile'];
}

/**
 * 计算中文字符串长度
 *
 * @param unknown_type $string
 * @return unknown
 */
function strlen_utf8($string = null) {
	// 将字符串分解为单元
	preg_match_all("/./us", $string, $match);
	// 返回单元个数
	return count($match[0]);
}
?>