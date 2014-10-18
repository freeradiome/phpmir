<?php 
class socket{
	
	//socket连接
	public $_socket;
	
	//资源对象
	public $_resource;

	//服务器地�?
	public $_server_ip;
	
	//服务器端�?
	public $_server_port;
	
	function __construct($params=array()){
		
		if(  isset( $params['ip'] ) ){
			$this->_server_ip = $params['ip'];
		}
		if(  isset( $params['port'] ) ){
			$this->_server_port = $params['port'];
		}
		
		$this->_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		
		if ($this->_socket < 0) {
  			throw_exception( "socket_create() failed: reason: " . socket_strerror($this->_socket)  );
		}
		
		$this->_resource = socket_connect($this->_socket, $this->_server_ip, $this->_server_port);
	
		if ($this->_resource < 0) {    
			throw_exception( "socket_connect() failed.\nReason: ($this->_resource) " . socket_strerror($this->_resource) );
		}	
	}
	
	/**
	 * 返回�?个参�?
	 *
	 * @param string $string
	 */
	private function send($string){
		
		if(!socket_write($this->_socket, $string, strlen($string))) {  
		  throw_exception( "socket_write() failed: reason: " . socket_strerror($this->_socket) );
		}
	}
	
	/**
	 * 返回�?个参�?
	 *
	 * @param unknown_type $string
	 */
	private function response(){
		return socket_read($this->_socket, 8192 );
	}
	
	function get($string){
		if($string) $this->send($string);
		$t = '';
		while($out = $this->response() ){
            $t = $t.$out;
		}
		return $t;
	}
	
	function __destruct(){
		socket_close($this->_socket);
	}
	
	
}


?>