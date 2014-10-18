<?php 
/**
 * 说明：后台控制器基类
 * 作者：RobertZeng <zeng444@163.com>
 * 日期：2011-10-12
 */
class aController extends cBase{


	public function filter(){
		$admin = new admins();
		if(!$admin->is_login()){
			redirect('admin/login');
		}
	}

}
?>