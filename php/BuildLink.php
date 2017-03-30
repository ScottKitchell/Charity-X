<?php

/*	
	This page builds a new donation page link 
	using a supplied challenge ID and user ID. 
	Returns the link info in JSON format. 
*/

// Include class structures
require_once($_SERVER["DOCUMENT_ROOT"].'/lib/X-ClassStructures.php'); // Class structures

// Format the responce object
$result = array(
	"ID" => null, 
	"URLID" => null, 
	"URL" => null, 
	"User" => null, 
	"Challenge" => null, 
	"Status" => "unknown",
	"Error" => null
);

// Request the challenge ID and then sync with db to return all other challenge properties
if(isset($_POST['challenge'])){
	$Challenge = new Challenge($_POST['challenge']);
	$Challenge->Sync();
} else {
	$result['Status'] = "failed";
	$result['Error'] = "URL failed to generate.";
	echo json_encode($result);
	throw ("No challenge supplied.");
	exit;
}

// Create the Link
$Link = new Link();

// If a user is signed in set there
if(isset($_SESSION['UserID'])){
	// Request the user ID
	$User = new User($_SESSION['UserID']);
	$Link->BuildURL($Challenge, $User);
} else {
	$Link->BuildURL($Challenge);
}

$Link->Save();

// Format successful Link build as JSON
$result["ID"] = $Link->ID; 
$result["URLID"] = $Link->URLID; 
$result["URL"] = $Link->URL; 
$result["User"] = $Link->User->ID; 
$result["Challenge"] = $Link->Challenge->ID;
$result["Status"] = "success";

// Return as JSON
echo json_encode($result);

?>