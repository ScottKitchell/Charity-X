<?php
session_start();
require_once('php/Redirect.php'); // Redirecting to landing page while under construction
require_once($_SERVER["DOCUMENT_ROOT"].'/lib/X-ClassStructures.php'); // Require class structures

// Test login
if(isset($_SESSION['UserID'])){
	$User = new User($_SESSION['UserID']);
	$User->Sync();
}
?>

<!doctype html>
<html prefix="og: http://ogp.me/ns#">

<head>
<!--  Meta data  -->
	<meta charset="utf-8">
	<title>Charity-X</title>
	<meta name="description" content="Share your impact with Charity-X">
	<meta name="keywords" content="give, donate, charity, support, impact, help, depressed, happy">
	<link rel="icon" type="image/x-icon" href="http://charity-x.org/favicon.png" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#C74C4E" />

<!--  Open Graph - This is what is displayed when page is shared on Social media -->
	<meta property="og:title" content="Share your impact with Charity-X" />
	<meta property="og:description" content="Band together with your friends to promote a cause. Be a part of something bigger than yourself. Create the biggest impact that you can." />
	<meta property="og:image" content="http://www.charity-x.org/images/showeveryoneyoucare.jpg" />
	<meta property="og:type" content="website" />
	<meta property="og:site_name" content="Charity-X" />
	<meta property="og:url" content="http://www.charity-x.org" />

<!--  Include jQuery  -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

<!--  Include Bootstrap - To make pages responsive for mobile, tablet or desktop-->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<!--  Include Stylesheets  -->
	<link rel="stylesheet" type="text/css" href="css/PageStyles.css" /> <!-- CSS for specific page styles -->
	<link rel="stylesheet" type="text/css" href="css/UI.css" /> <!-- CSS for material UI style - DO NOT EDIT - See material.google.com for details-->
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,500" rel="stylesheet"> 

<!--  Include JavaScripts  -->
	<script src="js/general.js"></script><!--general functions-->
	<!--page specific functions-->
	<script src="js/FB_login.js"></script><!--Facebook login-->

</head>


<body data-login-status="<? echo isset($User)? "connected" : "not_connected"; ?>" data-auto-login="<? echo $_COOKIE["AutoLogin"]; ?>">
<div class="container-fluid">

<!-- App Bar - This is the fixed tool bar at the top of the page -->
 	<header class="AppBar">
 	<!-- Primary App Bar - This is the top section of the App bar displaying the menu icons and logo -->
		<div class="PrimaryAppBar">
			<div class="Left">
				<span id="AppBarMainActionArea">
					<div class="NavIcon" id="NavAction"><i class="fa fa-bars"></i></div>					
				</span>
				<span id="AppBarTitleArea">
					<div class="Logo" id="Logo"><a href="http://charity-x.org"><img src="images/Charity-X_Logo_Small.png" alt="Charity-X"></a></div>
				</span>
			</div>
			<div class="Right">
				<div class="ActionIcon" id="Challenges"><i class="fa fa-question"></i></div>
				<div class="ActionIcon" id="UserMenuAction"><i class="fa fa-user-circle"></i></div>
			</div>
		</div>
	</header>
	
<!-- Main navigation menu - This is the left side menu -->
	<nav class="Menu" id="NavSlider">
	<!-- Menu header - This is where user image and details are shown -->
		<div class="header">
			<span class="FirstName">Hi there!</span> <span class="LastName"></span>
		</div>
	
	<!-- Menu items - This is all the links -->
		<ul>
			<li class="HiddenOnLogin"><a href="#" class="LoginEvent"><i class="fa fa-user"></i> Login (Facebook)</a></li>
			<li><a href="index.php"><i class="fa fa-heart"></i> Challenges</a></li>
			<li><a href="achievements.php"><i class="fa fa-trophy"></i> Achievements</a></li>
			<li><a href="leaderboard.php"><i class="fa fa-flag"></i> Leaderboard</a></li>
			<div class="divider"></div>
			<li><a href="newchallenge.php"><i class="fa fa-plus-square"></i> Create a challenge</a></li>
			<div class="divider"></div>
			<li><a href="info.php"><i class="fa fa-question-circle"></i> How it works</a></li>			
			<li><a href="help.php"><i class="fa fa-comments"></i> Help and feedback</a></li>
			<div class="divider"></div>
			<li><a href="options.php"><i class="fa fa-cog"></i> Options</a></li>
			<li class="HiddenOnLogout"><a href="#" onclick="FB.logout();" class="LogoutEvent"><i class="fa fa-user"></i> Logout</a></li>			
		</ul>
	</nav>

<!-- User menu - This is the right side menu -->
	<menu class="Menu" id="UserMenuSlider">
		<div class="Subheader HiddenOnLogout">Logged in as <span class="FirstName"></span> <span class="LastName"></span></div>
		<ul>
			<li class="HiddenOnLogin"><a href="#" class="LoginEvent"><i class="fa fa-user"></i> Login (Facebook)</a></li>
			<li class="HiddenOnLogout"><a href="profile.php"><i class="fa fa-user"></i> View profile</a></li>
			<li class="HiddenOnLogout"><a  href="achievements.php"><i class="fa fa-trophy"></i> Achievements</a></li>
			<li class="HiddenOnLogout"><a href="history.php"><i class="fa fa-calendar"></i> History</a></li>
			<div class="divider HiddenOnLogout"></div>
			<li class="HiddenOnLogout"><a href="#" class="LogoutEvent" onclick="FB.logout();"><i class="fa fa-user"></i> Logout</a></li>
		</ul>
	</menu>	
	
	
<!-- Main content area - This is where the challenges (or search results) are displayed -->
    <main>
    <!-- App Bar Spacer - This ensures content is displayed below the App Bar (not behind it) -->
		<div class="AppBarSpacer-Single"></div>
		
		<section>
		<!-- Challenge Suggestions - Challenges are loaded asynchronously using the ChallengeSearch() function in index.js -->
			<div class="Header">
				<div class="Title">How it works</div>
				<div class="Subhead">PAGE NOT COMPLETE</div>
			</div>
		</section>
    </main><!-- END main content area -->

		
<!-- Backdrop - This is a full page object responsible for darkening the screen when a menu appears -->
	<div class="Backdrop" id="Backdrop"></div>

<!-- Card Dialog - This pop-up dialog box provides sharing options (URL, Facebook, Twitter) when a user wants to share a challenge -->
	<section class="Card Dialog" id="ShareDialog">
		<div class="Card">
		<!-- Card Dialog Header - Displays the title -->
			<div class="Header">
				<div class="Title">A link just for you!</div> <!-- The Dialog Title -->
				<div class="Subhead"><span id="URL"></span></div> <!-- The Dialog Subtitle displaying the raw URL -->
			</div>
		<!-- Sharing options -->
			<div class="Section">
				<div class="Subsection"><button class="Raised Block js-textareacopybtn" id="CopyURLEvent"><i class="fa fa-share"></i> Copy URL</button></div>
				<div class="Subsection"><button class="Raised Block fb"><i class="fa fa-facebook"></i> Share on Facebook</button></div>
				<div class="Subsection"><button class="Raised Block twitter"><i class="fa fa-twitter"></i> Share on Twitter</button></div>
				<textarea class="js-copytextarea" id="URL">http://charity-x.org</textarea>
			</div>
		</div>
	</section>
	
	
</div></body> <!-- END container-fluid and body-->
</html>