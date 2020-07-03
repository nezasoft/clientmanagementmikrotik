<?php
include_once('connect/connect.php');

date_default_timezone_set("Africa/Nairobi");
/* This scripts gets user's data*/
/*Reads data from the Mikrotik Router and adds/updates the database server with the client listing and status. */
use PEAR2\Net\RouterOS;
require_once 'PEAR2_Net_RouterOS-1.0.0b6/src/PEAR2/Autoload.php';
header('Content-Type: text/plain');
//Connect to the database server
//include_once('connect/connect.php');
$ip ='192.168.20.250';
$username = 'customer_care';
$password = 'f0ns3cr3t';
$clients = array();
$client = new RouterOS\Client($ip,$username,$password);
//$request = new RouterOS\Request('/ip/address/print');
$request = new RouterOS\Request('/ppp/secret/print');
//Define $query here
//$request->setQuery($query);
$responses = $client->sendSync($request);
$count=0;
foreach ($responses as $response) {
$count++;
/* foreach ($response as $name => $value) {
        echo "{$name}: {$value}\n";
    }

   echo "====\n";*/
  $client_id = $response->getProperty('.id');
  $name = $response->getProperty('name');
  $service = $response->getProperty('profile');
  $disabled = $response->getProperty('disabled');
  $last_logged_out = $response->getProperty('last-logged-out');
  $comment = $response->getProperty('comment');
  $client_no = substr($name,0,3);//get the first 3 characters for  
  $username = $client_no; 
  // echo "<font color='red'>".$client_no."</font>"; 
  if($client_no=='C00'){  
  //echo $name."\n";
  $client_no = $name; 
  }elseif(substr($client_no,0,1)=='C' && substr($client_no,0,2)!='C0' ){
 // echo $name."\n";
  $client_prefix = substr($name,0,1); //returns "C"
  $client_postfix = substr($name,-3);
  $client_no=substr($name,0,-3);//remove the last 3 strings that indicate the item code and pair no
  //Lets count the len of string in the client_no
  $len = strlen($client_no);
    if($len==4){
    //Remove the prefix "C" from the client no
    $client_no = substr($client_no,1);
    $acc_no = $client_prefix."00000".$client_no.$client_postfix;
    $client_no = $client_prefix."00000".$client_no;
  }elseif($len==5){
    //Remove the prefix "C" from the client no
    $client_no = substr($client_no,1);

    $acc_no = $client_prefix."0000".$client_no.$client_postfix;
    $client_no = $client_prefix."0000".$client_no;

   }
 }elseif((substr($client_no,0,2)=='C0') && (strlen($client_no!=9))){
 // echo $name."\n";
  $client_prefix = substr($name,0,1); //returns "C"
  $client_postfix = substr($name,-3);
  $client_no=substr($name,0,-3);//remove the last 3 strings that indicate the item code and pair no
  //Lets count the len of string in the client_no
  $len = strlen($client_no);
 
    if($len==5){
    //Remove the prefix "C" from the client no
    $client_no = substr($client_no,1);
    $acc_no = $client_prefix."0000".$client_no.$client_postfix;
    $client_no = $client_prefix."0000".$client_no;
  }elseif($len==6){
    $client_no = substr($client_no,1);
    $acc_no = $client_prefix."000".$client_no.$client_postfix;
    $client_no = $client_prefix."000".$client_no;
   }elseif($len==4){
    $client_no = substr($client_no,1);
    $acc_no = $client_prefix."00000".$client_no.$client_postfix;
    $client_no = $client_prefix."00000".$client_no;
 }
}
  //Only sync clients with valid client accounts
if(strlen($client_no)==9){ 
          
  //Make requests to the database
 try{
  $conn->beginTransaction();
  //Check if client already exists
  $check = $conn->prepare("SELECT id AS client_id FROM clients WHERE client_no='".$client_no."' LIMIT 1");
  $check->execute();
  $check_count = $check->rowCount();  
  if($check_count==1){//if the record exists lets perform an update
   if($disabled=='true'){$active='No';}else{$active='Yes';}
  $update=$conn->prepare("UPDATE clients SET  user_profile='".$service."',last_active='".$last_logged_out."' WHERE client_no='".$client_no."' LIMIT 1 ");
  $update->execute();
  }else{//Insert new record
 if($disabled=='true'){$active='No';}else{$active='Yes';}
    $insert = $conn->prepare("INSERT INTO clients(client_no,account_no,username,user_profile,comment,connected,last_active)VALUES('".$client_no."','".$acc_no."','".$name."','".$service."','".htmlspecialchars(str_replace("'"," ",$comment))."','".$active."','".$last_logged_out."')");   
   $insert->execute();  
  } 
  $conn->commit();
 }catch(PDOException $e){
$conn->rollBack();
//Lets log any error message(s) we catch
file_put_contents('logs/sync_router.txt',$e->getMessage(),FILE_APPEND | LOCK_EX);
file_put_contents('logs/sync_router.txt', "\n", FILE_APPEND);
 }
}
$clients[] = $client_no;
}
//Sync Completed
file_put_contents('logs/sync_router.txt',"Finished syncing at---".date('Y-m-d H:i:s'),FILE_APPEND | LOCK_EX);
file_put_contents('logs/sync_router.txt', "\n", FILE_APPEND);
$clients = serialize($clients);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,"http://192.168.20.230/ClientManagement/sync_billing_server_data.php");
curl_setopt($ch, CURLOPT_POST, 1);
//curl_setopt($ch, CURLOPT_POSTFIELDS,"clients=$clients");
curl_setopt($ch, CURLOPT_POSTFIELDS,$clients);
//curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($clients));
// Receive server response ...
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,3);
curl_setopt($ch,CURLOPT_TIMEOUT, 20);
$response = curl_exec($ch);
print_r($response);
curl_close($ch);
//print_r(unserialize($clients));
?>


