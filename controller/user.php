<?php 
class User extends Controller {

	public $_cached_page_time = 4; //秒
	public $_page_cached_method =  array('test');
	
	
	/**
	 * 测试页
	 *
	 */
	public function test(){
		echo uniqid(true);
	}
	
	/**
	 * 用户登录
	 *
	 */
	public function login(){
		$user = new users();
		
		if($_POST){
			$data = _g('data');
			
			$referer = _g('referer');
			
			$referer = ($referer)?$referer:'/';
			$result =  $user->login($data['mobile'],$data['password']);
			
			if( !$result ){
				show_msg('用户名或者密码错误，登录失败');
			}
			
			redirect($referer);
		}
		$this->render('dl');
	}

	/**
	 * 用户退出
	 *
	 */
	public function quit(){
		$user = new users();
		if( $user->quit() ){
			redirect('/');
		}
	}

	/**
	 * 用户注册
	 */
	public function reg(){
		$users = new users();
		if($_POST){
			$referer = _g('referer');
			$data = _g('data');
			$referer = ($referer)?$referer:'/';
			$result = $users->register($data['mobile'],$data['email'],$data['password']);
			if($result==-1){
				show_msg('用户名已经注册');
			}elseif($result==-2){
				show_msg('邮件已经注册');
			}elseif($result){
				redirect($referer);
			}
		}
		$this->render('zchy');
	}

	/**
	 * 个人中心
	 *
	 */
	public function selfs(){
		$_limit =3;
		$user = new users();
		$notice = new Notices();
		$condition = array();
		$info =  $user->getLoginUserInfo();
		
		if( !$info ){
			show_msg('对不起你没有登录');
		}
		$user_info = $user->item(array('mobile','points'))->view(array('mobile'=>$info['mobile']));
		//分页xueli 20120921
		$condition = array('mobile'=>$info['mobile'],'is_read'=>'1');
		$nocount = $notice->getNoticesCount($condition);
		
		$page = new Page($nocount,$_limit);
	
		$notice_list = $notice->lists($condition,array('created_at'=>'desc'),$page->start,$page->limit);
		
		$pagestring = $page->mainPage();

		//读取关联栏目
		$categorys= new Categorys();
		$info = $categorys->view( array('id'=>$this->_category_id) );
			
		$articles = new articles();
		$relation_cat = $articles->relation_cat_lists($info['category_lists']);
		if( $relation_cat ) {
			$this->data('relation_cat',$relation_cat);
		}

		$this->datas(
				array('info'=>$user_info,
						'notices'=>$notice_list,	
						'pagestring' =>$pagestring
						));//xueli 20120921
		$this->render('grzx');
	}
	/**
	 * xueli 20120921
	 * 设置为已读
	 */
	function operation(){
		$user = new users();
		$notice = new Notices();
		$id=_g('id');
		$referer = _g('referer');
		$referer = ($referer)?$referer:'/';
		$info =  $user->getLoginUserInfo();
		if($notice->upnotice(array('mobile'=>$info['mobile'],'id'=>$id))){
			redirect($referer);
		}
		show_msg('设置失败');
	}
	/**
	 * 点击更多显示当前用户的所有的通知信息
	 * xueli 20120921
	 */
	function notice_more(){
			$_limit = 3;
		$user = new users();
		$notice = new Notices();
		$info =  $user->getLoginUserInfo();
		$condition = array('mobile'=>$info['mobile']);
		$nocount = $notice->getNoticesCount($condition);	
		$page = new Page($nocount,$_limit);
		$notice_list = $notice->lists($condition,array('created_at'=>'desc'),$page->start,$page->limit);
		$pagestring = $page->mainPage();
		$this->datas(array('notices'=>$notice_list,'pagestring' =>$pagestring));
		$this->render('notices');
	}
	
	/*	
	*找回密码
	*/
	function back(){		
		$this->render('mm');
	}
	/*
	*发送邮件
	*/
	function email(){
		$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
		$email = _g('email');
		if(preg_match($chars, $email)){
			$user = new users();
			if($user->is_mail_register($email)){
				$userinfo = $user->items('id')->view(array('email'=>$email));
				$pwreset = new pwreset();				
				if($pwreset->add(array('id'=>$userinfo['id'],'fuse'=>1))){					
					$url = WEB_ROOT.'user/pwreset?key='.$pwreset->_encode_key;
					if($this->sedmail($email,$url)){
						$this->render('mmt');
					}
				}
			}else{
				show_msg('对不起，这个邮箱没有被注册！');				
			}			
		}else {
			redirect('user/back');
		}		
	}
	/*
	*重置密码
	*/
	function pwreset(){
			$key = _g('key');	
			if($key){
				$pwreset = new pwreset();
				$userinfo = $pwreset->items('id,created_at')->view(array('fuse'=>$key),false);
				if($userinfo){
					$oldtime = strtotime($userinfo['created_at']);
					$nowtime = time();
					$difftime = intval(($nowtime-$oldtime)/3600);
					if($difftime<24){
						$this->datas(array(
							'uid' => $userinfo['id']
						));
						$this->render('pwreset');					
					}else {
						show_msg('你的申请已经超时，请重新找回密码！');					
					}
				}
			}else{				
				show_msg('发生错误');
			}
	}
	/*
	*提交密码
	*/
	function setpwd(){
		if($_POST){
			$uid = _g('uid');
			$oldpwd = _g('oldpwd');
			$newpwd = _g('newpwd');
			if(empty($oldpwd) and empty($newpwd)){
				show_msg('密码不能为空');			
			}elseif ($newpwd != $oldpwd){				
				show_msg('俩次密码不相等');
			}else {
				$users = new users();
				if($users->update(array('id'=>$uid),array('password'=>$oldpwd))){
					redirect('user/login');
				}				
			}			
		}else {
			show_msg('错误的提交');		
		}
	}
	
	/*
	*发送邮件
	*/
	function sedmail($email,$url){
		$mailbody="<HTML><HEAD><META http-equiv=Content-Type content='text/html; charset=utf-8'><style type='text/css'>";
		$mailbody=$mailbody."<!--";
		$mailbody=$mailbody."body,td,th {";
		$mailbody=$mailbody." font-size: 14px;";
		$mailbody=$mailbody." color: #000000;";
		$mailbody=$mailbody."}.red{ color:#FF0000;}";
		$mailbody=$mailbody."-->";
		$mailbody=$mailbody."</style></HEAD><BODY><p><font class='red'> </font><br><br>";
		$mailbody=$mailbody."用户您好，请你重新设置你的密码！<br><br>";
		$mailbody=$mailbody." <a href='".$url."' target='_blank'>请您点击下列连接进行设置</a> </p>";
		$mailbody=$mailbody."<p>如果上连接点击不开请复制地址 <br><br>";
		$mailbody=$mailbody.$url;
		$mailbody=$mailbody."<br><br>在IE上打开</p><br>此邮件为系统自动回复，请不要回复！";
		$mailbody=$mailbody."</BODY></HTML>";
		##########################################
		$smtpserver = "mail.my7g.cn";//SMTP服务器
		$smtpserverport = 25;//SMTP服务器端口
		$smtpusermail = 'wo@my7g.cn'; //SMTP服务器的用户邮箱
		$smtpemailto = $email;//发送给谁
		$smtpuser = "wo@my7g.cn";//SMTP服务器的用户帐号
		$smtppass = "wo11111";//SMTP服务器的用户密码
		$subject = "沃关怀密码找回";//邮件主题
		$mailsubject = "=?UTF-8?B?".base64_encode($subject)."?=";
		$mailbody = $mailbody; //"<h1>This is a test mail</h1>";//邮件内容
		$mailtype = "HTML";//邮件格式（HTML/TXT）,TXT为文本邮件
		##########################################
		$smtp = new smtp($smtpserver,$smtpserverport,true,$smtpuser,$smtppass);
		$smtp->debug = false;//TRUE;//是否显示发送的调试信息
		return $smtp->sendmail($smtpemailto, $smtpusermail, $mailsubject, $mailbody, $mailtype);		
	}
	
}
?>