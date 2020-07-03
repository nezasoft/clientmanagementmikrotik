


<?php
/* This scripts gets user's data*/
use PEAR2\Net\RouterOS;
require_once 'PEAR2_Net_RouterOS-1.0.0b6/src/PEAR2/Autoload.php';
header('Content-Type: text/plain');
//header('Content-Type: text/html');

$ip ='192.168.20.250';
$username = 'admin';
$password = '****';

$client = new RouterOS\Client($ip,$username,$password);
//$request = new RouterOS\Request('/ppp/secret/print');
$request = new RouterOS\Request('/ppp/active/print');

//Define $query here

$request->setQuery($query);
$responses = $client->sendSync($request);
$count=0;
foreach ($responses as $response) {
$count++;
 foreach ($response as $name => $value) {
        echo "{$name}: {$value}\n";
    }

   echo "====\n";
/*
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

 }elseif(substr($client_no,0,2)=='C0'){
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

  echo $client_no;

  $item ="Client No:-".$count."Client Name:- ".$comment." Client ID:-".$client_id." Account No:- ".$name." Service:-".$service." Disabled:- ".$disabled."\n";
 echo $item; 
*/
}

?>

