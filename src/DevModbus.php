<?php
namespace Esmi\modbus;

class DevModbus {
  protected $modbus = null;
  protected $debug = false;

  function __construct( $modbus=null, $info=null) {

    $this->debug = false;
    $this->slaveId = null;

    if ($info) {
      switch (gettype($info)) {
        case 'array': {
          $this->slaveId = $info['slaveId'];
          break;
        }
        case 'integer': {
          $this->slaveId = $info;
          break;
        }
      }
    }
    if ($modbus) {
      $this->setModbus($modbus);
    }
  }

  public function setModbus($modbus) {
    $this->modbus = $modbus;
    $this->debug = $modbus->modbus->debug;
  }
  public function setSlaveId($id) {
    $this->slaveId = $id;
  }
  public function wInt($d){

  }
  public function dwInt($d): integer {

    $f = [];
    $f = array_merge($f, array_slice($d,2,2));
    $f = array_merge($f, array_slice($d,0,2));
    $lux =  PhpType::bytes2signedInt($f) ;
  }
  public function qwInt($d): integer {

  }
  //swap "word" high, lower byte.
  protected function swapWordChar($d) {
    $byte = count($d);
    for ($i = 0 ; $i < $byte ; $i+=2) {
      // swap:
      $tmp = $d[$i];
      $d[$i] = $d[($i+1)];
      $d[($i+1)] = $tmp;
    }
    return $d;
  }
  public static function bytes2bitArray($d) {

    $s = self::bytes2bitString($d);
    return self::bitString2bitArray($s);
  }
  public static function bytes2bitString($d, $formated = false, $reverse = true) {
    // $reverse == true, 低位元會在字串的前端, 則呼叫 "bitString2bitArray()", 低位元會在陣列的低的 offset.
    $s = "";
    $bytes = count($d);
    for ($i = 0 ; $i < $bytes ; $i++) {
      $bin = decbin($d[$i]);
      $bin = str_pad($bin, 8, 0, STR_PAD_LEFT);
      $s .= (($formated ? "\\b": "") . ($reverse ? strrev($bin) : $bin));
    }
    return $s;
  }
  public static function bitString2bitArray($s) {
    //transfer bitString to bitArray,ex: "1101" => [1,1,0,1]
    //$s is 只能包含"0"與"1"的 bitString;
    $a = str_split($s);
    foreach ($a as $k => $v) {
      $a[$k] = $v == "1" ? 1 : 0;
    }
    return $a;
  }
  public static function bitArray2String($d, $ishex=false, $istag=true, $reverse=true, $bits=8) {
    // $ishex == false, return "\b00001100"
    // $ishex == true, return "\x0c";
    $s = "";
    for ($i =0; $i < count($d); $i = ($i + $bits)) {
      $bytes = array_slice($d, $i, $bits);
      $bin = implode($bytes);
      if ($ishex) {
        $hex = dechex(bindec( ($reverse) ? strrev($bin) : ($bin)));
        $hex = str_pad($hex, 2, 0, STR_PAD_LEFT);
        $s .= ($istag ? "\\x" : "") . $hex;
      }
      else {
        $s .= ($istag ? "\\b" : "") . ($reverse ? strrev($bin) : $bin);
      }
    }
    return $s;
  }
  static function bytes2hexString ($d)	{
    $s = "";
    foreach ($d as $k => $v) {
      $h = dechex($v);
      $h = str_pad($h,2,"0",STR_PAD_LEFT);
      $s = $s . "\\x" . $h;
    }
    return $s;
  }

}
