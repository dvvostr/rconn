<?php

class msg
{
  static function emptyMessage()
  {
    return array("code" => 0, "msg" => "", "desc" => "");
  }
  static function create($code, $message, $description)
  {
    return array("code" => $code, "msg" => $message, "desc" => $description);
  }

  static function error($message)
  {
    return array("code" => -1, "msg" => $message, "desc" => "");
  }
}