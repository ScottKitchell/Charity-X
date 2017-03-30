<?php

/*	
	This page creates a new challenge using 
	posted challenge data. 
	Returns the confimation in JSON format. 
*/

// Include class structures
require_once($_SERVER["DOCUMENT_ROOT"].'/lib/X-ClassStructures.php');

// Format the response object
$result = array(
	"ID" => null,
	"URL" => null,
	"status" => 'unknown',
	"errorMsg" => ""
);

// Only process if a POST is made
if($_SERVER['REQUEST_METHOD'] == 'POST'){

	// Create a new Challenge
	$Challenge = new Challenge();
	
	// Get the Title
	$Challenge->Title = Sanitize::Text($_POST["title"], $valid);
	if(!$valid){
		$result["status"] = "failed";
		$result["errorMsg"] .= "The title is not a valid string. ";
	}
	
	// Get the Description
	$Challenge->Description = Sanitize::Text($_POST["description"], $valid);
	if(!$valid){
		$result["status"] = "failed";
		$result["errorMsg"] .= "The description is not a valid string. ";
	}
	
	// Get the Target amount
	$Challenge->Target = Sanitize::Currency($_POST["target"], $valid);
	if(!$valid && isset($Challenge->Target)){
		if($Challenge->Target == 0){
			$Challenge->Target = null;
		} else{
			$result["status"] = "failed";
			$result["errorMsg"] .= "The target amount $Challenge->Target is not a valid amount. ";
		}
	}
	
	// Get the start date
	$Challenge->StartDate = Sanitize::Date($_POST["startDate"], $valid);
	if(!$valid){
		$result["status"] = "failed";
		$result["errorMsg"] .= "The start date is not a valid date. ";
	}
	
	// Get the end date
	$Challenge->EndDate = Sanitize::Date($_POST["endDate"], $valid);
	if(!$valid && isset($Challenge->EndDate)){
		$result["status"] = "failed";
		$result["errorMsg"] .= "The end date is not a valid date. ";
	}
	
	// Get Charity1
	$Challenge->Charity1 = Sanitize::Text($_POST["charity1"], $valid);
	if(!$valid){
		$result["status"] = "failed";
		$result["errorMsg"] .= "The first charity is not a valid charity. ";
	}
	
	// Get Charity2
	$Challenge->Charity2 = Sanitize::Text($_POST["charity2"]);
	
	// Get Charity3
	$Challenge->Charity3 = Sanitize::Text($_POST["charity3"]);
	
	$Challenge->Charity1Percent = 100;
	$Challenge->Charity2Percent = 0;
	$Challenge->Charity3Percent = 0;
	
	// Get the Image
	$Challenge->Image = Sanitize::Text($_POST["image"], $valid);
	if(!$valid){
		$result["status"] = "failed";
		$result["errorMsg"] .= "The image is not a valid file. ";
	}
	
	// If validation errors then exit
	if($result["status"] == "failed"){
		echo json_encode($result);
		exit;
	}
	
	// Build the default URL Link
	$Link = new Link();
	$Link->BuildDefaultURL($Challenge);
	$Challenge->LinkURLID = $Link->URLID;
	
	// Set the challenge creator
	$Challenge->User = new User($_SESSION["UserID"]);
	
	
	if($Challenge->Save() && $Link->Save()){
		// Challenge saved successfully
		$result["ID"] = $Challenge->ID;
		$result["URL"] = $Challenge->LinkURLID;
		$result["status"] = "success";
	} else {
		// Save failed
		$result["status"] = "failed";
		$result["errorMsg"] = "The challenge did not save.";
	}

	
} else{
	// No posted values
	$result["status"] = "failed";
	$result["errorMsg"] = "No challenge data was posted.";
}

//Return the result as JSON
echo json_encode($result);

?>