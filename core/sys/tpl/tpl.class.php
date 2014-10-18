<?php 
/**
 * 说明：引擎工厂类
 * 作者：RobertZeng <zeng444@163.com>
 * 日期：2011-08-27
 */
class Tpl{

	/**
	 * 取得实例
	 *
	 * @return object
	 */
	public static function getInstance(){
		$args = func_get_args();
		return get_instance_of(__class__,'factory',$args);
	}

	/**
	 * factory
	 *
	 * @param array $args
	 * @return object
	 */
	public function &factory($args=''){
		if(empty( $args)){
			$args = array( 'tpl_ext'=>tpl_ext, 'tpl_path'=>VIEWER_PATH);
		}
		$tpl = new Template($args);
		return $tpl;
	}


	/**
	 * HTML引入文件生成
	 *
	 * @param unknown_type $filename 文件名
	 * @param unknown_type $type 文件类型
	 * @param unknown_type $ver
	 */
	static public function import($filename,$type='css',$ver=0){
		$ver = ($ver==0)?'':"?$ver";
		if($type=='js'){
			echo '<script type="text/javascript" src="'.PUBLIC_ROOT.$type.'/'.$filename.'.'.$type.$ver.'"></script>'."\n";
		}elseif ($type=='css'){
			echo '<link rel="stylesheet" type="text/css" href="'.PUBLIC_ROOT.$type.'/'.$filename.'.'.$type.$ver.'">'."\n";
		}
	}
	/**
	 * 模版函数用于载入一个片段页面
	 *
	 * @param unknown_type $filename 文件路径
	 * @param unknown_type $data 数组数据
	 */
	static public function  render($filename,$data=array()){
		
		Template::import($filename,$data);
	}


	/**
	 * 载入一个挂件
	 *
	 * @param unknown_type $filename 挂件名
	 * @param unknown_type $data 挂件中的数据
	 * @param unknown_type $path 挂件存放路径
	 */
	static public  function  widget($filename,$data=array(),$path='widget/'){
		Template::import($path.$filename,$data);
	}

	/**
	 * 获取当前访问的路由类和方法
	 *
	 * @return array
	 */
	static function get_route(){
		//$hash_map = require(ROOT_PATH.'lang/'.WEB_LANG.'/app.config.php');

		if(isset($_GET['class'])){
			$class = strtolower(trim($_GET['class']));
		}

		//$app_lists = $hash_map[$class]['app'];

		if(isset($_GET['method'])){

			$method = strtolower(trim($_GET['method']));
			//$method = ($method && $method!='index')?$method: key($app_lists);
		}

		return array('method'=>$method,'class'=>$class);
	}


	


}

?>