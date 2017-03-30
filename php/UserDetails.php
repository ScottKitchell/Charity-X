<?php

/*	
	This page returns the user data from facebook
	using the Facebook session and saves it to 
	the database. Returns the user data in JSON 
	format. 
*/

// Include class structures
require_once($_SERVER["DOCUMENT_ROOT"].'/lib/X-ClassStructures.php');

// Format the responce object
$result = array(
	"ID" => null,
	"FirstName" => null,
	"LastName" => null,
	"Email" => null,
	"Picture" => null,
	"Gender" => null,
	"Link" => null,
	"Locale" => null,
	"TimeZone" => null,
	"Score" => null,
	"status" => "unknown",
	"errorMsg" => null
);

// Create a new user object
$User = new User($_SESSION['UserID']);

// Sync the user data with Facebook 
if($User->Sync()){
	// Format the responce user data
	$result["ID"] = $User->ID;
	$result["FirstName"] = $User->FirstName;
	$result["LastName"] = $User->LastName;
	$result["Email"] = $User->Email;
	$result["Picture"] = $User->Picture;
	$result["Gender"] = $User->Gender;
	$result["Link"] = $User->FBLink;
	$result["Locale"] = $User->Locale;
	$result["TimeZone"] = $User->TimeZone;
	$result["Score"] = $User->Score;
	$result["status"] = "success";
	
	// Save the user details to the database
	$User->Save();
	
} else {
	$result["status"] = "failed";
	$result["errorMsg"] = "The user data could not be retrieved from the database.";
}

// Return the user data as JSON
echo json_encode($result);

?>