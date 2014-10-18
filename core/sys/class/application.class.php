<?php 
/**
 * 说明：控制器实现数据库分布及数据库关系映射
 * 作者：RobertZeng <zeng444@163.com>
 * 日期：2011-10-12
 */
class Application{

	//数据库实例
	public  $_db;

	//数据库配置
	protected $_db_config;

	//表结构缓存文件夹
	protected $_db_cache_path = 'cache/db/';

	private $_table_encode_key = 'oav2201112457';

	//绑定数据表
	protected $_tablename ='';

	//影响的记录ID
	public $_last_insert_id = 0;

	//最后执行的SQL
	public $_last_sql ='';

	//模型层数据
	protected $_form_data = array();
	
 

	//绑定表字段规则
	protected $_validate=array();

	//验证器异常抛出函数
	protected $_validate_msg_box = 'show_msg';

	//当前模型名
	protected $_model_name ='';

	//验证器实例
	protected $_validate_obj = null;

	//memche开关
	protected $_mcache_on = false;

	//memche配置
	protected $_mcache_config = array();

	//memche实例
	protected $_mcache ; //memche对象

	//读取的列
	public $_items = '';

	//读取的列信息
	protected $_datagrid_field = array();


	public function __construct(){

		if(!$this->_tablename)  {
			throw_exception('没有绑定的数据表');
		}
		if(defined('MEM_CACHE') && MEM_CACHE ){
			$this->_mcache_on = true;
		}
		$this->_model_name = strtolower(get_class($this));
		global $module_config,$default_db_config;
		$this->_db_config = $default_db_config;
		if( isset($module_config[$this->_model_name]) ){
			$this->_db_config  = $module_config[$this->_model_name];
		}
		$this->_db = Db::getInstance($this->_db_config);

		//规则验证器
		if(is_array($this->_validate)) {
			$this->_validate_obj = new validate($this->_validate,$this->_validate_msg_box,$this); //定义报警器
			$this->_validate_obj->_table_columns = $this->getDbTableColumns($this->_db_config['database'],$this->_tablename);
		}
	}


	/**
	 * 建立一个memcaced连接
	 *
	 */
	public function getMemCache(){
		if(is_null($this->_mcache) && $this->_mcache_on){
			global $mcache_config; $this->_mcache_config = $mcache_config;
			$this->_mcache = Mcache::getInstance($this->_mcache_config);

		}
		return $this->_mcache;
	}

	/**
	 * 按条件删除一条表记录
	 *
	 * @param array $condition 数据筛选条件
	 * @return bool 返回布尔值
	 */
	public function delete($condition){
		if(empty($condition)) return false; //条件为空时不能更新表
		if($this->_validate_obj!=null && is_array($condition)){
			$condition = $this->_validate_obj->check($condition,'r');
		}

		$sql = Db::delete_to_sql($this->_tablename);
		
		if(!empty($condition)){
			$sql .= Db::condition_to_sql($condition); //设置更新数据的条件
		}
		$this->_last_sql = $sql;
		
		return $this->_db->execute($sql);
	}

	/**
	 * 对字段自增
	 *
	 * @param mixed $condition 自增的条件
	 * @param array $data 需要自增的数据
	 * @param bool $is_validate 是否验证数据
	 * @return bool
	 */
	public function increase($condition,$data, $is_auto_update_time=true,$is_validate=true){
		if(empty($data) || !is_array($data)) return false;
		if(empty($condition)) return false; //条件为空时不能更新表
		if($this->_validate_obj!=null){
			if($is_validate){
				$data = $this->_validate_obj->check($data);
				$this->_form_data = $data;
			}
			if(is_array($condition)){
				$condition = $this->_validate_obj->check($condition,'r');
			}
		}
		$new_data =  '';
		foreach ($data as $k=>$v){ $new_data .= " $k=$k+1 ,"; }
		if( $is_auto_update_time) {
			$data['updated_at']  = date('Y-m-d H:i:s',time());
		}
		$new_data = substr($new_data,0,strlen($new_data)-1);
		$sql = Db::update_to_sql($this->_tablename,$new_data);
		if(!empty($condition)){ $sql .= Db::condition_to_sql($condition); } //设置更新数据的条件
		$this->_last_sql = $sql;
		
		return $this->_db->execute($sql);
	}

	/**
	 * 通用表更新数据
	 *
	 * @param mixed string/array $condition 数据筛选条件
	 * @param array $data 更新数据
	 * @param bool $is_auto_update_time 是否自动更新日期更新
	 * @param bool $is_validate 是否对模型进行验证
	 * @return bool
	 */
	public function update($condition ,$data=array() , $is_auto_update_time=true, $is_validate=true){
			
		if( !is_array($data)) return false;
	
		if(empty($condition)) return false; //条件为空时不能更新表
		if($this->_validate_obj!=null){
			if($is_validate){
				$this->_form_data = $data;
				
				$data = $this->_validate_obj->check($data);
				
			}
			if(is_array($condition)){
				$condition = $this->_validate_obj->check($condition,'r');
			}
		}
	
		if( $is_auto_update_time ){
			$data['updated_at']  = date('Y-m-d H:i:s',time());
		}
		
		$new_data = Db::set_value_to_sql($data);
		$sql = Db::update_to_sql($this->_tablename,$new_data);
		
		if(!empty($condition)){ $sql .= Db::condition_to_sql($condition); } //设置更新数据的条件
		$this->_last_sql = $sql;

		return $this->_db->execute($sql);

	}
	/**
	 * 通用表更新一条数据(忽略验证)
	 *
	 * @param array $data 插入数据
	 * @return booleen
	 */
	public function save($condition ,$data , $is_auto_update_time=true, $is_validate=false){
		return $this->update($condition ,$data , $is_auto_update_time, $is_validate);
	}

	/**
	 * 通用表插入一条数据
	 *
	 * @param array $data 插入的关联数组
	 * @param bool $is_auto_update_time 是否自动维护更新字段
	 * @param bool $is_auto_insert_time 是否自动维护插入字段
	 * @param bool $is_validate 是否模型层验证
	 * @return bool 
	 */
	public function add($data, $is_auto_update_time=true, $is_auto_insert_time=true,$is_validate=true){
		
		if(empty($data) || !is_array($data)) return false;
		if($this->_validate_obj!=null && $is_validate){
			
			$this->_form_data = $data;
			$data = $this->_validate_obj->check($data,'w','full');
		}
		
		if( $is_auto_insert_time ){
			$data['created_at'] =  date('Y-m-d H:i:s',time());
		}
		if( $is_auto_update_time ){
			$data['updated_at']  = date('Y-m-d H:i:s',time());
		}
		$insert_values = Db::insert_value_to_sql($data);
		$this->_last_sql = Db::insert_to_sql($this->_tablename,$insert_values);
	
		$result = $this->_db->execute($this->_last_sql);
		if($result) $this->_last_insert_id = $this->_db->_last_insert_id;
	
		return $result;

	}
	
	
	/**
	 * 通用表替换或插入一条数据
	 *
	 * @param array $data 插入的关联数组
	 * @param bool $is_auto_update_time 是否自动维护更新字段
	 * @param bool $is_auto_insert_time 是否自动维护插入字段
	 * @param bool $is_validate 是否模型层验证
	 * @return bool 
	 */
	public function replace($data, $is_auto_update_time=false, $is_auto_insert_time=false,$is_validate=true){
		
		if(empty($data) || !is_array($data)) return false;
		
		if($this->_validate_obj!=null && $is_validate){
			
			$this->_form_data = $data;
			$data = $this->_validate_obj->check($data,'w','full');
		}
		
		if( $is_auto_insert_time ){
			$data['created_at'] =  date('Y-m-d H:i:s',time());
		}
		if( $is_auto_update_time ){
			$data['updated_at']  = date('Y-m-d H:i:s',time());
		}
		$insert_values = Db::insert_value_to_sql($data);
		$this->_last_sql = Db::repalce_to_sql($this->_tablename,$insert_values);
	
		$result = $this->_db->execute($this->_last_sql);
		if($result) $this->_last_insert_id = $this->_db->_last_insert_id;
	
		return $result;

	}
	
	
	/**
	 * 通用表插入一条数据(忽略验证)
	 *
	 * @param array $data 插入数据
	 * @return booleen
	 */
	public function create($data, $is_auto_update_time=true, $is_auto_insert_time=true,$is_validate=false){
		return $this->add($data,$is_auto_update_time,$is_auto_insert_time,$is_validate);
	}


	/**
	 * 缓存数据库字段
	 *
	 * @param unknown_type $db
	 * @param unknown_type $tablename
	 */
	public function  getDbTableColumns($db,$tablename){
		if( !is_readable( ROOT_PATH.$this->_db_cache_path ) )  @mkdir( ROOT_PATH.$this->_db_cache_path,0777);
		$table_info = ROOT_PATH.$this->_db_cache_path."{$this->encode_db_name($db)}/{$this->encode_db_name($tablename)}";
		if(is_readable($table_info)){
			$result =  json_decode(file_get_contents($table_info));
		}else{
			$folder = ROOT_PATH.$this->_db_cache_path.$this->encode_db_name($db);
			if(!is_readable($folder)){
				if( mkdir($folder,0777) ){
				}else{
					throw_exception('不可写的缓存目录DB！请修改'.$folder.'权限');
				}
			}
			$result = file_put_contents($table_info,json_encode($this->_db->getTableColumns($tablename)));
		}
		return  $result;
	}

	/**
	 * 加密数据表、字段
	 *
	 */
	private function  encode_db_name($name){
		return md5($name.$this->_table_encode_key);
	}

	/**
	 * 通用统计表记录数
	 *
	 * @param unknown_type $condition
	 * @param unknown_type $primary_key
	 * @param unknown_type $is_like
	 * @param unknown_type $list
	 * @return unknown
	 */
	public function count($condition=array(),$primary_key='id'){


		if($this->_validate_obj!=null && is_array($condition)){
			$condition = $this->_validate_obj->check($condition,'r');
		}
		$sql = Db::select_to_sql("count(`$primary_key`)",$this->_tablename) ;
		if(!empty($condition)){ $sql .=  Db::condition_to_sql($condition); }
		$this->_last_sql = $sql;
		
		return $this->_db->getOne($sql);
	}

	/**
	  * 通用表读取一条记录
	  *
	  * @param array/string $condition 筛选条件
	  * @return array
	  */
	public function  view($condition,$order='',$is_validate=true){
		 
		if($this->_validate_obj!=null && is_array($condition) && $is_validate){
			
			$condition = $this->_validate_obj->check($condition,'r');
		}
		
		$sql = Db::select_to_sql($this->_items,$this->_tablename) ;
		$this->_items='';
		if(!empty($condition)){ $sql .=  Db::condition_to_sql($condition); }
		if(!empty($order)){ $sql .= Db::order_to_sql($order); }
		$this->_last_sql = $sql;
		return $this->_db->getRow($sql);

	}

	/**
	 * 通用表读取信息
	 *
	 * @param array/string $condition 筛选表条件
	 * @param array/string $order 排序条件
	 * @param int $start 数据起点
	 * @param int $limit 读取条数
	 * @param booleen $is_like 是否LIKE查询
	 * @return array 数组数据
	 */
	public  function lists($condition=array(),$order='',$start=0,$limit=100){

		if($this->_validate_obj!=null && is_array($condition)){
			$condition = $this->_validate_obj->check($condition,'r');
		}
		$sql = Db::select_to_sql($this->_items,$this->_tablename) ;
		$this->_items='';
		if(!empty($condition)){ $sql .=  Db::condition_to_sql($condition); }
		if(!empty($order)){ $sql .= Db::order_to_sql($order); }
		$sql .=  Db::limit_to_sql($start,$limit);
		$this->_last_sql = $sql;
		
		return $this->_db->getAll($sql);
	}

	/**
	 * 设定查询字段
	 *
	 * @param array/string $array 字段表
	 * @return array 返回对象
	 */

	public  function items($array){

		if( empty($array) ) throw_exception('没有绑定字段');
		$array = ( is_array($array) )? $array :explode(',', $array);
		$this->_items = implode(',', $array);
		return $this;

	}

	/**
	 * items的别名函数
	 *
	 * @param array/string $array 字段表
	 * @return object 返回对象
	 */
	public  function item($array){
		return $this->items($array);
	}

	/**
	 * 为datagrid构建字段参数
	 *
	 * @param array $array 字段关联数组 array('id'=>'主键'')
	 * @return object 返回对象
	 */
	public function field($array){
		if(!is_array($array)){
			throw_exception('datagrid field必须绑定数组');
		}
		$this->_datagrid_field = $array;
		$this->items(array_keys($array));
		unset($this->_datagrid_field );
		return $this;
	}

	/**
	 * field的别名函数
	 *
	 * @param array/string $array 字段表
	 * @return object 返回对象
	 */
	public function fields($array){
		return $this->field($array);
	}

	/**
	 * 构建gridview,将条件数据构建成table并在table中绑定增删改查
	 *
	 * @param array $condition 查询条件 array('id'=>2)
	 * @param array $desc 排序条件 array('id'=>'desc')
	 * @param array $functions 功能绑定 默认 array('add'=>true,'delete'=>true,'modify'=>true,'limit'=>20,'pagequery'=>'page') 
	 * @return string 返回html
	 */
	public function gridview($condition=array(),$desc=array(),$functions=array()){
		$datagrid = new Datagrid($this,$functions);
		$datagrid->items=$this->_datagrid_field;
		return $datagrid->list_and_page($condition,$desc);
	}
	
	public function getname($id,$name='name'){
		$info = $this->view(array('id'=>$id));
		return ( isset($info[$name]) )?$info[$name]:'';
	}
}

?>