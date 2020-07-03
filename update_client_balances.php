



<?php
//connect to DB
date_default_timezone_set("Africa/Nairobi");
include("connect/connect.php");

//Hide Errors
ini_set('display_errors',false);
/**
 * @author Walter Omedo - Frontier Optical Networks Limited
 * @copyright 2020
 *This script updates client's balances from the billing server.
  * Published on 6th June 2020
 */

header('Content-Type: text/plain');
//Lets get all the clients
$query=$conn->prepare("SELECT id AS client_id, client_no,bill_ref_no FROM clients ORDER BY id ASC");
$query->execute();
$clients_count=$query->rowCount();
$items = array();
if($clients_count>=1){
 $clients = $query->fetchAll(PDO::FETCH_ASSOC);
 //Lets create an array to save the items
// $items = array();
 
  foreach($clients as $client){
   $items[] = $client['bill_ref_no'];
  } 


}

//$conn->query('KILL CONNECTION_ID()');
//$conn=null;
//$conn=null;
//open new connection
include("connect/connect240.php");
$count_items = count($items);

if($count_items!=0){
  for($i=0;$i<=$count_items;$i++){
  if(!isset($items[$i])){
   $items[$i] = null;
  }
  $client_no = $items[$i];
  //Get client balances
  $bal=$conn->prepare("SELECT SUM(IF(bs.cr_dr =  'C', bs.amount, - bs.amount ) ) AS Bal
                  FROM billingdb.statment  as bs
                  JOIN maindb.client as mc ON (mc.client_no = bs.client_no)
                  WHERE bs.acc_no =  '80061004' AND CONCAT( bs.cat_code,bs.client_no,bs.item_code,bs.pair_no)='".$client_no."'
                  GROUP BY CONCAT( bs.cat_code, bs.client_no, bs.item_code, bs.pair_no )
                  ORDER BY CONCAT( bs.cat_code, bs.client_no, bs.item_code, bs.pair_no ),bs.curr_type,bs.val_date,bs.ref_no");


$bal->execute();
$bal_row = $bal->fetch(PDO::FETCH_ASSOC);
$amount = $bal_row['Bal'];
if(!isset($balances[$items[$i]])){
   $balances[$items[$i]] = null;
}
//save in array
$balances[$items[$i]] = $amount;

//$balances[$items[$i]] = $amount;

  }
}



//Close this  newconnection and connect back to our main server
//$conn->query('KILL CONNECTION_ID()');
//$conn=null;

include("connect/connect.php");

try{
	$conn->beginTransaction();
	  //Lets loop through the array
	  foreach($balances as $cl => $key){
		  $client_no = $cl;
		  $amount = $key;		  
		  $update = $conn->prepare("UPDATE clients SET account_balance='".$amount."' WHERE bill_ref_no='".$client_no."'  LIMIT 1");
		  $update->execute();		  
	  }
	  
	$conn->commit();
	file_put_contents('logs/balances.txt',"Finished updating client balances at:-".date('Y-m-d H:i:s'),FILE_APPEND | LOCK_EX);
  file_put_contents('logs/balances.txt', "\n", FILE_APPEND);
}catch(PDOException $e){
	$conn->rollBack();	
	//save error in log
	file_put_contents('logs/balances.txt',"Error:-".$e->getMessage(),FILE_APPEND | LOCK_EX);
  file_put_contents('logs/balances.txt', "\n", FILE_APPEND);
	
}

//Lets add new clients and those that dont have arrears to the automatic disconnection pool
try{
$conn->beginTransaction();
$query = $conn->prepare("SELECT commence_date,account_balance,bill_ref_no,is_billing FROM clients ORDER BY id DESC");
$query->execute();
$query_count = $query->rowCount();
if($query_count>=1){
$query_rows = $query->fetchAll(PDO::FETCH_ASSOC);
foreach($query_rows as $query_row){
 $balance = $query_row['account_balance'];
 $is_billing = $query_row['is_billing'];
 $account_no = $query_row['bill_ref_no'];
 $commence_date = $query_row['commence_date'];
 $start_date = strtotime('2020-06-01');
 $register_date = strtotime($commence_date);
 $time_diff = $register_date - $start_date;
 if((($balance>=0.00) || ($time_diff>0)) && $is_billing=='Yes'){
 //Add this user
  $update_user = $conn->prepare("UPDATE clients SET auto_disconnect='Yes' WHERE bill_ref_no='".$account_no."' LIMIT 1");
  $update_user->execute();
}else{
//Remove user
  $update_user = $conn->prepare("UPDATE clients SET auto_disconnect='No' WHERE bill_ref_no='".$account_no."' LIMIT 1");
  $update_user->execute();
}


}

}
$conn->commit();
}catch(PDOException $e){
	$conn->rollBack();
  //save error in log
	file_put_contents('logs/balances.txt',"Error:-".$e->getMessage(),FILE_APPEND | LOCK_EX);
  file_put_contents('logs/balances.txt', "\n", FILE_APPEND);		
}

?>


































































