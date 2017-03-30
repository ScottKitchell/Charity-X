<?php

/*	
	This page logs a user out of the app. Returns 
	the login status in JSON format.
*/

session_start();
require_once($_SERVER["DOCUMENT_ROOT"].'/lib/FB_Database.php');

// Format the responce object
$result = array(
	"status" => "unknown",
	"errorMsg" => null
);

// Destroy the session and cookie
setcookie("AutoLogin", "false", time()-3600);
session_destroy();

// Unset the accesstoken
if(FB::UnsetAccessToken()){
	// Set result status to failed
	$result["status"] = "success";
} else {
	// Set result status success
	$result["status"] = "failed";
	$result["errorMsg"] = "Could not unset the Facebook access token.";
}

// Return the result as JSON
echo json_encode($result);

?>