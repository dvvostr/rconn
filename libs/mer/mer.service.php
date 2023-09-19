<?php

/***************************************************/
class Router{
	public $profile;
	public $path;
	public $query;
	public $method;
	public $params;
	public function __construct( $options ){
		$this->method = $options["method"];
		$this->params = $options["params"];
		$req = $options["get"]["req"];
		$arr = explode("/", substr($req, 0, 1) == "/" ? substr($req, 1, strlen($req)) : $req);
		$this->profile = "";
		$this->path = "";
		$this->query = "";
		foreach($arr as $key=>$value) {
			if ($key == 0) {
				$this->profile = $value;
			} else if (strlen($this->path) == 0) {
				$this->path = $value;
			} else {
				$this->path = $this->path."/".$value;
			}
		}
		foreach($options["get"] as $key=>$value) {
			if ($key != "req") {
				$this->query = $this->query.((strlen($this->query) == 0) ? "" : "&").$key."=".$value;
			}
		}
	}
	function getURL() {
		return $this->path.((strlen($this->query) == 0) ? "" : "?".$this->query);
	}
}

/***************************************************/
class Connector{
	var $router;
	var $errcode;
	var $errtext;
/*--------------------------------------*/
	private function hexToStr($hex){
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
	}
	
/*--------------------------------------*/
	private function strlen_dec($value, $len){
		$len_data = dechex(strlen($value));
		if (strlen($value) % 2 != 0)
			$len_data = "0".$len_data;
		while (strlen($len_data) < $len*2):
			$len_data = $len_data."0";
		endwhile;
		return $this->hexToStr($len_data);
	}
/*--------------------------------------*/
	public function __construct($router){
		$this->errcode = 0;
		$this->errtext = "";
		$this->router = $router;
	}
	public function execute($url) {
		$response = "";
		$headers = array();

		try{
			if (isset($this->router) && strlen($url) > 0){
				//throw new Exception("Exception test");

				$url = $url.$this->router->getURL();
				$CURL = curl_init($url);
				curl_setopt_array($CURL, array(
					CURLOPT_URL => $url,
					CURLOPT_CUSTOMREQUEST => $this->router->method,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_HEADER => true,
					CURLOPT_CONNECTTIMEOUT => 3000,
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_SSL_VERIFYHOST => false,
					CURLOPT_HEADERFUNCTION => 
						function($curl, $header) use (&$headers) {
							$len = strlen($header);
							$header = explode(':', $header, 2);
							if (count($header) < 2) // ignore invalid headers
								return $len;
							$name = strtolower(trim($header[0]));
							$headers[$name] = trim($header[1]);
							return $len;
						}
					)
				);
				if (strtoupper($this->router->method) == "POST") {
					curl_setopt($CURL, CURLOPT_POST, true);
					curl_setopt($CURL, CURLOPT_POSTFIELDS, $this->router->params);
					curl_setopt($CURL, CURLOPT_HTTPHEADER, array("content-type:application/json"));
					
				}
				$data = curl_exec($CURL);
				if (!curl_errno($CURL)) {
					$info = curl_getinfo($CURL);
					$header_size = curl_getinfo($CURL, CURLINFO_HEADER_SIZE);
					$header = substr($data, 0, $header_size);
					$data = substr($data, $header_size);
					$code = $info["http_code"];
					switch ($code) {
						case 200:
							return JSON::createMessage($code, json_decode($data, true));
						default:
							return JSON::createMessage(-1, $headers["x-result"]);
					}
				}
				curl_close($CURL);
			}
		} catch (Exception $ex){
			if (function_exists("debug_write_query"))
				debug_write_query($response." \n*********************************************\n ".$ex->getMessage());
			return JSON::createMessage(-1, $ex->getMessage());
		}
	}
/*--------------------------------------*/
}
/***************************************************/
?>
