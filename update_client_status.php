<?php
//date_default_timezone_set("Africa/Nairobi");
/**
 * @author Walter Omedo - Frontier Optical Networks Limited
 * @copyright 2020
 *This script gets all the active users and updates there statuses on our DB
  * Published on 17th June 2020
 */
header('Content-Type: text/plain');
date_default_timezone_set("Africa/Nairobi");
//Connect to the database server
include_once('connect/connect.php');
use PEAR2\Net\RouterOS;

require_once 'PEAR2_Net_RouterOS-1.0.0b6/src/PEAR2/Autoload.php';

//Connect to the DB server
include_once('connect/connect.php');
//Let's start by current user's connection status to Inactive
$disable = $conn->prepare("UPDATE clients SET connected='No'");
$disable->execute();

//Connect to router
$ip ='192.168.20.250';
$username ='customer_care';
$password ='f0ns3cr3t';

$client = new RouterOS\Client($ip,$username,$password);
//$request = new RouterOS\Request('/ip/address/print');
$request = new RouterOS\Request('/ppp/active/print');
//Define $query here
//$request->setQuery($query);
$responses = $client->sendSync($request);
$count=0;
foreach ($responses as $response) {
$count++;
$client_id = $response->getProperty('.id');
$name = $response->getProperty('name');
try{
$conn->beginTransaction();
//Lets check if there's a match on our Db
$check = $conn->prepare("SELECT id AS client_id FROM clients WHERE username='".$name."' LIMIT 1");
$check->execute();
$check_count = $check->rowCount();

if($check_count==1){
//Update user's status
$update_user = $conn->prepare("UPDATE clients SET connected='Yes' WHERE username='".$name."'");
$update_user->execute();
}
//file_put_contents('logs/sync_router.txt',"Active clients updated:- ".date('Y-m-d H:i:s'),FILE_APPEND | LOCK_EX);
//file_put_contents('logs/sync_router.txt', "\n", FILE_APPEND);
$conn->commit();
}catch(PDOException $e){
$conn->rollBack();
file_put_contents('logs/sync_router.txt',"Active clients sync error:- ".$e->getMessage(),FILE_APPEND | LOCK_EX);
file_put_contents('logs/sync_router.txt', "\n", FILE_APPEND);

}
}


file_put_contents('logs/sync_router.txt',"Active clients updated:- ".date('Y-m-d H:i:s'),FILE_APPEND | LOCK_EX);
file_put_contents('logs/sync_router.txt', "\n", FILE_APPEND);

?>

