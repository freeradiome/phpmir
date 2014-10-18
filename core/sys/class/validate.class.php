<?php
/**
 * 说明：自动验证器
 * 作者：RobertZeng <zeng444@163.com>
 * 日期：2011-10-19 
 * 使用说明：
 * 方式1、表单对应表中字段，嵌入模型层绑定数据表，模型层中定义规则，插入表时候自动进行验证 
 * 方式2、单独实例化使用如下：
 * 
 * $validate = array(
 * 	'name'=>array('msg'=>'必须为身份证','type'=>'idcard'),
 * 	'sex'=>array('unique'=>false,'msg'=>'此为必填选项！','type'=>'number')
 * ); //定义规则
 * 
 * $validate = new validate($validate,$_fun); //载入规则
 * $date = $validate->check($post['data']); //验证表单
 */
class validate{

	public $_is_xss = true; //是否默认过滤XSS

	public $_is_trim = true; //是否默认去空格

	public  $_is_intval = false; //是否默认强制转换为整型

	public $_is_unique = false ;//是否默认为必填

	public $_callback_fun = ''; //回调函数


	public $_table_columns  = array(); //当前表字段数组

	private  $_validate_rule = array();

	private $_form_data = array();

	private $_module_object; //模型层对象

	private $_check_fun_list = array('min','max','len','msg','rule','eq','neq','type','etype','unique','xss','trim','intval','fun'); //允许接受的参数

	private $_type_fun_list  = array('number','string','zhcn','mail','mobile','zipcode','idcard'); //类型参数

	public function __construct($_validate,$_fun='',$object){
		if(!is_object($object)){
			throw_exception('验证器没有绑定模型');
		}
		$this->_module_object = $object;
		if(!empty($_fun))  $this->_callback_fun =$_fun;
		if(!empty($_validate) && is_array($_validate)){
			$this->_validate_rule = $_validate;
		}
	}
	/**
	 * 过滤或者检测表单数据
	 *
	 * @param array $data 表单数组
	 * @param  array $type 验证类型 w为写数据库前的过滤 r为读数据库前的过滤
	 * @return array 表单数据
	 */
	public function check($data,$type='w',$check = 'part'){

		if(!is_array($data) || !is_array($this->_validate_rule)) throw_exception('不合法的模型规则或者表单数据！');
		$this->_form_data = $data;
		if($check=='full'){ //用于插入操作
			$this->check_full_form($type);
		}else if($check=='part'){ //用户更新和条件操作
			$this->check_part_condition($type);
		}
		return $this->_form_data;
	}
	/**
	 * 检测条件及更新数据
	 *
	 * @param unknown_type $type
	 */
	private function  check_part_condition($type){
		foreach ($this->_form_data as $k=>$v){
			$rule = ( isset($this->_validate_rule[$k]) && is_array($this->_validate_rule[$k]) )?$this->_validate_rule[$k]:array();
			if($type=='w'){
				$this->_form_data[$k]=$this->filter($v,$rule);
			}else{
				if($k=='{sql}') $rule['xss']=false;
				$this->_form_data[$k]=$this->pre_filter($v,$rule,true);
			}
		}
	}
	/**
	 * 检查插入完全数据验证
	 *
	 * @param unknown_type $type
	 */
	private function check_full_form($type){

		if(empty($this->_table_columns))  throw_exception('不合法的模型规则或者表单数据！');

		foreach ($this->_table_columns as $v){
				

			$rule = ( isset($this->_validate_rule[$v]) && is_array($this->_validate_rule[$v]) )?$this->_validate_rule[$v]:array();
			$value = ( isset($this->_form_data[$v]))?$this->_form_data[$v]:'';
			if($type=='w'){
					
				$value = $this->filter($value,$rule);
			}else{
				$value = $this->pre_filter($value,$rule,true);
			}
			if(isset($this->_form_data[$v])){
				$this->_form_data[$v]=$value;
			}
			
		}
	}

	/**
	 * 解析规则并进行encode($condition where使用)
	 *
	 * @param string $value验证的值
	 * @param array $rule 规则数组
	 * @return string 过滤后的值
	 */
	private function pre_filter($value,$rule,$is_fun=false){
		
		if(!preg_match('/\{(.*)\}/isU', $value)){//跳过验证符

			if( get_magic_quotes_gpc() ){ $value = stripslashes($value); }
			if(!isset($rule['trim'])) $rule['trim'] = $this->_is_trim;
			if(!isset($rule['xss'])) $rule['xss'] = $this->_is_xss;
			if(!isset($rule['intval']))  $rule['intval'] = $this->_is_intval;
			if(  $rule['intval']==true){ $value = intval($value); } //是否trim过滤
			if(  $rule['trim']==true && $value ){ $value = trim($value); } //是否trim过滤
			if(  $rule['xss']==true && $value  ){ $value = $this->xss($value); } //是否XSS过滤
			if(  isset( $rule['fun']) &&  $rule['fun'] && $is_fun==true){
				$to_fun =  (function_exists( $rule['fun']))?$rule['fun']:array($this->_module_object,$rule['fun']);
				$value = call_user_func($to_fun,$value);
			}
		}
		return $value;
	}


	/**
	 * 解析规则并进行验证(update,insert 对$data使用)
	 *
	 * @param string $value 验证的值
	 * @param array $rule 规则数组
	 * @return string 过滤后的值
	 */
	private function filter($value,$rule){

		$value = $this->pre_filter($value,$rule);
		
		if(!isset($rule['unique'])) $rule['unique'] = $this->_is_unique;
		
		if( ($rule['unique']==true) ||  ($rule['unique']==false && !empty($value) )){
			$is_msg = ( !isset($rule['msg']) ) ? false : true ;
			if( in_array($rule['type'],$this->_type_fun_list) ) {
				$result = call_user_func(array($this,'is_'.$rule['type']),$value);
				if(!$result){
					if($is_msg) $this->error_callback($rule['msg']);exit();
				}
			}elseif(isset($rule['type'])) {
				throw_exception("不存在的验证类型“{$rule['type']}”");
			}
			if(isset($rule['etype']) && !empty($rule['etype'])){
				if( !method_exists($this->_module_object,$rule['etype'])) {
					throw_exception("用户验证函数“{$rule['etype']}”没有创建");
				}
				//				$result = call_user_func($this->_model_name.'::'.$rule['type'],$value);
				$result = call_user_func(array($this->_module_object,$rule['etype']),$value);
				if(!$result){
					if($is_msg) $this->error_callback($rule['msg']);exit();
				}
			}

			//长度判断
			if(isset( $rule['len']) && !$this->len($value,$rule['len']) ){
				if($is_msg) $this->error_callback($rule['msg']);exit();
			}
			//检查最大长度
			if( isset( $rule['max']) && !$this->max($value,$rule['max']) ){
				if($is_msg) $this->error_callback($rule['msg']);exit();
			}

			//检查最小长度
			if(  isset( $rule['min'])  && !$this->min($value,$rule['min']) ){
				if($is_msg) $this->error_callback($rule['msg']);exit();
			}
			//检查是否等于某值
			if(isset( $rule['eq']) && !$this->eq($value,$rule['eq']) ){
				if($is_msg) $this->error_callback($rule['msg']);exit();
			}
			//检查是否不等于某值
			if(isset( $rule['neq']) && !$this->neq($value,$rule['neq']) ){
				if($is_msg) $this->error_callback($rule['msg']);exit();
			}
			//自定义正则验证
			if(isset( $rule['rule']) && !$this->regexp($value,$rule['rule']) ){
				if($is_msg) $this->error_callback($rule['msg']);exit();
			}
		}

		//自定义函数
		if(  isset( $rule['fun']) &&  $rule['fun'] ){
		
			$to_fun =  (function_exists( $rule['fun']))?$rule['fun']:array($this->_module_object,$rule['fun']);
			$value = call_user_func($to_fun,$value);

		}
		return $value;
	}
	//XSS过滤
	private  function xss($value){
		return htmlspecialchars($value);
	}
	//最小字符数检测
	public function min($value,$len){
		if($this->str_real_len($value)>=intval($len)){
			//					if(strlen($value)>=intval($len)){
			return true;
		}
	}
	//最大字符数检测
	public function max($value,$len){
		if($this->str_real_len($value)<=intval($len)){
			//					if(strlen($value)<=intval($len)){
			return true;
		}
	}
	//长度检测
	public function len($value,$len){

		if($this->str_real_len($value)==intval($len)){
			//					if(strlen($value)==intval($len)){
			return true;
		}
	}
	//验证是否为数字
	public function is_number($value){
		$regExp = '/^[\d]+$/';
		if(preg_match($regExp,$value)) return true;
	}

	//验证是否为中文
	public function is_zhcn($value){
		$regExp = "/^[\x{4e00}-\x{9fa5}]+$/u";
		if(preg_match($regExp,$value)) {
			return true;
		}
	}
	//验证是否为邮件
	public function is_mail($value){
		$regExp = '/^[-_A-Za-z0-9]+@([_A-Za-z0-9]+\.)+[A-Za-z0-9]{2,3}$/';
		if(preg_match($regExp,$value))  return true;
	}
	//验证是否为手机
	public function is_mobile($value){
		if($this->is_number($value) && strlen($value)==11) return true;
	}
	//验证是否为邮政编码
	public function is_zipcode($value){
		if($this->is_number($value) && strlen($value)==9) return true;
	}
	//验证是否为身份证
	public function is_idcard($value){
		$regExp = '/^[\d]{17}[A-Za-z]{1}$|^[\d]{18}$|^[\d]{15}$/';
		if(preg_match($regExp,$value))  return true;
	}
	//获取长度
	public function str_real_len($value){
		return mb_strlen($value,'gb2312');
	}
	//eq
	public function eq($value,$v){
		if($value==$v) return true;
	}
	//neq
	public function neq($value,$v){
		if($value!=$v) return true;
	}
	public function regexp($value,$v){
		$regExp = preg_quote('/'.$v.'/');
		if(preg_match($regExp,$value))  return true;
	}
	//按需求重写容错函数
	protected function error_callback($message){
		if(!empty($this->_callback_fun)) call_user_func($this->_callback_fun,$message);
	}
}

?>