<?php

/*	
	This page searches challenges using a supplied
	search term. Returns all the matching results 
	in JSON format. 
*/

// Include class structures
require_once($_SERVER["DOCUMENT_ROOT"].'/lib/X-ClassStructures.php'); // Class structures

$result = array(
	"challenges" => null,
	"status" => "unknown",
	"errorMsg" => null
);

if(isset($_REQUEST['term'])){ 
	$term = $_REQUEST['term'];
	
	// Search database for matching challenges
	$Challenges = Challenges::Search($term);
	
	// Format each challenge as an array row
	foreach($Challenges as $Challenge){
		$row = null;
		$row["ID"] = $Challenge->ID; 
		$row["LinkURLID"] = $Challenge->LinkURLID;
		$row["Title"] = $Challenge->Title;
		$row["Description"] = $Challenge->Description; 
		$row["Image"] = $Challenge->Image;
		
		// Add the row to the array
		$rows[] = $row;
	}
	
	// Add the array of challenges to the result
	$result["challenges"] = $rows;
	$result["status"] = "success";
	

} else {
	$result["status"] = "failed";
	$result["errorMsg"] = "No search teram was given";
}

// Return as JSON
echo json_encode($result);

?>
