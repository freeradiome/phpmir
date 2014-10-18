<?php
/**
 * 百度式分页
 * 修改历史
 * 日期 作者 修改内容
 * 2010 zeng444@163.com 
 * 
 */
class Page{

	//起始记录
	public $start;

	//当前页
	public $absolute_page;

	//总页数
	public $pagecount;

	//总记录数
	public $count;

	//分页大小
	public  $limit;

	//页QUERY
	private $pq;

	//显示多少页
	public $pageViewCount =10;

	//下一页
	public $next_page_string ;

	//上一页
	public $pre_page_string ;

	//
	public $ajax_fun='page';



	/**
	 * 初始化方法
	 *
	 * @param Int $count //记录总数
	 * @param Int $limit //每页数
	 */
	public function __construct($count,$limit,$query='page'){

		$this->pq = $query;
		$this->count = $count;
		$this->limit = $limit;
		$this->pagecount = ceil($count/$limit); //总页数
		$this->pagecount = (!$this->pagecount)?1:$this->pagecount;
		if(!isset($_GET[$this->pq])) $_GET[$this->pq]=1;
		$this->absolute_page = intval( $_GET[$this->pq] ); //当前页
		$this->absolute_page = ($this->absolute_page<1 )?1:$this->absolute_page ;
		$this->absolute_page = ($this->absolute_page>$this->pagecount )?$this->pagecount:$this->absolute_page;
		$this->pageViewCount =$this->pageViewCount-1;
		$this->start =($this->absolute_page-1)*$limit;

	}

	private function getPre(){
		$page = intval($this->absolute_page - 1);
		$page = ($page<1)?1:$page;
		return $page;
	}

	private function getNext(){
		$page = intval($this->absolute_page + 1);
		$page = ($page>$this->pagecount)?$this->pagecount:$page;
		return $page;
	}


	public function  pageRevMini(){
		if($this->count>=$this->limit){
			$first_page = ' <a href="'.$this->buildUrl(1).'">首页</a> ';
			$end_page =  ' <a href="'.$this->buildUrl($this->pagecount).'">尾页</a> ';
			$this->next_page_string = ' <a href="'.$this->buildUrl($this->getNext()).'">下一页</a>';
			$this->pre_page_string = ' <a href="'.$this->buildUrl($this->getPre()).'">上一页</a>';
		}
		$string .=$first_page.$this->pre_page_string.$this->next_page_string.$end_page ;
		return ($string);
	}

	public function pageRev(){
		if($this->count>=$this->limit){
			$string = '<div class="cleft">目前共有'.$this->count.'条记录</div>';
//			$string = '';
			$first_page = ' <a href="'.$this->buildUrl(1).'">首页</a> ';
			$end_page =  ' <a href="'.$this->buildUrl($this->pagecount).'">尾页</a> ';
			$this->next_page_string = ' <a href="'.$this->buildUrl($this->getNext()).'">下一页</a>';
			$this->pre_page_string = ' <a href="'.$this->buildUrl($this->getPre()).'">上一页</a>';
			$str='';
			$parm = intval($this->pageViewCount/2);
			$for_start = $this->absolute_page-$parm;
			$for_start = ($for_start<1)?1:$for_start;
			$for_end = $for_start+$this->pageViewCount;
			$for_end = ($for_end>$this->pagecount)?$this->pagecount:$for_end;
			for ($i=$for_start;$i<=$for_end;$i++){
				if($i==$this->absolute_page){
					$str .= '<span class="cpage">'.$i.'</span>';
				}else{
					$str .= ' <a href="'.$this->buildUrl($i).'" >'.$i.'</a> ';
				}
			}
			$string .=$first_page.$this->pre_page_string.$str.$this->next_page_string.$end_page ;
			return ($string);
		}
	}

	public function ajax_pageRev(){
		if($this->count>=$this->limit){
			$string = '';
			//$string .= '目前共有'.$this->count.'条记录';
			$first_page = ' <a onclick="'.$this->buildUrl(1,true).'">首页</a> ';
			$end_page =  ' <a onclick="'.$this->buildUrl($this->pagecount,true).'">尾页</a> ';
			$this->next_page_string = ' <a onclick="'.$this->buildUrl($this->getNext(),true).'">下一页</a>';
			$this->pre_page_string = ' <a onclick="'.$this->buildUrl($this->getPre(),true).'">上一页</a>';
			$str='';
			$parm = intval($this->pageViewCount/2);
			$for_start = $this->absolute_page-$parm;
			$for_start = ($for_start<1)?1:$for_start;
			$for_end = $for_start+$this->pageViewCount;
			$for_end = ($for_end>$this->pagecount)?$this->pagecount:$for_end;
			for ($i=$for_start;$i<=$for_end;$i++){
				if($i==$this->absolute_page){
					$str .= '<span class="cpage">'.$i.'</span>';
				}else{
					$str .= ' <a onclick="'.$this->buildUrl($i,true).'" >'.$i.'</a> ';
				}
			}
			$string .= $first_page.$this->pre_page_string.$str.$this->next_page_string.$end_page ;
			return ($string);
		}
	}

	public function pageRevNew(){
		return '';
	}

	private  function buildUrl($page,$ajax=false){
		if($ajax){
			return $this->ajax_fun."($page)";
			//return '#';
		}else{
			$str_query = strtolower($_SERVER['QUERY_STRING']);
			parse_str($str_query,$parm);
			$parm[$this->pq]= $page;
			return '?'.http_build_query($parm);
		}
	}
	//只有页数的分页
	public function mainPage(){
		if($this->count>=$this->limit){
			$str='';
			$parm = intval($this->pageViewCount/2);
			$for_start = $this->absolute_page-$parm;
			$for_start = ($for_start<1)?1:$for_start;
			$for_end = $for_start+$this->pageViewCount;
			$for_end = ($for_end>$this->pagecount)?$this->pagecount:$for_end;
			for ($i=$for_start;$i<=$for_end;$i++){
				if($i==$this->absolute_page){
					$str .= '<span class="cpage">'.$i.'</span>';
				}else{
					$str .= ' <a href="'.$this->buildUrl($i).'" >'.$i.'</a> ';
				}
			}
			$string .=$str;
			return ($string);
		}
	}
}
?>