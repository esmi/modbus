<?php
namespace Esmi\modbus;

use Esmi\modbus\PhpSerialModbus;
use PHPModbus\ModbusMaster;
/**
 * Modbus: Integration PhhpSerialModbus and PHPModbus(tcp/udp)
 * example:
 *  use Esmi\modbus\Modbus;
 *  use PHPModbus\PhpType;
 *
 *  $cfg = ['dev' => 'TCP', 'ip' => 'localhost' ];
 *  $modbus = new Modbus($cfg);
 *  $data = $modbus->sendQuery(1,4,0,4);
 *  var_dump($data);
 *
 *  $cfg = ['dev' => '/dev/ttyS2'];
 *  $modbus2 = new Modbus($cfg);
 *  $data = $modbus2->sendQuery(1,4,0,3);
 *  var_dump($data);
 */
class Modbus {
  protected $dev = null;
  protected $port = null;
  protected $baud = null;
  protected $parity = null;
  protected $char = null;
  protected $sbit = null;
  protected $flow = null;

  protected $isSerial = false;
  protected $debug = false; // enable serial modbus debug flag.
  protected $serSupportedfc = [1,2,3,4,5,6];
  protected $functionCode = [ 1, 2, 3, 4, 5, 6, 15, 16, 22, 23];

  public $modbus;
  public $info;

  function __construct($a) {

      if (!array_key_exists("dev", $a)) {
        $error = 'Modbusbus construct must pass ["dev" => "IP or device name"]';
        throw new Exception($error);
      }

      $this->dev = $a['dev']; // tcp, udp, /dev/ttyS..
      if (strtoupper($this->dev) == 'TCP' || strtoupper($this->dev) == "UDP") {
        $this->ip = $a['ip'];
        $this->modbus = new ModbusMaster($this->ip, strtoupper($this->dev));
        if (isset($a['port']) && $a['port'] != 502) {
          if ($this->debug ) print "Esmi/modbus [TCP port]: not support!\n";
        }
      }
      else {
        $this->isSerial = true;
        $this->modbus = new PhpSerialModbus;
        //$this->port = (array_key_exists("port", $a)) ? $a['port'] : $this->dev; //'/dev/ttyUSB0',
        $this->port = $this->dev; //'/dev/ttyUSB0',
        $this->baud = (array_key_exists("baud", $a)) ? $a['baud'] : 115200; // 115200,
        $this->parity = (array_key_exists("parity", $a)) ? $a['parity'] : 'none'; //'none',
        $this->char = (array_key_exists("char", $a)) ? $a['char'] : 8; // $char=8,
        $this->sbit = (array_key_exists("sbit", $a)) ? $a['sbit']: 1; //$sbits=1,
        $this->flow = (array_key_exists("flow", $a)) ? $a['flow']: 'none'; // $flow='none'
        $this->modbus->deviceInit($this->port, $this->baud, $this->parity, $this->char, $this->sbit, $this->flow);
        $this->modbus->deviceOpen();

        $this->modbus->debug = (array_key_exists("debug", $a)) ? $a['debug']: false;
      }

      $this->debug = (array_key_exists("debug", $a)) ? $a['debug']: false;
      $this->info = $a;

  }
  // function __destruct ( void ) {
  //   if (!$this->isSerial) {
  //     $this->modbus->disconnect();
  //   }
  // }
  /*                                                                              Test
   *                                                                              modrssim  modrssim
   *                                              Start   type  modrssim          Status    Status
   * Function Code                                Address       named             PHPModbus PhpSerialModbus
   *
   * FC1(0x01) - Read Coils                       00001   bits  Coil Output       OK        OK
   * FC2(0x02) - Read Input Discretes             10001   bits  Digital Input     OK        OK
   * FC3(0x03) - Read Multiple Registers.         40001   bytes Holding Register  OK        OK
   * FC4(0x04) - Read Multiple Input Registers.   30001   bytes Analogue Inputs   OK        OK
   *
   * FC5(0x05) - Write Single Register.           00001   bit_  Coil Output       OK
   * FC6(0x06) - Write Single Register.           40001   byte  Holding Register  OK        OK
   * FC15(0x0F) - Write Multiple Coils            00001   bits  Coil Output       OK
   * FC16(0x10) - Write Multiple Register.        40001   bytes Holding Register  OK
   *
   * FC22(0x16) - Mask Write Register.                                            ?
   * FC23(0x17) - Read Write Registers.                                           ?
   */

  function hexAddr($relay) {
    $hexRelay = dechex($relay);
    $hexRelay = str_pad($hexRelay, 4, 0, STR_PAD_LEFT);
    return $hexRelay;

  }
  function sendQuery($slaveId, $functionCode, $reference, $regCountOrData, $response = true) {
    //PS: fc23 not support in "sendQuery()"
    //echo "reference: $reference\r\n";
    if (in_array($functionCode, $this->functionCode)) {
      $data = null;
      try {
        // serial modbus
        if ($this->isSerial) {
          //$address = 10000 + $reference;
          //$registerAddress = substr((string)$address, 1, 4 );
          $registerAddress = $this->hexAddr($reference);
           //echo "reference: " .  $reference . ", address: $address, registerAddress: $registerAddress \r\n";
          $recdata = in_array($functionCode, $this->serSupportedfc) ?
            $this->modbus->sendQuery($slaveId, $functionCode, $registerAddress, $regCountOrData, $response )
            : null;
          //var_dump($recdata);
          if ($recdata && $response) {
              //var_dump($recdata);
              //transfer hex string to decimal.
              foreach ($recdata as $key => $value) {
                $data[$key] = hexdec($value);
              }
              //var_dump($data);
              if ( $functionCode <= 2)
                $data = $this->serCoilParser($data, $regCountOrData);
          }
        }
        else {
          // tcp modbus.
          if ($functionCode != 23 ) {

            $callfunction = "fc" . $functionCode;
            if (method_exists( $this->modbus, $callfunction)) {
              // echo "======\r\n";
              $regQuantityOrData =  ($functionCode == 5 || $functionCode == 6)  ?
                (gettype($regCountOrData) == "array" ?
                  $regCountOrData : array($regCountOrData)) : $regCountOrData;
              // echo "regCountOrData: $regCountOrData\r\n";
              // echo "callfunction: $callfunction\r\n";
              // echo "slaveId: $slaveId\r\n";
              // echo "reference: $reference\r\n";
              //
              //echo "regQuantityOrData: $regQuantityOrData\r\n";
              switch ($callfunction) {
                case 'fc1':
                case 'fc2':
                case 'fc3':
                case 'fc4': {
                  // echo "quantity: $regQuantityOrData\r\n";
                  //fc1($unitId, $reference, $quantity)
                  //fc2($unitId, $reference, $quantity)
                  //fc3($unitId, $reference, $quantity)
                  //fc4($unitId, $reference, $quantity)
                  $quantity = $regQuantityOrData;
                  $data = $this->modbus->$callfunction($slaveId, $reference, $quantity);
                  // if ( $functionCode <= 2)
                  //   $data = $this->serCoilParser($data, $regCountOrData);
                  //return $data;
                  break;
                }
                case 'fc5':
                case 'fc6':
                case 'fc16': {
                  $dataTypes = gettype($regCountOrData) == "array" ?
                      $regCountOrData : array($regCountOrData);
                  //fc5($unitId, $reference, $data, $dataTypes)
                  //fc6($unitId, $reference, $data, $dataTypes)
                  //fc16($unitId, $reference, $data, $dataTypes)
                  $data = $this->modbus->$callfunction($slaveId, $reference, $dataTypes);
                  break;
                }
                case 'f15' : {
                  //fc15($unitId, $reference, $data)
                  $dataTypes = $regCountOrData;
                  $data = $this->modbus->$callfunction($slaveId, $reference, $dataTypes);
                  break;
                }
                case 'f22' : {
                  //fc22($unitId, $reference, $andMask, $orMask)
                  echo "Warning function code: {$functionCode}, currently not implement!!!\r\n";
                  break;
                }
                case 'f23' : {
                  //fc23($unitId, $referenceRead, $quantity, $referenceWrite, $data, $dataTypes)
                  echo "Warning function code: {$functionCode}, currently not implement!!!\r\n";
                  break;
                }
                default:  {
                  echo "Error function code: {$functionCode}, not support in this libaray!!!\r\n";
                  return;
                }

              }
              //$data = $this->modbus->$callfunction($slaveId, $reference, $sendData );
              //var_dump($data);
            }
          }
          else {
            echo "function Code 23 not suported in Esmi\modbus\r\n";
            return null;
          }
        }
        //var_dump($data);
        return $data;
      } catch (Exception $e) {
      	// Print error information if any
      	echo $this->modbus;
      	echo $e;
      	exit;
      }
    }
    return null;
  }
  protected function serCoilParser( $data,$quantity) {
    $data_boolean_array = array();
		$di = 0;
		foreach ($data as $value) {
			for ($i = 0; $i < 8; $i++) {
				if ($di == $quantity) {
					continue;
				}
				// get boolean value
				$v = ($value >> $i) & 0x01;
				// build boolean array
				if ($v == 0) {
					$data_boolean_array[] = false;
				} else {
					$data_boolean_array[] = true;
				}
				$di++;
			}
		}
		return $data_boolean_array;

  }
  // public function getResponse ($raw=false,$offsetl=0,$offsetr=0)
  // {
  //   $data = null;
  //   if ($this->isSerial) {
  //
  //     $recdata = $this->modbus->getResponse($raw,$offsetl,$offsetr);
  //     foreach ($recdata as $key => $value) {
  //       $data[$key] = hexdec($value);
  //     }
  //     return $data;
  //     // //var_dump($data);
  //     // if ( $functionCode <= 2)
  //     //   $data = $this->serCoilParser($data, $regCountOrData);
  //     //
  //     // echo ("Modbus->getResponse;");
  //     // var_dump($a);
  //     // return $a;
  //     //return $this->modbus->getResponse($raw,$offsetl,$offsetr);
  //   }
  //   else {
  //
  //   }
  //
  // }
}
