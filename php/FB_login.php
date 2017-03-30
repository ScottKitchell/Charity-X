<?php

/*	
	This page checks to see if a user is logged 
	in. Returns the login status in JSON format.
	
	Althogh login works, this page is yet to be 
	completed.
*/

session_start();
require_once($_SERVER["DOCUMENT_ROOT"].'/lib/X-ClassStructures.php'); // Require Charity-X class structures
require_once($_SERVER["DOCUMENT_ROOT"].'/lib/FB_Database.php'); 

// Format the responce object
$result = array(
	"id" => null,
	"status" => "unknown",
	"errorMsg" => null
);

// Test if access token was generated
if(FB::SetAccessToken()) { // Logged in

	// Retrieve the users details and save/update them in the user class
	$User = new User();
	$User->FB_Sync();

	$User->Save();

	// Set User ID
	$_SESSION["UserID"] = $User->ID;
	$result["id"] = $User->ID;

	// Format success result as JSON
	$result["status"] = "success";

	// Set a cookie - expires in 60 days
	setcookie("AutoLogin", "true", time() + (60*60*24*60), "/", "charity-x.org");

} else { // Not authorised to log in

	// Unset user ID
	unset($_SESSION['UserID']);
	
	// Format failed result as JSON
	$result["status"] = "failed";
	$result["errorMsg"] = "Not authorised to login with Facebook.";
	
}

// Return the result as JSON
echo json_encode($result);

?>