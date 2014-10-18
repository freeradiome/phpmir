<?php
class Db{

	/**
	 * 快速取得实例
	 *
	 * @return obj
	 */
	public static function getInstance(){
		
		$args = func_get_args();
		
		return get_instance_of(__class__,'factory',$args);
	}
	
	/**
	 * 工厂方法
	 *
	 * @param array $args
	 * @return objetc
	 */
	public function &factory($args=''){
		
		$dbClass = 	default_db_type;
		//$classFile ='driver/'.$dbClass.'.single.class.php';
		$classFile = 'driver/'.$dbClass.'.class.php';
		require_once($classFile);
		if ( class_exists($dbClass) ){
			$o = new Mysql($args);
			//$o = Mysql::getInstance($args);
			return $o;
		}
	}

	/**
	 * 解释查询条件得到SQL条件
	 *
	 * @param mixed $condition  数组或字符串条件
	 * @return string
	 */
	public static function condition_to_sql( $condition ){
		if(is_array($condition)){
			$sql_condition='where';
			foreach ($condition as $k=>$v){
				if($k!='{sql}'){
					if(preg_match('/\{(.*)\}/isU',$v,$stra)){
						$sql_condition .= " $k {$stra[1]} and";
					}else{
						$sql_condition .= " $k = '".mysql_escpage($v)."' and";
					}
				}else{
					$sql_condition .= " $v and"; 
				}
			}
			$sql_condition = substr($sql_condition,0,strlen($sql_condition)-3);
		}else{
			$sql_condition .= (empty($condition))?'': "where ".$condition;
		}
		return $sql_condition;
	}

	/**
	 * 解释更新数据得到SQL语句
	 *
	 * @param array $data 数组
	 * @return string 
	 */
	public static function set_value_to_sql(  $data  ){
		$new_data =  '';
		foreach ($data as $k=>$v){ $new_data .= " `$k`='".mysql_escpage($v)."',"; }
		return substr($new_data,0,strlen($new_data)-1);
	}
	/**
	 * 解释插入数据得到SQL
	 *
	 * @param array $data 数组
	 * @return string
	 */
	public static function insert_value_to_sql(  $data  ){
		$keys = array_map( 'format_table_key',array_keys($data) );
		$data = array_map( 'mysql_escpage',array_values($data) );
		$value = array_map( 'format_table_value',$data );
		return " (".implode(',',$keys).")values(".implode(',',$value).")";
	}

	/**
	 * 解释查询条件得到$order
	 *
	 * @param array $condition 条件
	 * @return string
	 */
	public static  function order_to_sql( $condition  ){
		if(is_array($condition)){
			$sql_condition=' order by';
			foreach ($condition as $k=>$v){
				$sql_condition .= " `$k` $v,";
			}
			$sql_condition = substr($sql_condition,0,strlen($sql_condition)-1);
		}else{
			$sql_condition = (empty($condition))?'':" order by ".$condition;
		}
		return $sql_condition;
	}

	
	/**
	 * 解释得到select
	 *
	 * @param array $item 字段
	 * @param array $table 表名
	 * @return string
	 */
	public static function select_to_sql($item,$table){
		$item = ( $item ) ? $item : '*' ;
		return "select $item from `$table` ";
	}


	/**
	 * 将条件和insert语句结合
	 *
	 * @param array $table 表名
	 * @param array $value 插入值的sql string
	 * @return string
	 */
	public static function insert_to_sql($table,$value){
		return "insert into `$table` $value";
	}
	
	/**
	 * 将条件和repalce语句结合
	 *
	 * @param array $table 表名
	 * @param array $value 插入值的sql string
	 * @return string
	 */
	public static function repalce_to_sql($table,$value){
		return "replace into `$table` $value";
	}
	/**
	 * 将条件和UPDATE语句结合
	 *
	 * @param array $table 表名
	 * @param array $new_data  更新入值的和条件
	 * @return string
	 */
	public static function update_to_sql($table,$new_data){
		return "update `$table` set $new_data ";
	}

	/**
	 * 得到delete语句
	 *
	 * @param array $table 表名
	 * @return string
	 */
	public static function delete_to_sql($table){
		return "delete from `$table`";

	}
	/**
	 * 解释得到limit
	 *
	 * @param array $start 开始
	 * @param array $limit 结束
	 * @return string
	 */
	public static function limit_to_sql($start=0,$limit=20){
		return " limit $start,$limit";
	}
	
}

?>