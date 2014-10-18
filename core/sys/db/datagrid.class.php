
<?php 
/**
* 
*/
class Datagrid 
{

	//模型对象
	private $_object ;

	//查询器条件
	private $_condition = '';

	//分页数
	private $_page_limit=20;

	//分页符
	private $_page_query = 'page';

	//添加开关属性
	private $_is_add = true;

	//删除开关属性
	private $_is_delete = true;

	//修改开关属性
	private $_is_modify = true;


	public $items = array();

	function __construct($obj,$functions)
	{
		
		if( !is_object($obj) ){
			throw_exception('datagrid没有绑定对象');
			
		}
		if( isset($functions['add']) ){
			$this->_is_add = $functions['add'];
		}

		if( isset($functions['delete']) ){
			$this->_is_delete = $functions['delete'];
		}
		if( isset($functions['modify']) ){
			$this->_is_modify = $functions['modify'];
		}

		if( isset($functions['limit'])){
			$this->_page_limit = $functions['limit'];
		}
		if( isset($functions['pagequery'])){
			$this->_page_query = $functions['pagequery'];
		}
		$this->_object = $obj;
	}


	
	function list_and_page($condition,$desc){
		
		$this->_condition = $condition;

		$count = $this->_object->count($this->_condition);
		
		$page = new Page($count,$this->_page_limit);
//		$field = array_keys($this->items); //查询的字段
		$lists = $this->_object->lists($condition,$desc,$page->start,$page->limit);

		return (array(
			'lists'=>$lists,
			'pagestring'=>$page->pageRev()
		));

	}




}
?>