
/*****  DONATE PAGE FUNCTIONS  *****/

// Set Stripe publishable key
Stripe.setPublishableKey('pk_test_vgNLnQ0gC91FmNyDDy4CinH9'); 


$(function() {
	"use strict";
	
	// Format Inputs
	$('#Data-CardNumber').payment('formatCardNumber');
	$('#Data-CardExp').payment('formatCardExpiry');
    $('#Data-CardCVC').payment('formatCardCVC');
	
	
	// Amount Button event handler - Change donation amount to $
	$(".AmountButton").click(function() {
        // Set the donation amount
		var donation = $(this).data("amount");
		$("#AmountInput").val("$"+donation);
		$("#Data-Amount").val(donation);
		// Set the points
		var points = CalcPoints(donation, 1);
		$(".score_val").html(points);
		// Change selected item
		$("#AmountText").slideUp("fast");
		$(".AmountButton, .AmountOther").removeClass("Selected");
		$(this).addClass("Selected");
		console.log("Amount: "+$("#Data-Amount").val());
    });
	
	
	// Other Amount Button event handler - Slide down text input
	$(".AmountOther").click(function() {
		$("#AmountText").slideDown("fast");
		$("#AmountInput").focus();
		$(".AmountButton").removeClass("Selected");
		$(this).addClass("Selected");
    });
	
	$("#AmountInput").bind("keyup change",function(){
		// Set the donation amount
		var donation = 0;
		if($("#AmountInput").validateInput("currency")){ 
			donation = $("#AmountInput").val().replace(/[^\d.-]/g, '');
		}
		$("#Data-Amount").val(donation);
		
		// Set the points
		var points = CalcPoints(donation, 1);
		points = points.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
		$(".score_val").html(points);
	});
	
	function CalcPoints(donation, rate){
		var points = Math.round(Math.round(Math.pow(donation * 5, 1.2)) * rate);
		return points;
	}
	
	// Payment option radio selection
	$("input[type=radio][name=CardOption]").change(function(){
		var option = $("input[type=radio][name=CardOption]:checked");
		$(".CardOption").toggleClass("Selected", false);
		option.parents(".CardOption").toggleClass("Selected", true);
		if(option.val() === "new"){
			// New customer
			$("#PaymentFormCard").slideDown();
			$("#Data-ExistingCustomer").val('false');
			$("#Data-ExistingCustomerID").val("");
		} else {
			// Existing customer
			$("#PaymentFormCard").slideUp();
			$("#Data-ExistingCustomer").val("true");
			$("#Data-ExistingCustomerID").val(option.val());
		}
		
	});
	// Check first payment option when page loads
	$("#CardOptions").find("input[type=radio][name=CardOption]:first").click();
	
	// Payment Method Selection - Change payment method to selected item
	$(".Pmethod").click(function(){
		$(this).siblings(".row").removeClass("selected");
		$(this).find(".glyphicon-unchecked").removeClass("glyphicon-unchecked").addClass("glyphicon-check");
		$(this).siblings(".row").find(".glyphicon-check").removeClass("glyphicon-check").addClass("glyphicon-unchecked");
		$(this).addClass("selected");
	});
	
	// Help Score Button
	$("#hs").click(function(){
		$(this).addClass("selected");
		$(this).siblings("div").removeClass("selected");
		$("#extra-medals").slideUp("fast");
		$("#extra-hs").slideDown("fast");
	});
	
	//Medls Button
	$("#medals").click(function(){
		$(this).addClass("selected");
		$(this).siblings("div").removeClass("selected");
		$("#extra-hs").slideUp("fast");
		$("#extra-medals").slideDown("fast");
	});
	
	
	
	// Trigger SubmitDonation Event
	$("#ConfirmDonation").click(function(){
		$(document).trigger("SubmitDonation");
	});
	$('#payment-form').submit(function(event) {
		event.preventDefault();
		$(document).trigger("SubmitDonation");
	});
	
	
	//Confirm support
	$(document).on("SubmitDonation", function() {
		console.log("Submitting donation...");
		
		// Display loading
		$('body').css('cursor', 'progress');
		// Disable the submit button to prevent repeated clicks:
		$('#ConfirmDonation').prop('disabled', true);
		
		// Retreive the Form and Card
		var Card = $('input[name=CardOption]:checked').val();
		
		// Validate Inputs
		var ErrorCount = 0;
		if(Card === "new"){
			// Validate New Card
			console.log("Validating new credit card fields...");
			// Validate Amount
			if(!$("#Data-Amount").validateInput("currency")){ ErrorCount++; }
			// Validate Email
			if(!$("#Data-Email").validateInput("email")){ ErrorCount++; }		
			// Validate Name
			if(!$("#Data-CardName").validateInput("cc-name")){ ErrorCount++; }	
			// Validate Card Number
			if(!$("#Data-CardNumber").validateInput('cc-number')){ ErrorCount++; }	
			// Validate Card Expiry
			if(!$('#Data-CardExp').validateInput('cc-exp')){ ErrorCount++; }		  
			// Validate Card CVC
			if(!$('#Data-CardCVC').validateInput('cc-cvc')){ ErrorCount++; }		
			// Validate Post Code
			if(!$('#Data-PostCode').validateInput('post-code')){ ErrorCount++; }	
		} else {
			// Validate Existing Card
			console.log("Validating existing customer fields...");
			if(!$("#Data-ExistingCustomerID").validateInput("cc-stripe")){ ErrorCount++; }
		}
		
		console.log("Validating complete: "+ErrorCount+" errors found.");
		
		// If errors were found stop processing donation
		if(ErrorCount > 0){
			$('body').css( 'cursor', 'default' );
			$('#ConfirmDonation').prop('disabled', false);
			return false;
		}
		// All fields are validated
		
		if(Card === "new"){ 
			// Request a token from Stripe
			console.log("Generating Stripe token...");
			
			Stripe.card.createToken($("#payment-form"), function(status, response){
				if (response.error) { // Error!
					// Display the error
					console.log("Error: " + response.error.message);
					$("#DonationError").text(response.error.message);
					
					// Re-enable submission
					$('#ConfirmDonation').prop('disabled', false);
					$('body').css('cursor', 'default');
				} else { // Token was created!
					// Get the token ID:
					var token = response.id;
					console.log("Token generated ("+token+")");
					
					// Process the donation 
					processDonation(token);
				}
			});
		} else {
			// Process the donation
			processDonation(null);
		}
		
	});
	
	// Process the donation on the sever
	function processDonation(token) {
		console.log("Processing donation...");
		
		$.post("php/AuthoriseDonation.php",
		{
			LinkID: $("#Data-LinkID").val(),
			Amount: $("#Data-Amount").val(),
			Email: $("#Data-Email").val(),
			CardName: $("#Data-CardName").val(),
			PostCode: $("#Data-PostCode").val(),
			SaveCard: $("#Data-SaveCard").val(),
			ExistingCustomer: $("#Data-ExistingCustomer").val(),
			ExistingCustomerID: $("#Data-ExistingCustomerID").val(),
			stripeToken: token
		},
		function(data, status){
			if(status === "success" && data.status === "success"){
				console.log("Donation successful.");
				// Go to thanks page
				window.location.href = "http://charity-x.org/thanks.php?reciept=" + data.id;

			} else {
				console.log("An error occured: " + data.errorMsg);
				$("#DonateError").html("An error occured: " + data.errorMsg);
				// Display loading complete 
				$('body').css( 'cursor', 'default' );
				$('#ConfirmDonation').prop('disabled', false);
			}
		}, "json");
	}

	
	
});
	