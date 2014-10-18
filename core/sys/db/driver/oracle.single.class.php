<?php
/**
 * 说明：数据库oracle
 * 作者：RobertZeng <zeng444@163.com>
 * 日期：2011-08-27
 * 版本：1.0
 */
class Oracle{


	//查询消耗时间
	public $_runtime = 0;

	//查询开始时间
	protected $_begin_time = 0;

	//更新或插入的ID号
	public $_last_insert_id = 0;


	//查询结束时间
	protected $_end_time = 0 ;

	//当前SQL语句
	protected $_sql = '';

	//是否将开始SQL调试
	protected $_debug = true;

	//返回数据类型 obj/array
	protected $_result_type = 'obj';

	//影响的行数
	protected $_num_rows = 0 ;

	//当前连接
	protected $_link = null;

	//当前资源
	protected $_result = null;

	//是否连接到数据库
	public $_link_status = false;

	//当前实例
	public static $_instance = null;



	public function __construct($config){
		if(!isset($config['host']) || empty($config['host']) ){
			throw_exception('对不起数据库没有配置');
		}
		if(!isset($config['user']) || empty($config['user']) ){
			throw_exception('对不起数据库没有配置');
		}
		if(!isset($config['pass']) || empty($config['pass']) ){
			throw_exception('对不起数据库没有配置');
		}

		if($this->_link==null){
			$this->_link = oci_connect($config['user'],$config['pass'],$config['host']);
		}
		if(!$this->_link){
			
			throw_exception(oci_error());
		}
		$this->_link_status = true;

		
	}



	protected function error(){

		if(!is_resource($this->_link)){
			return ;
		}

		$error = oci_error($this->_link);
		if(!empty($this->_sql)){
			$error = '[SQL错误]：'.$this->_sql.'\n'.$error.'\n\n';
		}
		return $error;
	}


	/**
	 * 执行SQL
	 *
	 * @param unknown_type $sql
	 * @return unknown
	 */
	protected function query($sql){

		if( !$this->_link_status ){
			return false;
		}
		if(!is_resource($this->_link)){
			return false;
		}

		$this->_sql = $sql;
		$this->_begin_time = $this->micro_time();

		$result = oci_parse($this->_link, $sql);
		@oci_execute($result);
		

		//		$result = @mysql_query($this->_sql,$this->_link);
		if(oci_error()){
			throw_exception($this->error());
		}
		if($this->_debug){
			$this->debug();
		}
		if(is_resource($result)){
			$this->_result = $result;
			$this->_num_rows = oci_num_fields($this->_result);
			return true;
		}
	}

	/**
	 * 执行update、delete、insert等操作
	 *
	 * @param unknown_type $sql
	 * @return unknown
	 */
	public function execute($sql){
		if( !$this->_link_status ){
			return false;
		}
		if(!is_resource($this->_link)){
			return false;
		}
		if(!empty($sql)){
			$this->_sql = $sql;
		}
		$this->_begin_time = $this->micro_time();
		//		$result =@mysql_query($this->_sql,$this->_link);
		$result = oci_parse($this->_link, $sql);
		oci_execute($result	);
//		oci_commit($this->_link);  
		if(oci_error()){
			throw_exception($this->error());
		}
		if($this->_debug){
			$this->debug();
		}
		if($result){
			$this->_num_rows = oci_num_fields($this->_link);
			//			$this->_last_insert_id = mysql_insert_id($this->_link);
			return $this->_num_rows;
		}

	}


	/**
	 * 获取一个数据
	 *
	 * @param unknown_type $sql
	 * @return unknown
	 */
	public function getOne($sql){

		if(!empty($sql)){
			$this->query($sql);
		}
		
		if(!$this->_result){
			throw_exception($this->error(),$this->_link);
		}
		
		if($this->_num_rows>0){
			$result =  oci_fetch_array($this->_result);
			$result = array_map('gb2312_to_utf8',$result);
			return @current($result);
		}else{
			return false;
		}
	}

	/**
	 * 获取所有记录
	 *
	 * @param unknown_type $sql
	 * @param unknown_type $result_type
	 * @return unknown
	 */
	public function getAll($sql,$result_type=''){

		if(!empty($sql)){
			$this->query($sql);
		}
		if(!is_resource($this->_link)){
			throw_exception($this->error(),$this->_link);
		}
		$result = array();
		if(!empty($result_type)){
			$this->_result_type = $result_type;
		}
		$oci_fetch_type = ($this->_result_type=='obj') ? 'oci_fetch_assoc':'oci_fetch_object() ';
		while( $row = $oci_fetch_type($this->_result) ){
//			$result[] = $row;
			$result[] = array_map('gb2312_to_utf8',$row);
			//$result[] = array_map('charset_change',$row);
		}
		return $result;
	}

	/**
	 * 获取一条记录
	 *
	 * @param unknown_type $sql
	 * @param unknown_type $result_type
	 * @return unknown
	 */
	public function getRow($sql,$result_type=''){
		
		if(!empty($sql)){
			$this->query($sql);
		}
		if(!$this->_result){
			throw_exception($this->error(),$this->_link);
		}
		$oci_fetch_type = ($this->_result_type=='obj') ? 'oci_fetch_assoc':'oci_fetch_object() ';
		if($this->_num_rows>0){
			$result = array_map('gb2312_to_utf8',$oci_fetch_type($this->_result));
			return  $result;
		}else{
			return false;
		}
	}
	
	/**
	 * 调试信息
	 *
	 */
	protected  function debug(){
		$sql = 	$this->_sql;
		$this->_end_time = $this->micro_time();
		$this->_runtime = $this->_end_time-$this->_begin_time;
	}



	/**
	 * 取得唯一实例
	 *
	 * @param unknown_type $config
	 * @return unknown
	 */
	static public function getInstance($config){
		if(self::$_instance==null){
			self::$_instance = new self($config);
		}
		return self::$_instance;
	}

	/**
	 * 关闭连接
	 *
	 */
	public function close(){
		if(is_resource($this->_link)){
			if (!@mysql_close($this->_link) ){
				throw_exception($message);
			}
			$this->_link_status = false;
			$this->_link = null;
			$this->_result = null;
		}
	}
	/**
	 * 获取微妙时间轴
	 *
	 * @return unknown
	 */
	private function  micro_time(){
		return array_sum(explode(' ',microtime()));
	}

	/**
	 * 显示表字段
	 *
	 * @param unknown_type $table_name
	 * @return unknown
	 */
	public function getTableColumns($table_name){

		$sql= 	($table_name) ? "select * from sys.user_tab_columns where TABLE_NAME='$table_name'":'';
		
		$result =  $this->getAll($sql);
		foreach ($result as $k=>$v){
			$result[$k] = $v['COLUMN_NAME'];
		}
		return $result;
	}
}

?>