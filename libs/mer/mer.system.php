<?php
/****************************************/
class Session{
	static function resetValues(){
		foreach ($_SESSION as $key => $item){
			unset($_SESSION[$key]);
		}		
	}
}
/****************************************/
class JSON{
	static function DecodeA($value, $assoc = false){
    $value = str_replace(array("\n","\r", chr(92)),"",$value);
    $value = preg_replace('/([{,])(\s*)([^"]+?)\s*:/','$1"$3":',$value);
    return json_decode($value, $assoc);
	}
/*--------------------------------------*/
	static function DecodeB($value){  
		$comment = false; 
		$out = "$x=";
		for ($i=0; $i < strlen($json); $i++){
			if (!$comment){ 
				if ($json[$i] == "{")
					$out .= " array(";
				else if ($json[$i] == "}")
					$out .= ")"; 
				else if ($json[$i] == ":")
					$out .= "=>"; 
				else
					$out .= $json[$i];            
			} else 
			$out .= $json[$i];
			if ($json[$i] == '"')
				$comment = !$comment; 
		}
		eval($out . ';'); 
		return $x; 
	}
/*--------------------------------------*/
	static function encode( $value ){
		$charset = "utf-9";
		if (defined("DEFAULT_CHARSET"))
			$charset = DEFAULT_CHARSET;
		header("Content-type: text/json; charset=".$charset);
		return json_encode(CP1251::fix($value));
	}
/*--------------------------------------*/
	static function createError( $type, $code, $text ){
		return json_encode(array($type => $text, 'code'=>$code));
	}
/*--------------------------------------*/
	static function createMessage( $code, $text, $desc = "" ){
		return json_encode(array("CODE" => $code, "MSG" => $text, "DESCRIPTION" => $desc));
	}	
/*--------------------------------------*/
	static function fixCurr($var, $charset){
    if (is_array($var)) {
			$new = array();
			foreach ($var as $k => $v) {
				$new[JSON::fixCurr($k)] = JSON::fixCurr($v);
			}
			$var = $new;
    } elseif (is_object($var)) {
			$vars = get_object_vars($var);
			foreach ($vars as $m => $v) {
				$var->$m = JSON::fixCurr($v);
			}
    } elseif (is_string($var)) {
       $var = iconv('cp1251', $charset, $var);
    }
    return $var;
	}	
} 
/****************************************/
class XML{
	static function toArray($xml){
		try{
				$fils = 0; 
				$tab = false; 
				$array = array(); 
				foreach($xml->children() as $key => $value){    
						$child = XML::toArray($value); 
						foreach($value->attributes() as $ak=>$av){
								$child[$ak] = (string)$av; 
						}
						if($tab==false && in_array($key,array_keys($array))){ 
								$tmp = $array[$key]; 
								$array[$key] = NULL; 
								$array[$key][] = $tmp; 
								$array[$key][] = $child; 
								$tab = true; 
						} elseif($tab == true) { 
								$array[$key][] = $child; 
						} else { 
								$array[$key] = $child; 
						} 
						$fils++;        
					} 
				if($fils==0) { 
						return (string)$xml; 
				} 
				return $array; 
			} catch( Exception $ex){
				return array();
			}
	}
}
/****************************************/
class Message{
	static function create($code, $text, $desc){
		return array("CODE" => $code, "MSG" => $text, "DESCRIPTION" => $desc);
	}
}
/****************************************/
class CP1251{
	static function toUTF8($s){ 
		if ((mb_detect_encoding($s,'UTF-8,CP1251')) == "WINDOWS-1251"){ 
			$c209 = chr(209); $c208 = chr(208); $c129 = chr(129); 
		for($i=0; $i<strlen($s); $i++){
			$c=ord($s[$i]); 
			if ($c>=192 and $c<=239)
				$t.=$c208.chr($c-48); 
			elseif ($c>239)
				$t.=$c209.chr($c-112);
			elseif ($c==184)
				$t.=$c209.$c209; 
			elseif ($c==168)
				$t.=$c208.$c129; 
			else $t.=$s[$i]; 
		} 
		return $t; 
    } else { 
	    return $s; 
    }
	}
/*--------------------------------------*/
	static function fix($var, $charset){
    if (is_array($var)) {
			$new = array();
			foreach ($var as $k => $v) {
				$new[CP1251::fix($k)] = CP1251::fix($v);
			}
			$var = $new;
    } elseif (is_object($var)) {
			$vars = get_object_vars($var);
			foreach ($vars as $m => $v) {
				$var->$m = CP1251::fix($v);
			}
    } elseif (is_string($var)) {
       $var = iconv('cp1251', $charset, $var);
    }
    return $var;
	}
}
/****************************************/
class UTF8{
	static function toWIN1251($s){ 
		if ((mb_detect_encoding($s,'UTF-8,CP1251')) == "UTF-8") { 
			for ($c=0;$c<strlen($s);$c++) { 
				$i=ord($s[$c]); 
				if ($i<=127)
					$out.=$s[$c]; 
				if ($byte2) { 
					$new_c2=($c1&3)*64+($i&63); 
					$new_c1=($c1>>2)&5; 
					$new_i=$new_c1*256+$new_c2; 
					if ($new_i==1025) { 
						$out_i=168; 
					} else { 
						if ($new_i==1105) { 
							$out_i=184; 
						} else { 
							$out_i=$new_i-848; 
						} 
					} 
					$out.=chr($out_i); 
					$byte2=false; 
				} 
						if (($i>>5)==6) { 
					$c1=$i; 
					$byte2=true; 
				} 
			} 
				return $out; 
		} else {
			return $s; 
		} 
	}
}
/****************************************/
class DateValue{
	static function toSQLFormat($value){ 
		$ret = substr($value, -4, 4).substr($value, 3, 2).substr($value, 0, 2);
		return $ret;
	}
/*--------------------------------------*/
	static function createDefaultDate(){
		return date_create_from_format("d.m.Y", "01.01.1970");
	}
/*--------------------------------------*/
	static function toDefaultDateA($value, $defValue){
		$ret = date_create_from_format("d.m.Y", "01.01.1970"); 
		try{
			$ret = date_create_from_format("d.m.Y", $value); 
		} catch (Exception $e){
			$ret = $defValue;
		}
		return $ret;
	}
/*--------------------------------------*/
	static function toDefaultDateB($value, $defValue){
		if (isset($value) && $value != ""){
			$ret = date_create_from_format("d.m.Y", "01.01.1970"); 
			try{
				$ret = date_create_from_format("d.m.Y", $value); 
			} catch (Exception $e){
				$ret = date_create_from_format("d.m.Y", "01.01.1970");
			}
			$ret = $ret->format('Ymd'); //date('Ymd', $ret);
		} else
			$ret = "";
		return $ret;	
	}
}
/****************************************/
class IntValue{
	static function fromBool($value){
		if (strtoupper($value) == "TRUE" || strtoupper($value) == "1")
			return 1;
		else
			return 0;
	}
/*--------------------------------------*/
	static function create($value, $defValue){
		$ret = 0;
		try{
			$ret = intval($value);
		} catch (Exception $e){
			$ret = $defValue;
		}
		return $ret;
	}
}
/****************************************/


/*--------------------------------------*/
/* 			TODO				*/
/*--------------------------------------*/

function buildTree(array $list) {
	$ret = array();
	$nodes = array();
	foreach ($list as $id => $node) {
		if (!$node['PARENT']) {
			$ret[] = &$list[$id];
			$nodes[$node['dataIndex']] = &$list[$id];
		} else {
			$nodes[$node['PARENT']]["nodes"][] = &$list[$id];
		}
	}
  return $ret;
}
/****************************************/
function fixTreeB(array $values, $level = 0) {
	$ret = array();
	$newlevel = $level + 1;
	foreach ($values as $id => $item) {
		$nodes = fixTreeB($item["nodes"]);
		$data = array("dataIndex" => $item["dataIndex"], "PARENT" => $item["PARENT"], "text" => $item["text"], "level" => $newlevel, "chields" => count($nodes));
		if (count($nodes) > 0){
			$data["nodes"] = fixTreeB($item["nodes"], $newlevel);
		}
		
		$ret[] = $data;
	}
	return $ret;
}
/****************************************/
function buildTreeB(array $dataset) {
    $tree = array();
    $references = array();
    foreach ($dataset as $id => &$node) {
        $references[$node['dataIndex']] = &$node;
        $node['nodes'] = array();
        if (strlen($node['PARENT']) == 0) {
            $tree[$node['dataIndex']] = &$node;
        } else {
            $references[$node['PARENT']]['nodes'][$node['dataIndex']] = &$node;
        }
    }
    return fixTreeB($tree);
}
/****************************************/
function getParamsValue( $name, $params ){
	$farray = array();
	$farray = array_filter($params, function ($element) use ($name) {
		return (strtoupper($element["name"]) == strtoupper($name));
	});
	if (count($farray) >= 1){
		$keys = array_keys($farray);
		return $farray[$keys[0]]["value"];
	} else {
		return "";
	}
}
/****************************************/
function getGUID(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    }else{
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12)
            .chr(125);// "}"
        return $uuid;
    }
}
/****************************************/
function getListDataTranformA($values, $sender, $type = 0){
	$data = array();
		if (true){
		foreach ($values as $key1 => $value1){
			if (array_key_exists("0", $value1) && count($value1[0]) > 1){
				foreach ($value1 as $key2 => $value2){
					if ($sender == "collection"){
						$name = iconv(DEFAULT_CHARSET, 'cp1251', $value2["field_1"]);
						$desc = iconv(DEFAULT_CHARSET, 'cp1251', $value2["field_4"]." - ".$value2["field_2"]);
					} else if ($sender == "stock" || $sender == "incomeplace"){
						$name = iconv(DEFAULT_CHARSET, 'cp1251', $value2["field_1"]);
						$desc = iconv(DEFAULT_CHARSET, 'cp1251', $value2["field_1"]." - ".$value2["field_2"]);
					} else if ($sender == "article"){
						$name = iconv(DEFAULT_CHARSET, 'cp1251', $value2["field_2"]);
						$desc = iconv(DEFAULT_CHARSET, 'cp1251', $value2["field_2"]." - ".$value2["field_3"]);
					} else if ($sender == "product"){
						$name = iconv(DEFAULT_CHARSET, 'cp1251', $value2["field_1"]);
						$desc = iconv(DEFAULT_CHARSET, 'cp1251', $value2["field_2"]." - ".$value2["field_3"]);
					} else if ($sender == "tmgroup"){
						$name = iconv(DEFAULT_CHARSET, 'cp1251', $value2["field_3"]);
						if ((!isset($value2["field_1"]) && !isset($value2["field_2"])) || ($value2["field_1"] == "" && $value2["field_2"]))
							$desc = iconv(DEFAULT_CHARSET, 'cp1251', $value2["field_3"]." - ".$value2["field_4"]);
						else
							$desc = iconv(DEFAULT_CHARSET, 'cp1251', $value2["field_1"]." - ".$value2["field_2"]." -> ".$value2["field_3"]." - ".$value2["field_4"]);
					} else if ($sender == "producer-child"){
						$name = iconv(DEFAULT_CHARSET, 'cp1251', $value2["field_3"]);
						$desc = iconv(DEFAULT_CHARSET, 'cp1251', $value2["field_3"]." - ".$value2["field_4"]);
					} else {
						$name = iconv(DEFAULT_CHARSET, 'cp1251', $value2["field_1"]);
						$desc = iconv(DEFAULT_CHARSET, 'cp1251', $value2["field_2"]);
					}
					$data[] = array( "ID" => $name, "NAME" => $name, "DESC" => $desc );
				}
			} else{
				if ($sender == "collection"){
					$name = iconv(DEFAULT_CHARSET, 'cp1251', $value1["field_1"]);
					$desc = iconv(DEFAULT_CHARSET, 'cp1251', $value1["field_4"]." - ".$value1["field_2"]);
				} else if (($sender == "stock" && $type == 1) || $sender == "stock" || $sender == "incomeplace"){
					$name = iconv(DEFAULT_CHARSET, 'cp1251', $value1["field_1"]);
					$desc = iconv(DEFAULT_CHARSET, 'cp1251', $value1["field_1"]." - ".$value1["field_2"]);
				} else if ($sender == "article"){
					$name = iconv(DEFAULT_CHARSET, 'cp1251', $value1["field_2"]);
					$desc = iconv(DEFAULT_CHARSET, 'cp1251', $value1["field_2"]." - ".$value1["field_3"]);
				} else if ($sender == "product"){
					$name = iconv(DEFAULT_CHARSET, 'cp1251', $value1["field_1"]);
					$desc = iconv(DEFAULT_CHARSET, 'cp1251', $value1["field_2"]." - ".$value1["field_3"]);
				} else if ($sender == "tmgroup"){
						$name = iconv(DEFAULT_CHARSET, 'cp1251', $value1["field_3"]);
						if ((!isset($value1["field_1"]) && !isset($value1["field_2"])) || ($value1["field_1"] == "" && $value1["field_2"] == ""))
							$desc = iconv(DEFAULT_CHARSET, 'cp1251', $value1["field_3"]." - ".$value1["field_4"]);
						else
							$desc = iconv(DEFAULT_CHARSET, 'cp1251', $value1["field_1"]." - ".$value1["field_2"]." -> ".$value1["field_3"]." - ".$value1["field_4"]);
				} else if ($sender == "producer-child"){
					$name = iconv(DEFAULT_CHARSET, 'cp1251', $value1["field_3"]);
					$desc = iconv(DEFAULT_CHARSET, 'cp1251', $value1["field_3"]." - ".$value1["field_4"]);
				} else {
					$name = iconv(DEFAULT_CHARSET, 'cp1251', $value1["field_1"]);
					$desc = iconv(DEFAULT_CHARSET, 'cp1251', $value1["field_2"]);
				}
				$data[] = array( "ID" => $name, "NAME" => $name, "DESC" => $desc );

			}
		}
	}
	return $data;
}
/**************************************/
function getListDataTranformB($values){
	$data = array();
	if (count($values[0]) > 1){
		foreach ($values as $key1 => $value){
				$data[] = $value;
		}
	}else{
		$data[] = $values;
	}
	return $data;
}
/**************************************/
function getTreeDataTranformA($values, $sender){
	$data = array();
	foreach ($values as $key1 => $value1){
		if (count($value1[0]) > 1){
		foreach ($value1 as $key2 => $value2){
				$code = iconv(DEFAULT_CHARSET, 'cp1251', $value2["field_3"]);
				$parent = iconv(DEFAULT_CHARSET, 'cp1251', $value2["field_1"]);
				$name = iconv(DEFAULT_CHARSET, 'cp1251', $value2["field_4"]);
				if (strlen($name) == 0)
					$name = $value2["field_4"];
				if ($sender == "collection")
					$data[] = array( "dataIndex" => $code, "PARENT" => $parent, "text" => $name, "name" => $name );
				else
					$data[] = array( "dataIndex" => $code, "PARENT" => $parent, "text" => $code." - ".$name, "name" => $name );
			}
		} else{
				$code = iconv(DEFAULT_CHARSET, 'cp1251', $value1["field_3"]);
				$parent = iconv(DEFAULT_CHARSET, 'cp1251', $value1["field_1"]);
				$name = iconv(DEFAULT_CHARSET, 'cp1251', $value1["field_4"]);
				if (strlen($name) == 0)
					$name = $value1["field_4"];
				if ($sender == "collection")
					$data[] = array( "dataIndex" => $code, "PARENT" => $parent, "text" => $name, "name" => $name );
				else
					$data[] = array( "dataIndex" => $code, "PARENT" => $parent, "text" => $code." - ".$name, "name" => $name );
		}
	}
	return $data;
}
/**************************************/
?>