// JavaScript Document
Stripe.setPublishableKey('pk_test_vgNLnQ0gC91FmNyDDy4CinH9'); // Stripe publishable key


$(function() {
  var $form = $('#payment-form');
  $form.submit(function(event) {
	  console.log("Generate Token.");
    // Disable the submit button to prevent repeated clicks:
    $('#ConfirmDonation').prop('disabled', true);

    // Request a token from Stripe:
    Stripe.card.createToken($form, stripeResponseHandler);

    // Prevent the form from being submitted:
    return false;
  });
});


function stripeResponseHandler(status, response) {
	// Grab the form:
	var $form = $('#payment-form');
  
	if (response.error) { // Problem!
		console.log("Token error.");
    
		// Show the errors on the form:
    	$form.find('.payment-errors').text(response.error.message);
    	$('#ConfirmDonation').prop('disabled', false); // Re-enable submission

	} else { // Token was created!
		console.log("Token Generated.");
		
		// Get the token ID:
		var token = response.id;

    	// Insert the token ID into the form so it gets submitted to the server:
    	$form.append($('<input type="hidden" name="stripeToken">').val(token));

    	// Submit the form:
    	//$form.get(0).submit();
	}
};



























