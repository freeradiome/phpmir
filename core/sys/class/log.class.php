<?php 

class Log{
		
	public  static function  write($message){
		$path = self::compose_file_name();
		error_log(date('m-d H:i:s')."\n".$message."\n", 3, $path);
	}

	
	public static function  compose_file_name(){
		if(!is_readable(ROOT_PATH .'log/')){
			mkdir(ROOT_PATH .'log/');
		}
		$path = ROOT_PATH .'log/'. date('Y-m-d',time()).'.txt' ;
		return $path;
	}
	
}

?>