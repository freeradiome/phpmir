<?php 
/**
 * 后台管理员模型
 * robert zeng 2012-08-09
 *
 */
class admins extends Application{

	protected  $_tablename = 'admins'; //绑定数据库

	protected $_password_ticket = '&%h%R$';

	//对入库数据设定自动验证规则
	public $_validate = array(
	'name'=>array('unique'=>true,'min'=>4,'max'=>15,'msg'=>'管理员账户为4-15位'),
	'password'=>array('unique'=>true,'min'=>2,'max'=>15,'msg'=>'2-15位密码'),
	);

	 


}