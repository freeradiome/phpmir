<?php 
/**
 * 文本分页
 * 修改历史
 * 日期 作者 修改内容
 * 2012 zeng444@163.com 
 * 
 */
class cPage{

	//分页tag
	public $_pagetag='_my7g_page_break_tag_';

	//内容
	public   $_content='' ;
	
	//分页符
	public $_pagequery = 'cpg';

	//总页数
	protected $_count_page = 1;

	//当前页
	protected $_absolute_page = 1;

	//内容数组
	private  $_content_array = array() ;

	function __construct($content,$pagetag='',$pagequery=''){
		
		$this->_content = ($content ) ?$content:$this->_content;
		
		if(strpos($this->_content,$this->_pagetag )===false){
			
			$this->_content_array[0] = $this->_content;
		}else{
			$this->_content_array = explode( $this->_pagetag ,$content );
			$this->_count_page = sizeof( $this->_content_array );
			if( $pagequery ) $this->_pagequery =$pagequery;
			if( $pagetag ) $this->_pagetag = $pagetag;
			if(!isset($_GET[$this->_pagequery])) $_GET[$this->_pagequery]=1;
			$this->_absolute_page  = intval( $_GET[$this->_pagequery] ); //当前页
			$this->_absolute_page  = ($this->_absolute_page <1 )?1:$this->_absolute_page ;
			$this->_absolute_page  = ($this->_absolute_page >$this->_count_page )?$this->_count_page:$this->_absolute_page ;
		}
	}

	/**
	 * 输出分页文
	 *
	 * @return string
	 */
	public function content(){
		return $this->_content_array[$this->_absolute_page-1];
	}
	/**
	 * 创建分页html
	 *
	 * @return string
	 */
	public function pageRev(){
		if($this->_count_page>1){
			$html='';
			for($i=1;$i<=$this->_count_page;$i++){
				if($this->_absolute_page==$i){
					$html .= "<span>$i</span>";	
				}else{
					$html .= '<a href="'.$this->buildUrl($i).'">'.$i.'</a>';
				}
			}
		}
		return $html;
	}
	
	/**
	 * 生成连接地址
	 *
	 * @param int $page
	 * @return string
	 */
	private  function buildUrl($page){
		$str_query = strtolower($_SERVER['QUERY_STRING']);
		parse_str($str_query,$parm);
		$parm[$this->_pagequery]= $page;
		return '?'.http_build_query($parm);
	}

}