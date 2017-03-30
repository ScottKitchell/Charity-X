
/*****  NEW CHALLENGE PAGE FUNCTIONS  *****/


$(function() {
	"use strict";
	
	//$("#Data-Target").format("currency");
	
	//Trigger image upload
	$("#FileSelectButton").click(function(){
		$("#FileSelect").click();
	});
	// Trigger for submit challenge
	$("#ConfirmChallenge").click(function(){
		$(document).trigger("SubmitChallenge");
	});
	$('#newChallengeForm').submit(function(e) {
		e.preventDefault();
	});
	$("#FileSelect").submit(function(e) {
		e.preventDefault();
	});
	
	
	// Confirm challenge
	$(document).on("SubmitChallenge", function() {
		console.log("Submitting new challenge...");
		
		// Display loading
		$('body').css( 'cursor', 'progress' );
		// Disable the submit button to prevent repeated clicks:
		$('#ConfirmChallenge').prop('disabled', true);
		$("#FormError").html("");
		
		// Validate Inputs
		var ErrorCount = 0;
		console.log("Validating challenge fields...");
		
		// Validate Title
		if(!$("#Data-Title").validateInput("text")){ ErrorCount++; }	
		// Validate Description
		if(!$("#Data-Description").validateInput("long")){ ErrorCount++; }
		// Validate Card Expiry
		if(!$('#Data-Image').validateInput("file")){ ErrorCount++; $("#FileSelect").toggleInputError(true);} else { $("#FileSelect").toggleInputError(false); }
		// Validate Target amount
		if(!$("#Data-Target").validateInput("currency", "null")){ ErrorCount++; }
		// Validate Charitys
		if(!$("#Data-Charity1").validateInput("text")){ ErrorCount++; }
		if(!$("#Data-Charity2").validateInput("text", "null")){ ErrorCount++; }
		if(!$("#Data-Charity3").validateInput("text", "null")){ ErrorCount++; }
		// Validate Start Date
		if(!$("#Data-StartDate").validateInput("text")){ ErrorCount++; }
		// Validate Start Date
		if(!$("#Data-StartDate").validateInput("text", "null")){ ErrorCount++; }

		console.log("Validating complete: "+ErrorCount+" errors found.");
		// If errors were found stop processing donation
		if(ErrorCount > 0){
			$('body').css( 'cursor', 'default' );
			$('#ConfirmChallenge').prop('disabled', false);
			return false;
		}
		// All fields are validated
		processChallenge();
		
	});
	
	function processChallenge(){
		console.log("Processing new challenge...");
		// Create the challenge and save it on the sever
		$.post("php/CreateChallenge.php", { 
			// Challenge prporeties
			title: $("#Data-Title").val(),
			description: $("#Data-Description").val(),
			image: $('#Data-Image').val(),
			target: $("#Data-Target").val(),
			charity1: $("#Data-Charity1").val(),
			charity2: $("#Data-Charity2").val(),
			charity3: $("#Data-Charity3").val(),
			startDate: $("#Data-StartDate").val(),
			endDate: $("#Data-EndDate").val()
		}, function(data, status){
			// If URL was successfully returned
			if(status === "success" && data.status === "success"){
				console.log("New Challenge: "+data.status);
				window.location.href = "http://charity-x.org?l="+data.URL;
			} else {
				// Log the error
				console.log("New Challenge failed: "+data.errorMsg);
				$("#FormError").html(data.errorMsg).show();
				$('body').css( 'cursor', 'default' );
				$('#ConfirmChallenge').prop('disabled', false);
			}
		}, "json");
	}
	
	
	$("#FileSelect").on('change',function(e){
		e.preventDefault();
		
		// Show loading
		$("body").css('cursor', 'progress');
		$("#FileSelectButton").prop('disabled', true);
		$("#previewImage").html('<div class="loading"><i class="fa fa-smile-o fa-spin fa-3x fa-fw"></i><br>Uploading</div>');
		
		var fileData = new FormData($("#ImageForm")[0]);    
		$.ajax({
			url: 'php/UploadImage.php',
			type: 'POST',
			data: fileData,
			dataType: "json",
			processData: false,
			contentType: false,
		}).done(function(response){
			if(response.status === "success"){
				console.log("Image upload: "+response.status);
				$("#previewImage").html('<img src="images/'+response.imageName+'" >');
				$("#Data-Image").val(response.imageName);
				$('#FileSelectButton').html('<i class="fa fa-picture-o"></i> Change picture');
			} else {
				$("#previewImage").html('<img src="images/challenge.jpg">');
			}
		}).always(function(){
			$('body').css('cursor', 'default');
			$('#FileSelectButton').prop('disabled', false);
		});
	});
		


	
});