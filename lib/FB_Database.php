<?php

/*	
	This page contains the Facebook class structures 
	required to access data from Facebook. 
	
	Example of use:	
	$fb_request = '/me?fields=name, email;
	if(FB::Query($fb_request, $result)){
		$Email = $result['Email'];
		$Name = $result['Name'];
	}
	
	// See https://developers.facebook.com/docs/php/api/5.0.0 for details
*/

session_start();
require($_SERVER["DOCUMENT_ROOT"].'/config/config_fb.php');

//
// Facebook Database class
// Methods: Query(), Token(), SetJSToken()
//
class FB{
	private static $app_id = APP_ID;
	private static $app_secret = APP_SECRET;
	private static $default_graph_version = DEFAULT_GRAPH_VERSION;

	// Method used to querey Facebook
	public static function Query($fb_request, &$result = -1)
	{
		// Load the Facebook SDK API
		require_once('facebook-sdk-v5/autoload.php');
		
		// Create a new facebook object
		$fb = new Facebook\Facebook([
			'app_id' => self::$app_id,
			'app_secret' => self::$app_secret,
			'default_graph_version' => self::$default_graph_version,
		]);
		
		// Set the default status to false
		$status = false;
		
		// Get the access token
		$accessToken = $_SESSION['fb_access_token'];
		if(isset($accessToken)){
			// Try run the querey
			try {
				$response = $fb->get($fb_request, $accessToken);
				$status = true;
			} catch(Facebook\Exceptions\FacebookResponseException $e) {
				throw new Exception('Graph returned an error: ' . $e->getMessage(), 0, $e);
			} catch(Facebook\Exceptions\FacebookSDKException $e) {
				throw new Exception('Facebook SDK returned an error: ' . $e->getMessage(), 0, $e);
			}

			// Set the result 
			try{
				$result = $response->getGraphObject();
			} catch(Exception $e){
				throw new Exception("Error processing the request: ".$fb_request);
			}
			
		}
		
		// Return the success status
		return $status;
	}
	
	public static function SetAccessToken()
	{
		// Set default status to false
		$Set = false;
		
		// Load the Facebook SDK API
		require_once('facebook-sdk-v5/autoload.php');
		
		// Create a new facebook object
		$fb = new Facebook\Facebook([
			'app_id' => self::$app_id,
			'app_secret' => self::$app_secret,
			'default_graph_version' => self::$default_graph_version,
		]);

		// Access the Facebook JavaScript helper
		$helper = $fb->getJavaScriptHelper();

		// Try to retreive the user access token
		try
		{
		  $accessToken = $helper->getAccessToken();
		} 
		catch(Facebook\Exceptions\FacebookResponseException $e)
		{
		  // When Graph returns an error
		  echo 'Graph returned an error: ' . $e->getMessage();
		  return Set;
		} 
		catch(Facebook\Exceptions\FacebookSDKException $e)
		{
		  // When validation fails or other local issues
		  echo 'Facebook SDK returned an error: ' . $e->getMessage();
		  return Set;
		}
		
		// Test is access token is set
		if(isset($accessToken)) // Logged in
		{
			// Store the access token in a session variable
			$_SESSION['fb_access_token'] = (string)$accessToken;

			// Format result
			$Set = true;
		} 
		else // Not logged in
		{ 
			// Store the login status in a session variable
			unset($_SESSION['fb_access_token']);
			
			// Format result
			$Set = false;
		}
		
		return $Set;
	}
	
	public static function UnsetAccessToken(){
		$Unset = false;
		// Unset the access token
		unset($_SESSION['fb_access_token']);
		
		if(!isset($_SESSION['fb_access_token'])) // Logged in
		{
			// Format result
			$Unset = true;
		} 
		else // Not logged in
		{ 
			// Format result
			$Unset = false;
		}
		
		return $Unset;
	}
	
} // END FB class

?>