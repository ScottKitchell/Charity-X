<?php
session_start();
require_once('php/Redirect.php'); // Redirecting while under construction
require($_SERVER["DOCUMENT_ROOT"].'/lib/X-ClassStructures.php'); //Charity-X


if(isset($_GET['reciept'])){
	
	$Donation = new Donation(Sanitize::Int($_GET['reciept']));
	if(!$Donation->Sync())
		header('location:http://charity-x.org/'); // Invalid reciept
	$Donation->Link->Sync();
	if(isset($Donation->User))$Donation->User->Sync();
	$Donor = $Donation->User;
	$Donation->Challenge->Sync();
	$Challenge = $Donation->Challenge;
	$Points = $Donation->Points->Points;
	
} else {
	header("Location: http://www.charity-x.org");
}

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
	<title>Charity-X reciept <? echo $Donation->ID; ?></title>
	<meta name="description" content="Thankyou for donating to <? echo $Donation->Title; ?>">
	<meta name="keywords" content="give, donate, charity, support, impact, help, depressed, happy">
	<link rel="icon" type="image/x-icon" href="http://charity-x.org/favicon.png" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="format-detection" content="telephone=no"/>
	<meta name="theme-color" content="#C74C4E" />
	<meta http-equiv="cache-control" content="no-cache">

<!--  jQuery  -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

<!-- Stripe - Secure payment processing -->
	<script type="text/javascript" src="https://js.stripe.com/v2/"></script> <!-- Stripe API -->
	<script type="text/javascript" src="lib/jquery.payment.min.js"></script>
	
<!--  Bootstrap - To make pages responsive for mobile, tablet or desktop-->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<!--  Stylesheet  -->
	<link rel="stylesheet" type="text/css" href="css/PageStyles.css" /> <!-- CSS for specific page styles -->
	<link rel="stylesheet" type="text/css" href="css/UI.css" /> <!-- CSS for material UI style - DO NOT EDIT - See material.google.com for details-->
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,500" rel="stylesheet"> <!-- Font: Roboto -->

<!--  JavaScripts  -->
	<script src="js/general.js"></script><!--general functions-->
	<script src="js/FB_login.js"></script><!--Facebook login-->

</head>

<body data-login-status="<? echo isset($User)? "connected" : "not_connected"; ?>" data-auto-login="<? echo $_COOKIE["AutoLogin"]; ?>">
<div class="container-fluid">

	<header class="AppBar">
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

	<input type="hidden" name="DonorID" id="Data-DonorID" value="<? echo $Donor->ID; ?>">
	<input type="hidden" name="LinkID" id="Data-LinkID" value="<? echo $Link->ID; ?>">
	<input type="hidden" name="Amount" id="Data-Amount" value="<? echo $Donation->Amount; ?>">
	<input type="hidden" name="Points" id="Data-Points" value="<? echo $Points; ?>">


	<main>
		<section>
			<div class="AppBarSpacer-Single"></div>

			<div class="RichMedia"><div>
				<img src="images/<? echo $Challenge->Image; ?>">
			</div></div>

			<div class="Header">
				<div class="Title">Today is a good day!</div>
				<div class="Subhead">We've emailed you a reciept.</div>
			</div>

			<div class="SupportingText">
				<p>Thanks<? if(isset($Donor->FirstName)) echo " ".$Donor->FirstName; ?>, your donation to <strong><? echo $Challenge->Title; ?></strong> will go a long way in overcoming the challenge. </p>
				<div class="PointsDisplay">
					<div class="label">Points Earnt</div>
					<div class="score_val"><? echo $Points; ?> points</div>
				</div>
			</div>
		</section>
		
		<!-- Facebook Login card - Is hidden when already logged in -->	
		<section class="Card Login Blue <? if(!isset($User)) echo 'HiddenOnLogin'; else echo 'Hidden'; ?>">
			<div class="Header">
				<div class="Title">It's not too late to redeem those points</div>
			</div>
			<div class="SupportingText">
				<div class="col-xs-4 Centered">
					<p><i class="fa fa-trophy fa-3x"></i></p>
					<p>Compete with friends by donating and sharing challenges like this one.</p>
				</div>
				<div class="col-xs-4 Centered">
					<p><i class="fa fa-gift fa-3x"></i></p>
					<p>Get updated on the impact made thanks to you and your friends efforts.</p>
				</div>
				<div class="col-xs-4 Centered">
					<p><i class="fa fa-credit-card fa-3x"></i></p>
					<p>Store your credit card details for added protection and convenience.</p>
				</div>
			</div>
			<div class="Section">
				<button class="Flat Large Block Blue" onClick="TryLogin()"><i class="fa fa-facebook-official"></i> Login with Facebook</button>
			</div>
		</section><!-- END Facebook Login Card -->
		
		<!-- Next Action Card -->
		<section class="Card">
			<div class="Header">
				<div class="Title">Where to now?</div>
			</div>
			<div class="SupportingText">
				<div class="col-xs-4"><button class="Flat ShareEvent" >
					<p><i class="fa fa-share-alt fa-3x Green"></i></p>
					<p>Share to earn points per donation</p>
				</a></div>
				<div class="col-xs-4"><a class="Button Flat Block" href="http://charity-x.org">
					<p><i class="fa fa-globe fa-3x Blue"></i></p>
					<p>See all the challenges</p>
				</a></div>
				<div class="col-xs-4"><a class="Button Flat Block" href="http://charity-x.org/donate.php?l=6115118335">
					<p><i class="fa fa-heart fa-3x Red"></i></p>
					<p>Help support Charity-X (Double Points!)</p>
				</a></div>
			</div>
		</section>
		
	</main> <!-- END main content area -->
 
   
<!-- Backdrop - This is a full page object responsible for darkening the screen when a menu appears -->
	<div class="Backdrop" id="Backdrop"></div>
   
<!-- Share Card Dialog - This pop-up dialog box provides sharing options (URL, Facebook, Twitter) when a user wants to share a challenge -->
	<section class="Card Dialog" id="ShareDialog">
	<!-- Card Dialog Header - Displays the title -->
		<div class="Header">
			<div class="Title">A link just for you!</div> <!-- The Dialog Title -->
			<div class="Subhead"><span id="URLtitle">Generating URL...</span></div> <!-- The Dialog Subtitle displaying the raw URL -->
		</div>
	<!-- Sharing options -->
		<div class="Section">
			<div class="Subsection"><button class="Raised Block js-textareacopybtn" id="CopyURLEvent"><i class="fa fa-clipboard"></i> Copy URL</button></div>
			<div class="Subsection"><a class="Button Raised Block fb" id="FB_URL" target="_blank" ><i class="fa fa-facebook"></i> Share on Facebook</a></div>
			<div class="Subsection"><a class="Button Raised Block twitter" id="Twitter_URL" target="_blank" href=""><i class="fa fa-twitter"></i> Share on Twitter</a></div>
			<textarea class="copyBox" id="URL">http://charity-x.org</textarea>
		</div>
	</section> <!-- END Share card dialog -->

</div>
</body>
</html>