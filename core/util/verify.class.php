<?php 
/*
*软件著作权：中华网科技公司
*系统名称：中华网sns社区
*相关文档：一期社区产品PRD-1.1.docx
*作者：曾维骐
*程序功能：生成验证码
*$RCSfile: verify.class.php,v $
*$Revision: 1.7 $
*$Date: 2010/11/04 05:36:20 $
*修改历史：
*修改日期          修改者      BUG小功能修改申请单号
*YYYY/MM/DD     ###         *******
*/
class Verify{

	public  $_string_len= 4; //生成字符个数

	//public  $_string ="23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPRSTUVWXYZ23456789"; //随机数定义
	public  $_string ="23456789ABCDEFGHJKLMNPRSTUVWXYZ"; //随机数定义

	public $_session_key_name = 'ggda_v2'; //生成会话名
	
	public $_image_width = 58;  //图片宽

	public $_image_height = 22; //图片高
	
	public $_image_background = array('r'=>225,'g'=>195,'b'=>215); //图片背景RGB色
	
	public $_image_border = array('r'=>153,'g'=>153,'b'=>153); //边框RGB色
	
	private $_verify_txt,$_aimg; 
	
	/*
	* 初始化图片对象
	* <br>修改历史：
	* <br>修改日期    修改者     工单号＋工单名称
	* <br>
	* @return
	*注意：
	*/
	function __construct(){
		$this->_aimg = imagecreate($this->_image_width,$this->_image_height);
	}
	
	
	/*
	* 生成图片背景
	* <br>修改历史：
	* <br>修改日期    修改者     工单号＋工单名称
	* <br>
	* @return
	*注意：
	*/
	private function createImgBackgroundObj (){

		return imagecolorallocate($this->_aimg,$this->_image_background['r'],$this->_image_background['g'],$this->_image_background['b']);
	}
	
	/*
	* 得到验证码文本
	* <br>修改历史：
	* <br>修改日期    修改者     工单号＋工单名称
	* <br>
	* @return
	*注意：
	*/
	public function getVerifyTxt(){
		return $this->_verify_txt;	
	}
	
	/*
	* 生成图片边框
	* <br>修改历史：
	* <br>修改日期    修改者     工单号＋工单名称
	* <br>
	* @return
	*注意：
	*/
	private function createImgBorderObj(){
		return imagecolorallocate($this->_aimg,$this->_image_border['r'],$this->_image_border['g'],$this->_image_borde['b']);
	}
	
	/*
	* 生成随机验证码
	* <br>修改历史：
	* <br>修改日期    修改者     工单号＋工单名称
	* <br>
	* @return
	*注意：
	*/
	private function randomkeys(){
		$pattern= $this->_string;
		$len = strlen($pattern)-1;
		$key='';
		for($i=0; $i < $this->_string_len; $i++)
		{
			$key .= $pattern{rand(0,$len)};
		}
		$this->_verify_txt = $key;
		return $key;
	}
	
	/*
	* 创建SESSION会话
	* <br>修改历史：
	* <br>修改日期    修改者     工单号＋工单名称
	* <br>
	* @return
	*注意：
	*/
	private function seesion(){
		session_start();
		$_SESSION[$this->_session_key_name]=$this->_verify_txt;
	
	}
	

	
	/*
	* 显示图片
	* <br>修改历史：
	* <br>修改日期    修改者     工单号＋工单名称
	* <br>
	* @return
	*注意：
	*/
	function display(){
		$authnum=$this->randomkeys();
		$x_size=$this->_image_width;
		$y_size=$this->_image_height;
		if(function_exists('imagecreate') && function_exists('imagecolorallocate') && function_exists('imagepng') && function_exists('imagesetpixel') && function_exists('imageString') && function_exists('imagedestroy') && function_exists('imagefilledrectangle') && function_exists('imagerectangle')){

			$back = $this->createImgBackgroundObj();
			$border = $this->createImgBorderObj();
			imagefilledrectangle($this->_aimg,0,0,$x_size - 1,$y_size - 1,$back);
			imagerectangle($this->_aimg,0,0,$x_size - 1,$y_size - 1,$border);
			for($i=1; $i<=20;$i++){
				$dot = imagecolorallocate($this->_aimg,mt_rand(50,255),mt_rand(50,255),mt_rand(50,255));
				imagesetpixel($this->_aimg,mt_rand(2,$x_size-2), mt_rand(2,$y_size-2),$dot);
			}
			for($i=1; $i<=10;$i++){
				imageString($this->_aimg,1,$i*$x_size/12+mt_rand(1,3),mt_rand(1,13),'*',imageColorAllocate($this->_aimg,mt_rand(150,255),mt_rand(150,255),mt_rand(150,255)));
			}
			for ($i=0;$i<strlen($authnum);$i++){
				imageString($this->_aimg,mt_rand(3,5),$i*$x_size/4+mt_rand(1,5),mt_rand(1,6),$authnum[$i],imageColorAllocate($this->_aimg,mt_rand(50,255),mt_rand(0,120),mt_rand(50,255)));
			}
			header("Pragma:no-cache");
			header("Cache-control:no-cache");
			header("Content-type: image/png");
			$this->seesion();
			imagepng($this->_aimg);
			exit;
		} 
	}
	/*
	* 撤销对象
	* <br>修改历史：
	* <br>修改日期    修改者     工单号＋工单名称
	* <br>
	* @return
	*注意：
	*/
	function __destruct(){
		 imagedestroy($this->_aimg);
	}
}



?>