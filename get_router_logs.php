<?php
/*This script retrieves all the logs from the router.*/

use PEAR2\Net\RouterOS;
//require_once 'PEAR2_Net_RouterOS-1.0.0b6.phar';
require_once 'PEAR2_Net_RouterOS-1.0.0b6/src/PEAR2/Autoload.php';
header('Content-Type: text/plain');

$ip ='192.168.20.250';
$username = 'customer_care';
$password = 'f0ns3cr3t';

try {
    $util = new RouterOS\Util($client = new RouterOS\Client($ip,$username,$password));

    foreach ($util->setMenu('/log')->getAll() as $entry) {
        echo $entry('time') . ' ' . $entry('topics') . ' ' . $entry('message') . "\n";
    }
} catch (Exception $e) {
  //  echo 'Unable to connect to RouterOS.'.$e->getMessage();
   var_dump($e->getMessage());
}

?>
