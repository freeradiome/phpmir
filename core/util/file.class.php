<?php 
//imagecopyresampled”代替“imagecopyresized相对不失真版本的缩略库//
class File{
	//上传的参数
	public $input_name;  //上传表单的file控件名
	public $save_path;  //上传路径
	public $savefile_name; //文件上传后保存名
	public $file_size=20000; //允许上传的文件默认值

	public $allow_type =array("image/gif","image/pjpeg","image/jpeg","image/bmp","image/png","image/x-png");
	//public $allow_type =array("image/gif","image/pjpeg","image/jpeg");

	//缩略参数

	public $to_w;    //缩略到的正方体宽度

	//----------------缩略图片主函数----------------
	public function ImageResize(){
		$object =  $this->upfile();
		if($object==1){
			$srcfile = $this->save_path.$this->savefile_name; //保存为新文件名

			$info = "";
			$data = GetImageSize($srcfile,$info);
			switch ($data[2]) {

				case 1:
					if(!function_exists("imagecreatefromgif")){

						echo "您的GD2库不能使用GIF格式的图片,请使用JPEG或PNG格式！ ";
						exit();

					}
					$im = ImageCreateFromGIF($srcfile);
					break;

				case 2:

					if(!function_exists("imagecreatefromjpeg")){

						echo "您的GD2库不能使用JPEG格式的图片,请使用其他格式的图片！ ";
						exit();

					}
					$im = ImageCreateFromJpeg($srcfile);
					break;

				case 3:
					$im = ImageCreateFromPNG($srcfile);
					break;

			}


			$srcW=ImageSX($im);
			$srcH=ImageSY($im);
			if($srcH>$srcW){

				$midH = intval(($srcH-$srcW)/2) ;//取整

			}else{

				$midH = intval(($srcW-$srcH)/2) ;//取整

			}

			$ftoW=$this->to_w ; //round($srcW/3);

			if(function_exists("imagecreatetruecolor")){

				$ni = ImageCreateTrueColor($ftoW,$ftoW);

				if($srcH>$srcW){

					if($ni){

						ImageCopyResampled($ni,$im,0,0,0,$midH,$ftoW,$ftoW,$srcW,$srcW);

					}else{

						$ni=ImageCreate($ftoW,$ftoW);
						imagecopyresampled($ni,$im,0,0,0,$midH,$ftoW,$ftoW,$srcW,$srcW);

					}
				}else{
					if($ni){

						ImageCopyResampled($ni,$im,0,0,$midH,0,$ftoW,$ftoW,$srcH,$srcH);

					}else{

						$ni=ImageCreate($ftoW,$ftoW);
						imagecopyresampled($ni,$im,0,0,$midH,0,$ftoW,$ftoW,$srcH,$srcH);

					}

				}
			}else{

				$ni=ImageCreate($ftoW,$ftoW);

				if($srcH>$srcW){
					imagecopyresampled($ni,$im,0,0,0,$midH,$ftoW,$ftoW,$srcW,$srcW);
				}else{
					imagecopyresampled($ni,$im,0,0,$midH,0,$ftoW,$ftoW,$srcH,$srcH);
				}

			}
			if(function_exists('imagejpeg')) {
				ImageJpeg($ni,$srcfile);
			}else{
				ImagePNG($ni,$srcfile);
			}
			ImageDestroy($ni);
			ImageDestroy($im);
		}
		return $object;
	}

	/**
	 * 获取文件后缀
	 *
	 */
	public  function get_file_ext($file){
		$pathinfo = pathinfo($file);
		return $pathinfo['extension'];
	}

	function upfile(){


		if($_FILES[$this->input_name]['size']<$this->file_size){

			if(in_array($_FILES[$this->input_name]['type'],$this->allow_type)){
				if(is_readable($this->save_path)){
					move_uploaded_file($_FILES[$this->input_name]['tmp_name'],$this->save_path.$this->savefile_name.'.'.$this->get_file_ext($_FILES[$this->input_name]['name']));
					$this->savefile_name = $this->savefile_name.'.'.$this->get_file_ext($_FILES[$this->input_name]['name']);
					return 1; //返回参数1代表上传成功
				}else {
					throw_exception('不存在的图片上传路径');
				}

			}else{

				return 2 ;//不准上传的格式

			}

		}else{

			return 3; // "文件超过大小";

		}

	}

}


?>	