
/*****  FACEBOOK LOGIN AND SDK  *****/


$(function() {
	"use strict";
	
	// Initialize User object
	var User = {
		ID: null,
		FirstName: null,
		LastName: null,
		Image: null,
		Score: null,
		Status: "not_connected"
	};
	
	// Initialize the JavaScript SDK
	window.fbAsyncInit = function() {
		FB.init({
			appId	: '1688330948162569',
			cookie	: true,  // enable cookies to allow the server to access the session
			 xfbml	: true,  // parse social plugins on this page
			version	: 'v2.8' // use graph api version 2.8
		});

		// Now that we've initialized the JavaScript SDK, we test if we can login.
		AutoLoginTest();
	};


	// Load the SDK asynchronously
	(function(d, s, id){
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) {return;}
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/en_US/sdk.js";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));

	
	// When the SDK loads this will be called.
	function AutoLoginTest(){
		if($("body").data("login-status") === "connected"){
			// The user is already logged in
			console.log("Login status: logged in");
			$(document).trigger("loginStatusChange", "connected");
		} else if($("body").data("auto-login") === "true") {
			// The user is not logged in but auto-login is set to true
			TryLogin();
		} else {
			// The user is not logged in
			console.log("Login status: not logged in");
			FB.getLoginStatus(function(response) {
				console.log("Facebook login status: " + response.status);
			});
		}
	}


	//
	// Attempt to log the user in
	//
	function TryLogin(){
		console.log('Logging in... ');

		// Login with Facebook
		FB.login(function(response) {
			// Log the result
			console.log('Facebook login status: ' + response.status);

			// If Facebook is logged in and has authorised the app
			if (response.status === 'connected') {
				// Process Login on the server
				$.post("php/FB_login.php", function(data, status){
					if(status === "success" && data.status === "success"){
						// Logged in
						console.log("Login status: logged in");
						$(document).trigger("loginStatusChange", "connected");
					} else { 
						// Not Logged in
						console.log("Login "+status+": " + data.errorMsg);
						console.log("Login status: not logged in");
						$(document).trigger("loginStatusChange", "failed");
					} 
				}, "json");
			} else if (response.status === 'not_authorized') {
				// The person is logged into Facebook, but not your app.
				console.log("Login failed: could not authorise Facebook permissions");
				console.log("Login status: not logged in");

			} else {
				// The person is not logged into Facebook, so we're not sure if
				// they are logged into this app or not.
				console.log("Login failed: unknown");
				console.log("Login status: not logged in");
			}
		}, {scope: 'public_profile,email,user_friends'});
	}


	//
	// Attempt to log the user out
	//	
	function TryLogout(){
		console.log('Logging out... ');
		$.post("php/FB_logout.php", function(data){ 
			console.log('Logout: ' + data.status);
			if(data.status === "success"){ 
				// user is now logged out
				$(document).trigger("loginStatusChange", "not_connected" );
			}
		}, "json");
	}
	
	
	//
	// Custom Login Button trigger
	//
	$(".LoginEvent").on("click", function(e) {
		e.preventDefault();
		TryLogin();
	});


	//
	// Custom Logout Button trigger
	//
	$(".LogoutEvent").on("click", function(e) {
		e.preventDefault();
		TryLogout();
	});
	
	
});



