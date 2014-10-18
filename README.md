phpmir
======
三、控制器说明
控制器在controller目录中命名规则为“类名.php”，site.php是一个案例
主要的控制器中方法如下

控制器过滤
控制器过滤：是指在进入控制器前会先执行Controller对象中的filter()方法，一般用于进入程序前验证登录状态、用户权限等操作。
例外的变量：指哪些继承的对象或者对象中的方法属于控制器过滤例外的情况。
$this->_filter_except_class		class过滤的例外
$this->_filter_except_method		method过滤的例外
$this->_cached_page_time		需静态化处理的页面的静态化时间
$this->_page_cached_method		需静态化处理的页面方法
模板控制
 $this->fetch($filename)	 $filename 模版文件名	返回编译后的模版内容
$this->data( $symbol,$content )	$symbol 定义模版变量名
$content 赋值	将组数据提供给模版
$this->datas($array)	同上变量定义在array传入	将一组数据提供给模版
$this->render($filename)	$filename 模版文件名	渲染模版
页面跳转
redirect($url,$route=true,$time=0,$msg='')	$url	要定向的URL地址
$time	定向的延迟时间，单位为秒
$msg	提示信息	URL重定向
url_back()		自动跳转到上一页地址
数据控制
_g($name, $type = '')	$name 变量名	读取参数GET或者POST参数
trim_xss($data)	$type 过滤成的类型	对GET或者POST数据安全处理
四、模型层说明
模型层在modules目录中，命名规则为“类名.class.php”，articles.class.php是一个,
模型层在操作数据库时尽量使用对象映射的方式，自动验证减少不必要的数据操作
案例
全局变量
 $this->_tablename 	string	定义此变量让模型和表绑定
 $this->_form_data	array	模型层数据，插入或更新时候初始化，方便_validate中fun方法调取
 $this->_validate 	array	定义此变量数据KEY对应字段
Value对应规则（规则参考数据验证的规则说明）
$this->_last_insert_id 	string	最后条插入的记录id
$this->_last_sql 	string	最后条执行的sql
数据库访问（自动验证）
$this->item($array)	$array 字段表	只访问指定字段的记录array(''name','passwd')
$this->fields($array)	$array 字段表和表说明的二位数组	列出读取的表字段和字段说明array(''name'=>'姓名','passwd'=>'密码')
$this->gridview($condition=array(),$desc=array(),$functions=array())	$condition 查询条件 array('id'=>2)
array $desc 排序条件 $functions 功能绑定 默认 array('add'=>true,'delete'=>true,'modify'=>true,'limit'=>20,'pagequery'=>'page') 
	通用带分页的表读取信息 返回html
$condition构造规则
Array(
'name'=>1 //建立一个对等式
'key'=>"{ > 1}", //建立一个不等式
'{sql}'=>" i{d>0 or id<100} " //建立sql式
}
$this->lists($condition=array(),$order='',$start=0,$limit=100)	$condition 筛选表条件
$order 排序条件
$start 数据起点
$limit 读取条数
$is_like 是否LIKE查询	通用表读取信息
$this->count($condition=array(),$primary_key='id')	$condition 筛选表条件
$primary_key 设置主键	通用统计表记录数
$this->view($condition)	array/string $condition 筛选条件	 通用表读取一条记录
$this->create($data)	$data 插入数据
	add($data,$is_validate=false)的别名函数，强制$is_validate为false
$this->add($data,$is_validate=true)	$data 插入数据
$is_validate 是否自动进行模型数据验证	通用表插入一条数据
$this->update($condition ,$data , $is_validate=true)	$condition 筛选表条件
$data 自增的数据
$is_validate 是否自动进行模型数据验证	通用表更新数据
$this->increase($condition,$data, $is_validate=true)	$condition 筛选表条件
$data 插入数据
$is_validate 是否自动进行模型数据验证	按条件删除一条表记录
$this->delete($condition)	$condition 筛选表条件	按条件删除一条表记录
低级数据库访问（不验证）
$this->_db->getAll($sql)	$sql sql语句	 获取多列数据
$this->_db-> execute($sql)	$sql sql语句	执行一句sql
$this->_db-> getOne($sql)	$sql sql语句	获取一个数据
$this->_db->getAll($sql,$result_type='')	$sql sql语句
$result_type 返回类型（数组/对象）	获取所有记录
$this->_db->getRow($sql,$result_type='')	$sql sql语句
$result_type 返回类型（数组/对象）	获取一条记录
$this->_db-get_server_info()		获取数据库版本信息
$this->_db->startTrans()		建立数据库事务
$this->_db->rollback()		事务回滚
$this->_db->commit()		用于非自动提交状态下面的查询提交
$this->_db->getTableColumns($table_name)	$table_name 表名	获取表字段信息
 $this->_db->debug()	$result_type 返回类型（数组/对象）	调试信息,显示执行时间
缓存控制
$this->getMemCache()		按系统配置自动建立一个memcaced连接
$this->getMemCache()		按系统配置自动建立一个memcaced连接

模型层中数据验证的规则说明：

标签	参数类型	可选参数	说明
type	string	number	是否为数字类型
		string	是否为字符类型
		zhcn	是否是中文
		mail	是否是合法的邮件格式
		mobile	是否为合法的手机格式
		tel(0.9)	是否为合法的座机格式
		zipcode	是否为合法的邮政编码
		idcard	是否为合法的身份证号码
unique（0.7）	bool	true/false	是否为必填项（默认true）
fun 	string	string	自定义验证规则，支持对当前模型对象中的非静态方法访问，使用$this->_form_data对数据进行访问
rule（0.4）	string	正则式	直接定义正则式规则作为验证条件
max	integer	任意数字	最大多少个字符
min	integer	任意数字	最少多少个字符
len	integer	任意数字	必须多少个字符
eq	string	任意字符	是否等于某值
neq	string	任意字符	是否不等于某值
msg	string	任意字符	定义容错提示信息
msgbox（0.5）	string	元素ID	容错信息的容器（当定义了容错容器后将不再默认方式弹出错误提示）
sucess_css（0.5）	string	css class	正确提示的容错容器CSS样式（class名）
failed_css（0.5）	string	css class	错误提示的容错容器CSS样式（class名）

五、页面整体缓存
1、使用方法
1、1设置缓存
控制器中定义：

protected  $_cached_page_time = 4; //静态化时间秒
protected  $_page_cached_method = array('index'); //需要静态化的方法

 完整例子：
<?php
class Site extends Controller{

	//静态化时间
	protected $_cached_page_time = 3600;
	
	//需静态化处理的页面方法
	protected $_page_cached_method = array('index');
	
	function index(){
	    $artcles = new articles();
		$artcles->view(array('id'));
		$this->render('index');
	}
}
%>


通过蓝色部分的设置将index这个页面缓存下来，3600秒内，直接从缓存取数据，不在对index()运算。
1、2删除缓存
控制器或者模型层中执行pageCache::delete($classmethod,$query)即可

完整例子：
2、删除site/index页的缓存数据

pageCache::delete(  array('site','index')  );


2、删除site/index?id=1页的缓存数据

pageCache::delete(  array('site','index')  ,  array('id'=>1)  );
2、缓存配置
可以将缓存的页面存到内存或者文件中，支持memcache（最大存入数据2M到内存），redis(最大存入数据2G到内存)，或存到本地文件中，用哪种存取在Config/global.config.php下配置：

//页面缓存类型

define('PAGE_CACHE_TYPE','file'); 

不配置则默认使用file作为页面缓存。

六、视图说明
标准php输出，按照控制器data()传递进入的变量进行处理。

视图方法
run($class,$method='index)	同上变量定义在array传入	按照路由规则输出模版中的URL连接地址
get_router($class,$method='index')	$filename 模版文件名	按照路由规则生成模版中的URL连接地址

七、系统其他方法

参考core/util下的方法包含：cookie处理，文件上传，图片处理，日志记录，效率测试等。

八、系统其他方法
自定义函数方法统一写在 core/function/function.php中

