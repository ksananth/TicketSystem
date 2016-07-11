<?php
require_once('loader.php');
 
// return json response 
$json = array();
 
$nameUser  = $_REQUEST["name"];
$nameEmail = $_REQUEST["email"];
$userId = $_REQUEST["userId"];
$msg = $_REQUEST["msg"];
 
// GCM Registration ID got from device
$gcmRegID  = $_REQUEST["regId"]; 
//$gcmRegID  ="APA91bG_AYuQPM6RklkT4sT9ktkZNOoOSiBq3rc7B4oDdTeV-0QPxJIHG_ttZymPNIbcXWSHeq43-RtHgmvFBdyJCGXE69kzO0RNnSPrVj5lYEgPb6VXAAwHE_nimICxWTQHPsG4oZkr";

 
/**
 * Registering a user device in database
 * Store reg id in users table
 */
 	// echo $nameUser.' '.$nameEmail.' '.$gcmRegID;
if (isset($nameUser) 
     && isset($nameEmail) 
     && isset($gcmRegID)) {
     
	// echo $nameUser.' '.$nameEmail.' '.$gcmRegID;
    // Store user details in db
    $res = storeUser($userId,$nameUser, $nameEmail, $gcmRegID);
 
    $registatoin_ids = array($gcmRegID);
    $message = array("price" => ".$msg.");
 
    $result = send_push_notification($registatoin_ids, $message);
 
    echo $result;
} else {
    // user details not found
}
?>