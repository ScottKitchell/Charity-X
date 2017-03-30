
/*****  GENERAL FUNCTIONS  *****/

$(function() {
	"use strict";
	
	// Hide elements marked HiddenOnLogout
	$(".HiddenOnLogout").hide();
	
	// Navigation Menu - Show in the nav menu.
	$(".PrimaryAppBar").on("click", "#NavAction", function(){		
		$("#NavSlider").show(300);
		$("#Backdrop").fadeIn(300);
		$("#UserMenuSlider").hide();
	});
	
	
	// User Menu - Show  the user menu
	$(".PrimaryAppBar").on("click", "#UserMenuAction", function(){
		$("#UserMenuSlider").show(300);
		$("#Backdrop").fadeIn(300);
		$("#NavSlider").hide();
	});
	
	
	// Click off the Menu - Hide menu
	$("#Backdrop").click(function(){
		$("#NavSlider").hide(300);
		$("#UserMenuSlider").hide(300);
		$(".Dialog").fadeOut(300);
		$("#Backdrop").fadeOut(400);
	});
	
	
	//
	// Default Login status change event handler
	//
	$(document).on("loginStatusChange", function(e, loginStatus){
		if(loginStatus === "connected"){

			// Login successful - Update UI
			$(".HiddenOnLogin").hide(); // Hide content
			$(".HiddenOnLogout").show(); // Show content
			$("body").data("login-status", "connected"); // Set login-status to connected
			
			// Fetch user data
			console.log('Fetching user data...');
			$.post("php/UserDetails.php", function(data){
				if(data.status === "success"){
					// User data was returned
					console.log("Fetch user data: "+data.status);
					// Update the UI
					$("#UserMenuAction").addClass('UserImg').html('<img src="'+data.Picture+'">');
					$("span.FirstName").html(data.FirstName);
					$("span.LastName").html(data.LastName);
					$("span.points").html(data.Score);
				} else {
					// Retieveing the user data failed
					console.log("Fetch user data "+data.status+": "+data.errorMsg);
				}
			}, "json");
			
		} else if(loginStatus === "failed"){
			// Login unsuccessful - Update the page
			
		} else if(loginStatus === "not_connected"){
			// Refresh the page
			document.location.reload();
		} else {
			console.log("UI not updated: login status not given");
		}
	});

	// Share button - Show the share dialog
	$("main").on("click", ".ShareEvent", function(){
		// Retrieve values
		var ChallengeID = $(this).closest(".Card").data("challengeid");
		var Title = $(this).closest(".Card").data("title");
		
		// Show the share dialog
		$("#URL").html("Generating URL...");
		$("#ShareDialog, #Backdrop").fadeIn(300);
		
		// Build a link and save it on the sever
		$.post("php/BuildLink.php", { challenge : ChallengeID }, function(data, status){
			// If URL was successfully returned
			if(status === "success"){
				// Log the URL
				console.log("URL created:"+data.URL);
				
				// Set the share dialog values
				$("#URL").html(data.URL);
				$("#URLtitle").html(data.URL);
				$("#FB_URL").attr("href", "http://www.facebook.com/sharer/sharer.php?u="+data.URL+"&title="+Title);
				$("#Twitter_URL").attr("href", "http://twitter.com/intent/tweet?status="+Title+"+"+data.URL);
				$("#CopyURLEvent").attr("href", data.URL);
			} else {
				// Log the error
				console.log("There was an error generating a new URL.");
			}
		}, "json");
	});

	
	// URL Copy - Copies the URL in a hidden textbox
	$(".Dialog").on("click", "#CopyURLEvent", function(e){
		e.preventDefault();
		if(CopyURL()){
			$("#CopyURLEvent").html("<i class=\"fa fa-clipboard\"></i> Copied!");
		} else {
			$("#CopyURLEvent").html("<i class=\"fa fa-clipboard\"></i> Sorry, copying not supported");
		}
	});
	
	
	// Copy function
	function CopyURL(){
		// Set the properties
		var result = false;
		var copyBox = $("#URL");
		
		// Select the textbox containing the URL
		copyBox.select();
		
		// Try coping the text in the textbox
		try {
			var successful = document.execCommand('copy');
			var msg = successful ? 'successful' : 'unsuccessful';
			console.log('Copying the URL was ' + msg);
			if(successful){
				result = true;
			}
		} catch (err) {
			console.log('Unable to copy');
		}
		
		// Unselect the textbox
		copyBox.blur();
		
		// Return the result
		return result;
	}
	
	// Validate an input
	$.fn.validateInput = function(type, allowNull) {
		var value = $(this).val();
		var erred = false;
		
		// Check for null value
		if(value === (null || "")){
			// If null ensure null value is allowed
			if(allowNull !== ("null" || true)){
				erred = true;
			}
		} else {
			// Validate the input depenting on the type given
			switch(type){
				case "text":
					// must be between 1 and 255 charcters
					erred = (value === null || value === "" || value.length > 255);
					break;
				case "long":
					// must be between 1 and 255 charcters
					erred = (value === null || value === "" || value.length > 3000);
					break;
				case "date":
					// must have a valid address
					erred = (value === null);
					break;
				case "file":
					// must have a valid address
					erred = (value === null || value === "");
					break;
				case "currency":
					// must have a valid address
					var currencyRegex = /(?=.)^\$?(([1-9][0-9]{0,2}(,[0-9]{3})*)|[0-9]+)?(\.[0-9]{1,2})?$/;
					erred = (!currencyRegex.test(value));
					break;
				case "cc-stripe":
					// Card must not be empty
					erred = (value === null || value === "");
					break;
				case "cc-name":
					// Name must not be empty
					erred = (value === null || value === "");
					break;
				case "cc-number":
					// Stripe cc number validation
					erred = !$.payment.validateCardNumber(value);
					break;
				case "cc-exp":
					// Stripe cc expiry validation
					erred = !$.payment.validateCardExpiry($.payment.cardExpiryVal(value));
					break;
				case "cc-cvc":
					// Stripe cc cvc validation
					erred = !$.payment.validateCardCVC(value);
					break;
				case "email":
					// Email must match the following regex
					var emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
					erred = (value ==="" || !emailRegex.test(value));
					break;
				case "post-code":
					// Post code must match the following regex
					var zipRegex = /^(\d{4,5})$/;
					erred = (value === null || value ==="" || !zipRegex.test(value));
					break;
				default:
					// Invalid validdation type given
					throw "Invalid validation type";
			}
		}

		// Toggle the Input error
		$(this).toggleInputError(erred);

		// Return result
		return !erred;
	};

	// Toggle wether an input displays an error or not.
	$.fn.toggleInputError = function(erred) {
		this.parents('.input').toggleClass('invalid', erred);
		return this;
	};
	
	// Format an input for a certain type.
	$.fn.format = function(type) {
		switch(type){
			case "currency":
				$(this).on("keyup", function(){
					$(this).val(parseFloat($(this).val(), 10).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,").toString());
				});
				break;
			default:
				// Invalid validdation type given
				throw "Invalid format";
		}
		return this;
	};

});


