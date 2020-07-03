




<?php
//Hide Errors
date_default_timezone_set("Africa/Nairobi");
ini_set('display_errors',false);
/**
 * @author Walter Omedo - Frontier Optical Networks Limited
 * @copyright 2020
 *This script syncs data between the mikrotik router, 230 and billing server. It updates the client's billing status(active/inactive) and client's connectivity status.
  * Published on 6th June 2020
 */
$db_prefix='';

//$der('Content-Type: text/plain');iclients=POST['clients'];
header('Content-Type: text/plain');
if (!$request=file_get_contents('php://input')){
echo "Invalid input";
exit();
}

$data = unserialize($request);
$count = count($data);

//Connect to the billing server
include_once("connect/connect240.php");
//Get user data

try{
$conn->BeginTransaction();
//Lets save our results in an array
$client_status = array();
$active_status = array();
$commencements = array();
$emails = array();
$bill_refs = array();
$client_names = array();
for($i=0;$i<=$count;$i++){

 if(!isset($data[$i])){
   $data[$i] = null;
}
 if(!isset($bill_refs[$data[$i]])){
   $bill_refs[$data[$i]] = null;
}
if(!isset($client_names[$data[$i]])){
   $client_names[$data[$i]] = null;
}
if(!isset($client_status[$data[$i]])){
   $client_status[$data[$i]] = null;
}
if(!isset($active_status[$data[$i]])){
   $active_status[$data[$i]] = null;
}
if(!isset($commencements[$data[$i]])){
   $commencements[$data[$i]] = null;
}
if(!isset($emails[$data[$i]])){
   $emails[$data[$i]] = null;
}
  $acc_no = $data[$i];
  /* We are matching the records on the mikrotik router to that on the ERP/billing server. Once found we are going to update our locate DB*/
 //Confirm this account is on the billing server
 $check_client = $conn->prepare("SELECT client_no,acc_active,commence_date,email,concat(cat_code,client_no,item_code,pair_no) AS acc_no,client_name FROM client WHERE client_no='".$acc_no."' LIMIT 1");
 $check_client->execute();
 $check_client_count=$check_client->rowCount();
 
 $client_row = $check_client->fetch(PDO::FETCH_ASSOC);

  if($check_client_count==1){
    $found = 'Yes';

  }else{
    $found ='No';
  }
  $active = $client_row['acc_active'];
  $commence_date= $client_row['commence_date'];
  $email =  $client_row['email'];
  $client_name =  $client_row['client_name'];
  $acc_ref_no =  $client_row['acc_no'];
  $email=preg_replace('/^([^,]*).*$/', '$1', $email);//Remove commas
  $email=preg_replace('/^([^;]*).*$/', '$1', $email);//Remove semi colons
  $bill_refs[$data[$i]] = $acc_ref_no;
  $client_names[$data[$i]] = str_replace("'"," ",$client_name);
  $client_status[$data[$i]] = $found;
  $active_status[$data[$i]] = $active;
  $commencements[$data[$i]] = $commence_date;
  $emails[$data[$i]] = $email;

}
//Kill the connection with a KILL Statement.
$conn->query('KILL CONNECTION_ID()');
$conn=null;
}catch(PDOException $e){
$conn->rollBack();
file_put_contents('logs/sync_server.txt',"Error:-".$e->getMessage(),FILE_APPEND | LOCK_EX);
file_put_contents('logs/sync_server.txt', "\n", FILE_APPEND);

}
/* Before going further lets close the existing connection to 240 server and open a new connection to this server. We will do so by forcibly killing the connection */



//Now lets open a new connection
include_once("connect/connect.php");
//save the client's billing status in our log file
$client_count = count($client_status);
//print $client_count;
$item_no=0;
foreach($client_status as $cl => $key){
$item_no++;
 //echo "No.".$item_no."A/C No:-".$cl." Status:-".$key."\n";

  $client_acc = $cl;
  $status = $key;
  //lets update our db with statuses from the billing server regarding this clients.
  try{
	  $conn->BeginTransaction();
	  $update = $conn->prepare("UPDATE clients SET account_found='".$status."' WHERE client_no='".$client_acc."' LIMIT 1");
	  $update->execute();
	  $conn->commit();  
  }catch(PDOException $e){
	  $conn->rollBack();
	  file_put_contents('logs/sync_server.txt',"Error:-".$e->getMessage(),FILE_APPEND | LOCK_EX);
      file_put_contents('logs/sync_server.txt', "\n", FILE_APPEND);
	  
  }

}

//Lets update the active status 
foreach($active_status as $cl => $key){
 //echo "No.".$item_no."A/C No:-".$cl." Status:-".$key."\n";
  $client_acc = $cl;
  $status = $key; 
  if($status==1){
	  $status ='Yes';	  
  }else{	  
	  $status ='No';
  }
  //lets update our db with statuses from the billing server regarding this clients.
  try{
	  $conn->BeginTransaction();
	  $update = $conn->prepare("UPDATE clients SET is_billing='".$status."' WHERE client_no='".$client_acc."' LIMIT 1");
	  $update->execute();
	  $conn->commit();

  }catch(PDOException $e){
	  $conn->rollBack();
	  file_put_contents('logs/sync_server.txt',"Error:-".$e->getMessage(),FILE_APPEND | LOCK_EX);
      file_put_contents('logs/sync_server.txt', "\n", FILE_APPEND);
	  
  }

}

try{

//Lets update the commence date
foreach($commencements as $cl => $key){
  $client_acc = $cl;
  $commence_date= $key;
  //lets update our db with statuses from the billing server regarding this clients.
  try{
	  $conn->BeginTransaction();
	  $update = $conn->prepare("UPDATE clients SET commence_date='".$commence_date."' WHERE client_no='".$client_acc."' LIMIT 1");
	  $update->execute();
	  $conn->commit();
	  
  }catch(PDOException $e){
	  $conn->rollBack();
	  file_put_contents('logs/sync_server.txt',"Error:-".$e->getMessage(),FILE_APPEND | LOCK_EX);
    file_put_contents('logs/sync_server.txt', "\n", FILE_APPEND);
	  
  }

}

//Lets update the emails
foreach($emails as $cl => $key){
  $client_acc = $cl;
  $email=$key;
  //lets update our db with statuses from the billing server regarding this clients.
  try{
	  $conn->BeginTransaction();
	  $update = $conn->prepare("UPDATE clients SET email='".$email."' WHERE client_no='".$client_acc."' LIMIT 1");
	  $update->execute();
	  $conn->commit();
	  
  }catch(PDOException $e){
	  $conn->rollBack();
	  file_put_contents('logs/sync_server.txt',"Error:-".$e->getMessage(),FILE_APPEND | LOCK_EX);
      file_put_contents('logs/sync_server.txt', "\n", FILE_APPEND);
	  
  }

}

//Lets update the bill_ref_no
foreach($bill_refs as $cl => $key){
  $client_acc = $cl;
  $acc_no=$key;
  //lets update our db with statuses from the billing server regarding this clients.
  try{
	  $conn->BeginTransaction();
	  $update = $conn->prepare("UPDATE clients SET bill_ref_no='".$acc_no."' WHERE client_no='".$client_acc."' LIMIT 1");
	  $update->execute();
	  $conn->commit();
	  
  }catch(PDOException $e){
	  $conn->rollBack();
	  file_put_contents('logs/sync_server.txt',"Error:-".$e->getMessage(),FILE_APPEND | LOCK_EX);
    file_put_contents('logs/sync_server.txt', "\n", FILE_APPEND);
	  
  }

}
//Lets update the client's name
foreach($client_names as $cl => $key){
  $client_acc = $cl;
  $client_name=$key;
  //lets update our db with statuses from the billing server regarding this clients.
  try{
	  $conn->BeginTransaction();
	  $update = $conn->prepare("UPDATE clients SET client_name='".$client_name."' WHERE client_no='".$client_acc."' LIMIT 1");
	  $update->execute();
	  $conn->commit();
	  
  }catch(PDOException $e){
	  $conn->rollBack();
	  file_put_contents('logs/sync_server.txt',"Error:-".$e->getMessage(),FILE_APPEND | LOCK_EX);
   file_put_contents('logs/sync_server.txt', "\n", FILE_APPEND);
	  
  }

}
//print_r($client_names);
file_put_contents('logs/sync_server.txt',"Finished updating server with status of clients from billing server :-".date('Y-m-d H:i:s'),FILE_APPEND | LOCK_EX);
file_put_contents('logs/sync_server.txt', "\n", FILE_APPEND);
}catch(Exception $e){
file_put_contents('logs/sync_server.txt',"Error:-".$e->getMessage(),FILE_APPEND | LOCK_EX);
file_put_contents('logs/sync_server.txt', "\n", FILE_APPEND);

}
//Kill the connection with a KILL Statement.
//$conn->query('KILL CONNECTION_ID()');
//$conn=null;
//Lets update client balances
include("update_client_balances.php");
?>

