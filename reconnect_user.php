<?php


/**
 * @author Walter Omedo - Frontier Optical Networks Limited
 * @copyright 2020
 *This reconnects users giving them internet access.
  * Published on 7th June 2020
 */

use PEAR2\Net\RouterOS;

require_once 'PEAR2_Net_RouterOS-1.0.0b6/src/PEAR2/Autoload.php';

//Connect to the DB server
include_once('connect/connect.php');


$ip ='192.168.20.250';
$username ='admin';
$password ='****';


$client = new RouterOS\Client($ip,$username,$password);
$request = new RouterOS\Request('/ppp/secret/print');
$request->setArgument('.proplist','.id');
$request->setQuery(RouterOS\Query::where('name','C00001000'));

$id = $client->sendSync($request)->getProperty('.id');

$update = new RouterOS\Request('/ppp/secret/set');
$update->setArgument('numbers',$id);
$update->setArgument('disabled','false');
$client->sendSync($update);


?>

