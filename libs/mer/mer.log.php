<?php
class Log{
    //Log::write('message', 'file');
    static function write($mess="", $name="main"){
        if(strlen(trim($mess)) < 2){
            return fasle;
        }
        if(preg_match("/^([_a-z0-9A-Z-_@.!#$]+)$/i", $name, $matches)){
            $file_path = $_SERVER['DOCUMENT_ROOT'].'/logs/'.$name;
//			$text = htmlspecialchars($mess)."\r\n";
            $text = date('Y-m-d H:i:s')." - ".$mess;
            $handle = fopen($file_path, "a");
            @flock ($handle, LOCK_EX);
            fwrite ($handle, $text);
            fwrite ($handle, "\r\n\r\n==============================================================\r\n\r\n");
            @flock ($handle, LOCK_UN);
            fclose($handle);
            return true;
        } else {
            return false;
        }
    }
}
/****************************************/
function debug_write_query( $query ){
//	if (defined("USE_LOG") && USE_LOG == 1){
//		if ( isset($_SESSION["user_id"]) )
//			$client = $_SESSION["user_id"];
//		else
//			$client = '00000000';
//		Log::write($query, $client.'-'.date('Ymd').'-QUERY.log');
//	}
	if (defined("USE_LOG") && USE_LOG == 1){
        Log::write($query, date('Ymd').'-QUERY.log');
    }
}

?>