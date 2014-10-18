<?php
/**
 * 说明：数据库操作类
 * 作者：RobertZeng <zeng444@163.com>
 * 日期：2011-08-27
 * 版本：1.0
 */
class Mysql{

	//查询消耗时间
	public $_runtime = 0;

	//查询开始时间
	protected $_begin_time = 0;

	//更新或插入的ID号
	public $_last_insert_id = 0;

	//查询结束时间
	protected $_end_time = 0 ;

	protected $_config = array();

	//当前SQL语句
	protected $_sql = '';

	//是否开启长连接
	protected $_pcontent = false;

	//是否将开始SQL调试
	protected $_debug = true;

	//事务指定数
	protected $_transTimes = 0;

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
		if(!isset($config['database']) || empty($config['database']) ){
			throw_exception('对不起数据库没有配置');
		}
		if(!isset($config['port']) || empty($config['port']) )  $config['port']=3306;
		$this->_config = $config;
	}

	protected function  connect(){
		if($this->_link==null  && !empty($this->_config)){
		
			$config = $this->_config;
			if( $this->_pcontent ){
				$this->_link = @mysql_pconnect($config['host'].':'.$config['port'],$config['user'],$config['pass']);
			}else {
				$this->_link = @mysql_connect($config['host'].':'.$config['port'],$config['user'],$config['pass']);
			}
			if(!$this->_link){
				throw_exception(mysql_error());
			}
			if(!@mysql_select_db($config['database'],$this->_link)){
				throw_exception(mysql_error());
			}
			if(isset($config['charset']) && !empty($config['charset'])){
				mysql_query('set names '.$config['charset'],$this->_link);
							
			}
			$this->_link_status = true;

		}
	}

	/**
     * 初始化数据库连接
     * @param bool $master 是否是主服务器
     * @return void
     */
	protected function initConnect() {
		if ( !$this->_link_status ){
			$this->connect();
		}
	}

	protected function error(){
		if(!is_resource($this->_link)){
			return ;
		}
		$error = mysql_error($this->_link);
		if(!empty($this->_sql)){
			$error = '[SQL错误]：'.$this->_sql.'\n'.$error.'\n\n';
		}
		Log::write($error);
		return $error;
	}

	/**
	 * 执行SQL
	 *
	 * @param unknown_type $sql
	 * @return unknown
	 */
	protected function query($sql){
		$this->initConnect();
		if( !$this->_link_status ){
			return false;
		}

		if(!is_resource($this->_link)){
			return false;
		}
		$this->_sql = $sql;
		$this->_begin_time = $this->micro_time();
		$result = mysql_query($this->_sql,$this->_link);
		if(mysql_errno()){
			throw_exception($this->error());
		}
		if($this->_debug){
			$this->debug();
		}
		if(is_resource($result)){
			$this->_result = $result;
			$this->_num_rows = mysql_num_rows($this->_result);
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

		$this->initConnect();
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
		$result=@mysql_query($this->_sql,$this->_link);
		if(mysql_errno()){
			throw_exception($this->error());
		}
		if($this->_debug){
			$this->debug();
		}
		if($result){
			$this->_num_rows = mysql_affected_rows($this->_link);
			$this->_last_insert_id = mysql_insert_id($this->_link);
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
			if ( mysql_data_seek($this->_result,0) ){
				$result = mysql_fetch_assoc($this->_result);
			}
			return @current($result);
		}else{
			return array();
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
			throw_exception(mysql_error(),$this->_link);
		}
		$result = array();
		if(!empty($result_type)){
			$this->_result_type = $result_type;
		}
		$mysql_fetch_type = ($this->_result_type=='obj') ? 'mysql_fetch_assoc':'mysql_fetch_object';
		while( $row = $mysql_fetch_type($this->_result) ){
			$result[] = $row;
		}
		if(!empty($result)) mysql_data_seek($this->_result,0); //将指针移动回去
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
		if(!empty($result_type)){
			$this->_result_type = $result_type;
		}
		if(!$this->_result){
			throw_exception($this->error(),$this->_link);
		}
		$mysql_fetch_type = ($this->_result_type=='obj') ? 'mysql_fetch_assoc':'mysql_fetch_object';
		if($this->_num_rows>0){
			if ( mysql_data_seek($this->_result,0) ){
				return  $mysql_fetch_type($this->_result);
			}
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
	 * @param array $config
	 * @return object
	 */
	static public function getInstance($config){
		if(self::$_instance==null){
			self::$_instance = new self($config);
		}
		return self::$_instance;
	}

	/**
	 * 获取表字段信息
	 *
	 * @param unknown_type $table_name
	 * @return unknown
	 */
	public function getTableColumns($table_name){
		$sql= 	($table_name)?"SHOW COLUMNS FROM $table_name":'';
		$result =  $this->getAll($sql);
		foreach ($result as $k=>$v){
			$result[$k] = $v['Field'];
		}
		return $result;
	}

	/**
	 * 获取数据库版本信息
	 *
	 * @return unknown
	 */
	public function get_server_info(){
		if(is_resource($this->_link)){
			return mysql_get_server_info($this->_link);
		}
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
	 * 事务
	 */
	public function startTrans() {
		//数据rollback 支持
		if ( $this->_transTimes==0 ){
			mysql_query('START TRANSACTION',$this->_link);
		}
		$this->_transTimes++;
		return ;
	}

	/**
     * 用于非自动提交状态下面的查询提交
     * @return boolen
     */
	public function commit(){
		if ( $this->_transTimes>0 ){
			$result = mysql_query('COMMIT',$this->_link);
			$this->_transTimes = 0;
			if ( !$result ){
				throw_exception($this->error());
				return false;
			}
		}
		return true;
	}

	/**
     * 事务回滚
     * @return boolen
     */
	public function rollback(){
		if ( $this->_transTimes>0 ){
			$result = mysql_query('ROLLBACK',$this->_link);
			$this->_transTimes = 0;
			if ( !$result ){
				throw_exception($this->error());
				return false;
			}
		}
		return true;
	}


	/**
	 * 转义字符
	 *
	 * @param unknown_type $str
	 * @return unknown
	 */
	public function mysql_escpage($str){
		return mysql_real_escape_string($str);
	}

	/**
	 * 获取微妙时间轴
	 *
	 * @return unknown
	 */
	private function  micro_time(){
		return array_sum(explode(' ',microtime()));
	}


}
?>