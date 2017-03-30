<?php

/*	
	This page proceesses a new donation using 
	Stripe  (stripe.com) payment processing. 
	Returns the confimation in JSON format. 
*/

// Include class structures
require_once($_SERVER["DOCUMENT_ROOT"].'/lib/X-ClassStructures.php');
// Include Stripe API 
require_once($_SERVER["DOCUMENT_ROOT"].'/lib/stripe-php-4.3.0/stripe-php-4.3.0/init.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/config/config_stripe.php'); // Private key
// Set the Stripe secret key (remember to change this to the live secret key in production)
\Stripe\Stripe::setApiKey(PRIVATE_KEY);

// Format the response object
$result = array(
	"id" => "",
	"status" => "unknown",
	"errorMsg" => ""
);

// Only process if a POST is made
if($_SERVER['REQUEST_METHOD'] == 'POST'){

	// Create a new donation object
	$Donation = new Donation();
	$result['id'] = $Donation->ID;
	
	// Get the Donation amount
	$Donation->Amount = Sanitize::Currency($_POST['Amount'], $valid);
	if(!$valid){
		$result["status"] = "failed";
		$result["errorMsg"] .= "The donation amount is not a valid amount. ";
	}
	
	// Get the Donor details
	if(isset($_SESSION['UserID'])){
		// Donor is logged in
		$Donor = new User($_SESSION['UserID']);
		$Donor->Sync();
	} else {
		// Donor is not logged in
		$Donor = new User();

		// Set the donor name as the cardholder name
		$Donor->Name = Sanitize::Text($_POST['CardName'], $valid);
		if(!$valid){
			$result["status"] = "failed";
			$result["errorMsg"] .= "The name on the card is not a valid name. ";
		}

		// Set the Donor email
		$Donor->Email = Sanitize::Email($_POST['Email'], $valid);
		if(!$valid){
			$result["status"] = "failed";
			$result["errorMsg"] .= "The email is not a valid email. ";
		}
	}
	$Donation->User = $Donor;

	// Get the donation page Link
	$Link = new Link(Sanitize::Int($_POST['LinkID']));
	if(!$valid){
		$result["status"] = "failed";
		$result["errorMsg"] .= "The link ID $Link->ID is not a valid ID. ";
	}
	$Link->Sync();
	$Donation->Link = $Link;
	
	// Get the Challenge
	$Challenge = $Link->Challenge;
	$Challenge->Sync();
	$Donation->Challenge = $Challenge;

	// Get the Charities
	$Donation->Charity1 = $Challenge->Charity1;
	$Donation->Charity1Percent = $Challenge->Charity1Percent;
	$Donation->Charity2 = $Challenge->Charity2;
	$Donation->Charity2Percent = $Challenge->Charity2Percent;
	$Donation->Charity3 = $Challenge->Charity3;
	$Donation->Charity3Percent = $Challenge->Charity3Percent;
	
	// Get the Points
	// Create a Points object (Points multiplier is set at x1)
	$Points = new Points();
	$Points->User = $Donor;
	$Points->Action = "Donation";
	$Points->Link = $Link->ID;
	$Points->Rate = 1;
	$Points->calc($Donation->Amount);
	$Donation->Points = $Points;
	
	// Get the Stripe details
	$Token = Sanitize::Text($_POST['stripeToken']);
	$SaveCard = Sanitize::Bool($_POST['SaveCard']);
	if($ExistingCustomer = Sanitize::Bool($_POST['ExistingCustomer'])){
		$CustomerID = Sanitize::Text($_POST['ExistingCustomerID']);
		if(!isset($CustomerID)){
			$result["status"] = "failed";
			$result['errorMsg'] .= "No Stripe customer ID  was given. ";
		}
	}
	
	// Check for any errors before charging the credit card
	if($result["status"] == "failed"){
		echo json_encode($result);
		exit;
	}
	
	// Process the payment using Stripe API (stripe.com/docs/api)
	
	if($ExistingCustomer || $SaveCard) {
		// Process the payment using a Stripe Customer ID.
		// This is used if an account already exists or the account is to be saved.
		
		// If they want to save their card details (No existing Stripe Customer ID)
		if($SaveCard && !$ExistingCustomer) {
			// Create a new Stripe Customer
			try{
				$Customer = \Stripe\Customer::create(array(
					"source" => $Token,
					"description" => $Donor->Name
				));
				
				// Get the Customer ID from the newly created Stripe customer object
				$CustomerID = $Customer->id;
				
			} catch(Stripe\Error\InvalidRequest $e) {
				$result["status"] = "failed";
				$result["errorMsg"] = "Stripe could not create a new customer.";
				echo json_encode($result);
				exit;
			}

			
		}
		
		// Try to charge the Customer
		try{
			$Charge = \Stripe\Charge::create(array(
				"amount" => ($Donation->Amount * 100), // Amount in cents - Min is 100
				"currency" => "aud",
				"customer" => $CustomerID,
				"receipt_email" => $Donor->Email,
				"description" => "Charity-X donation",
				"statement_descriptor" => "Charity-X donation", // Max 22 characters
				"metadata" => array("DonationID" => $Donation->ID, "UserID" => $Donor->ID)
			));

			// Update the Result
			if($Charge->status == ("succeeded" || "pending")){
				$result["status"] = "success";
			} else{
				$result["status"] = "failed";
				$result["errorMsg"] = "Stripe could not process the card correctly (status: $Charge->status).";
				echo json_encode($result);
				exit;
			}

		} catch(\Stripe\Error\Card $e) {
			// The card has been declined
			$result['status'] = "failed";
			$result['errorMsg'] = $e->message;
			echo json_encode($result);
			exit;
		}
		
	} else {
		// Process the payment using a Stripe Token.
		// This is used if no Stripe Customer ID is avalible and the card is not to be saved.
		
		// Try to charge the card using the Stripe token (generated in donate.js)
		try {
			$Charge = \Stripe\Charge::create(array(
				"amount" => ($Donation->Amount * 100), // Amount in cents
				"currency" => "aud",
				"source" => $Token,
				"receipt_email" => $Donor->Email,
				"description" => "Charity-X donation",
				"statement_descriptor" => "Charity-X donation", // Max 22 characters
				"metadata" => array("DonationID" => $Donation->ID, "UserID" => $Donor->ID)
			));
			
			// Update the Result
			if($Charge->status == ("succeeded" || "pending")){
				$result["status"] = "success";
			} else{
				$result["status"] = "failed";
				$result["errorMsg"] = "Stripe could not process the card correctly (status: $Charge->status).";
				echo json_encode($result);
				exit;
			}
			
		} catch(\Stripe\Error\Card $e) {
			// The card has been declined
			$result['status'] = "failed";
			$result['errorMsg'] = $e->message;
			echo json_encode($result);
			exit;
		}
	}
	// The Stripe Customer or Card Token has now been charged.

	// Add the Stripe charge id and reciept no. to the donation object
	$Donation->StripeChargeID = $Charge->id;
	$Donation->RecieptNo = $Charge->receipt_number;
	
	// If save card was selected save the card
	if($SaveCard && !$ExistingCustomer) {
		// Create a Stripe card object - No confidential info is stored
		$StripeCard = new StripeCard($CustomerID);
		$StripeCard->User = $Donor;
		$StripeCard->Brand = $Charge->source->brand;
		$StripeCard->ExpiryMonth = $Charge->source->exp_month;
		$StripeCard->ExpiryYear = $Charge->source->exp_year;
		$StripeCard->Last4 = $Charge->source->last4;

		// Save the Stripe card
		if(!$StripeCard->Save()){
			$result['status'] = "failed";
			$result['errorMsg'] = "The donation succeded but the card did not save correctly.";
		}
	}
	
	// Save the Donation
	if(!$Donation->Save()){
		$result['status'] = "failed";
		$result['errorMsg'] = "The donation succeded but the result did not save correctly.";
	}
	
	//Return the result as JSON
	echo json_encode($result);
	
}

?>