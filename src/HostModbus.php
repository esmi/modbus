<?php
namespace Esmi\modbus;

use Esmi\DB\DB;
use Esmi\modbus\Modbus;
use PHPModbus\PhpType;
use \DateTime;

class HostModbus extends Modbus{

  private $debugLevel = 1;
  function __construct($info) {
    //forms:
    // a.  $modbus = new HostModbus( (new DevHost($db)->deviceId($modbusId)));
    //
    // b.
    //  $modbusInfo = [
    //   'dev' => '/dev/ttyS2', // /dev/ttyS1, TCP,UDP
    //   'baud' => 9600,
    //   'parity' => 'none',
    //   'char' => 8,
    //   'sbit' => 1,
    //   'flow' => 'none',
    //   'timeout' => 5000,  //current not support on ttySx, stty -F /dev/ttyS0 min 0 time 10
    //   'debug' => 1,
    //   'isTR' => 1,       // CC2050
    //   'pinTR' => 1,      // CC2050
    //   'is2R' => 1,       // CC2050, when echo cannot diable.
    //   'isSimulator' => 1,
    //   //'options' => 'N,8,1' // current not support.
    //  ];
    //  $modbus = new HostModbus( $modbusInfo);

    // print_r($info);

    parent::__construct($info);

    // When support for CC2050,
    // Table: "devhost" must set flowing fields:
    // "isTR", "pinTR", "is2R", "isSimulator"
    if (in_array(strtoupper($this->dev), ['TCP',"UDP"])) {
      $this->isTR = 0;
      $this->pinTR = 0;
      $this->is2R = 0;
      // $this->modbus->setTimeout(2);
      // $this->modbus->setSocketTimeout($read_timeout_sec, $write_timeout_sec);
      $this->modbus->setSocketTimeout(0.3, 2);
      $this->modbus->port = $info['port'];
      //echo "modbus->port: ", $this->modbus->port , "\n";
      $this->modbus->connect();
      // var_dump($this->modbus->timeout_sec);
    }
    else {
      $this->isTR = $info['isTR'];
      $this->pinTR = $info['pinTR'];
      $this->is2R = $info['is2R'];
    }


    //當isTR=1時, 此欄位判斷是否執行於模擬器,
    //並影響HostModbus的setTRSend(), setTRRead(), 及sendQuery
    //當執行於CC2050時, 此欄位需設定為 0.
    $this->isSimulator = $info['isSimulator'];

    $this->info = $info;
  }
  // function hostDeviceId($id) {
  //
  // }
  function setTRSend() {
    if ($this->isSimulator) {
      if ($this->debug) echo ("set TR send: gpio write " . $this->pinTR . " 1\r\n");
    }
    else {
      // echo ("set TR send: gpio write " . $this->pinTR . " 1\r\n");
      system("gpio write " . $this->pinTR . " 1");
    }
  }
  function setTRRead() {
    if ($this->isSimulator) {
      if ($this->debug) echo ("set TR read: gpio write " . $this->pinTR . " 0\r\n");
    }
    else {
      // echo ("set TR read: gpio write " . $this->pinTR . " 0\r\n");
      system("gpio write " . $this->pinTR . " 0");
    }
  }
  function sendQuery($slaveId, $functionCode, $reference, $regCountOrData, $response = true) {
    if ( $this->isTR) {
      //

      $this->setTRSend();
      $d = parent::sendQuery($slaveId, $functionCode, $reference, $regCountOrData, false );
      if ( $response ) {
        $d = $this->getResponse();
      }
    }
    else {

      //$this->modbus->setSocketTimeout(0.5, 1);
      //$this->modbus->setSocketTimeout($read_timeout_sec, $write_timeout_sec);
      if ($this->debug && $this->debugLevel > 2 )  {
        print "Esmi/modbus [HostModbus read/write timeout]: {$this->modbus->socket_read_timeout_sec}, {$this->modbus->socket_write_timeout_sec}\n";
      }

      $d = parent::sendQuery($slaveId, $functionCode, $reference, $regCountOrData, $response);
    }
    return $d;
  }
  public function getResponse($raw=false,$offsetl=0,$offsetr=0) {
    $data = null;
    if ( $this->isTR) {
      $this->setTRRead();
      if ($this->is2R) {
        if (!$this->isSimulator) {
          $t = $this->modbus->getResponse();
        }
      }
      // $recdata = $this->modbus->getResponse();
      // foreach ($recdata as $key => $value) {
      //   $data[$key] = hexdec($value);
      // }
    }
    $d = $this->trGetResponse($raw=false,$offsetl=0,$offsetr=0);
    return $d;
  }
  public function trGetResponse($raw=false,$offsetl=0,$offsetr=0)
  {
    $data = null;
    if ($this->isSerial) {

      //echo "this is a serial ............\r\n";
      $recdata = $this->modbus->getResponse($raw,$offsetl,$offsetr);
      //var_dump($recdata);
      if ($recdata) {
        foreach ($recdata as $key => $value) {
          $data[$key] = hexdec($value);
        }
      }
    }
    return $data;

  }
  public function close() {
    if (in_array(strtoupper($this->dev), ['TCP',"UDP"])) {
      echo "hostModbus: close TCP connection!\n";
      $this->modbus->disconnect();
    }

  }

}
