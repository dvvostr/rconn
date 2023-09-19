<?php

define('DB_DEBUG_USE', 1);
define('USE_LOG', 1);

class Settings {
    static function getActiveConnection($name) {
      $str = current(array_filter(CONNECTION_LIST, function($v, $k) use ($name) {
        return $v["name"] == $name;
      }, ARRAY_FILTER_USE_BOTH))["value"];
      return (substr($str, strlen($str) - 1, 1) != "/") ? $str."/" : $str;
    }
}
define('CONNECTION_LIST', array(
	array("name"=>"server1", "value"=>"https://server1-data.com:8888/point1"),
	array("name"=>"server2", "value"=>"https://server1-data.com:8888/point2"),
	array("name"=>"server3", "value"=>"https://server1-data.com:8888/point3"),
	array("name"=>"server4", "value"=>"https://server1-data.com:8888/point4"),
	array("name"=>"server5", "value"=>"https://server1-data.com:8888/point5"),
	)
);	

?>
