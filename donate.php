<?php

session_start();
require($_SERVER["DOCUMENT_ROOT"].'/php/Redirect.php'); // Redirecting while under construction
require($_SERVER["DOCUMENT_ROOT"].'/lib/X-ClassStructures.php'); //Charity-X

// Get the Link
if(!empty($_GET['l'])){
	$Link = new Link($_GET['l']);
	$Link->Sync();
} else {
	header("Location: http://www.charity-x.org");
}

// Get the Challenge
if(isset($Link->Challenge)){
	$Link->Challenge->Sync();
	$Challenge = $Link->Challenge;
	if($Link->Active == 0) $Link->SetActive();
} else {
	header("Location: http://charity-x.org");
}

// Get the Link Owner
if(isset($Link->User)){
	$Link->User->Sync();
	$LinkOwner = $Link->User;
} else {
	$LinkOwner = null;
}

// Test login
if(isset($_SESSION['UserID'])){
	$User = new User($_SESSION['UserID']);
	$User->Sync();
	$Cards = StripeCards::OwnedBy($User->ID);
	$ExistingCards = (count($Cards) >= 1)? true : false;
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
	<meta name="format-detection" content="telephone=no"/>
	<meta name="theme-color" content="#C74C4E" />
	<meta http-equiv="cache-control" content="no-cache">

<!--  Open Graph - This is what is displayed when page is shared on Social media -->
	<meta property="og:type" content="website" />
	<meta property="og:site_name" content="Charity-X" />
	<meta property="og:url" content="http://<? echo $Link->URL; ?>" />
	<meta property="og:title" content="<? echo $Challenge->Title; ?>" />
	<meta property="og:description" content="Support <? echo $Link->User->FirstName; ?>. $2 is all it takes to do greatness." />
	<meta property="og:image" content="http://charity-x.org/images/<? echo $Challenge->Image; ?>" />
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />

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
	<script src="js/donate.js"></script><!--page specific functions-->
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
			<li class="HiddenOnLogout"><a href="#" class="LogoutEvent"><i class="fa fa-user"></i> Logout</a></li>			
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
			<li class="HiddenOnLogout"><a href="#" class="LogoutEvent"><i class="fa fa-user"></i> Logout</a></li>
		</ul>
	</menu>	
	
<!-- Donate Page data -->


<!-- Main content area - This is where the challenges (or search results) are displayed -->
	<main>
	<!-- App Bar Spacer - This ensures content is displayed below the App Bar (not behind it) -->
		<div class="AppBarSpacer-Single"></div>
		
	<!-- Challenge Info Section -->
		<section>
		<!-- Challenge image -->
			<div class="RichMedia"><div>
				<img src="images/<? echo $Challenge->Image; ?>">
			</div></div>
		
		<!-- Challenge Title -->
			<div class="Header">
				<div class="Title"><? echo $Challenge->Title; ?></div>
				<div class="Subhead"><? if($Challenge->Target != 0) echo "Target: $".number_format($Challenge->Target); ?></div>
			</div>
			
		
			
		<!-- Challenge Description -->
			<div class="SupportingText">
				<p><? echo $Challenge->Description; ?></p>
			</div>
			
		<!-- Shared by info -->
			<? 
			if(isset($LinkOwner)){
				echo ("<div class=\"SupportingText\" ><p>Shared by: $LinkOwner->Name </p></div>");
			}
			?>
			
		</section>
	
	
	<!-- Donation Amount -->
		<section class="Section">
			<button class="Raised AmountButton" data-amount="2.00">$2</button>
			<button class="Raised AmountButton Selected" data-amount="5.00">$5</button>
			<button class="Raised AmountButton" data-amount="10.00">$10</button>
			<button class="Raised AmountButton" data-amount="20.00">$20</button>
			<button class="Raised AmountOther" data-amount="other">other</button>
			<div class="text" id="AmountText">
				<label>Amount</label>
				<input type="tel" id="AmountInput" value="$5.00">
				<span class="inputError"></span>
			</div>
		</section>

	<!-- Points rewarded for donation -->
		<section class="PointsDisplay">
			<div class="label">SOCIAL SCORE POINTS</div>
			<div class="score_val">48</div>
		</section>

	<!-- Facebook Login card - Is hidden when already logged in -->	
		<section class="Card Login Blue <? if(!isset($User)) echo 'HiddenOnLogin'; else echo 'Hidden'; ?>">
			<div class="Header">
				<div class="Title">Login to redeem your points (optional)</div>
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


	<!-- Select Credit Card for Payment -->
		<section id="CardOptions" class="Card <? if(!$ExistingCards) echo 'Hidden'; ?>" >
			<? foreach($Cards as $Card){
				echo ('<label class="CardOption" for="'.$Card->ID.'">
					<div class="Option checkbox"><input type="radio" name="CardOption" id="'.$Card->ID.'" value="'.$Card->ID.'"><label for="'.$Card->ID.'"></label></div>
					<div class="Number"><i class="fa fa-cc-'.$Card->Type.'"></i> •••• •••• •••• '.$Card->Last4.'</div>
				</label>');
			} ?>
			<label class="CardOption" for="OtherCardAction">
				<div class="Option checkbox"><input type="radio" name="CardOption" id="OtherCardAction" value="new" <? if(!isset($User)) echo checked; ?> ><label for="OtherCardAction"></label></div>
				<div class="Number">Use a different card</div>
			</label>
		</section> <!-- END Select Credit Card for Payment -->


	<!-- Payment Section -->
		<section class="Card <? if($ExistingCards) echo 'Hidden'; ?>" id="PaymentFormCard">
			<div class="Header">
				<div class="Icon"><i class="fa fa-cc-visa Blue"></i><i class="fa fa-cc-mastercard Red"></i><i class="fa fa-cc-amex Green"></i></div>
				<div class="Title Centered">Secure Donation</div>
				<div class="Subhead"><i class="fa fa-exclamation-circle"></i> <b>TEST MODE!</b> To complete this form use one of the <a href="https://stripe.com/docs/testing#cards" target="_blank">test card numbers</a> such as 4242 4242 4242 4242 and dumby data. No reciept will be sent.
				</div>
			</div>
			<form action="/php/AuthoriseDonation.php" method="POST" id="payment-form" class="PaymentForm" autocomplete="on">

				<div class="input-group input text">
					<label for="Data-Email">Email (for reciept)</label>
					<input type="email" name="Email" id="Data-Email" data-stripe="email" autocomplete="email" value="<? echo $User->Email; ?>" required>
					<span class="inputError">Must be a valid email address</span>
				</div>

				<div class="input-group input text">
					<label>Name on Card</label>
					<input type="text" name="CardName" id="Data-CardName" data-stripe="name" autocomplete="cc-name" required>
					<span class="inputError">The name on your card must be given</span>
				</div>

				<div class="input-group input text">
					<label for="Data-CardNumber">Card Number</label>
					<input type="tel" id="Data-CardNumber" data-stripe="number" autocomplete="cc-number" placeholder="•••• •••• •••• ••••" required>
					<span class="inputError">Must be a valid credit card number</span>
				</div>

				<div class="input-group input text half">
					<label for="Data-CardExp">Expiry Date</label>
					<input type="tel" id="Data-CardExp" data-stripe="exp" autocomplete="cc-exp" placeholder="•• / ••" required>
					<span class="inputHelp">mm/yy</span>
					<span class="inputError">The expiry date cannot have expired</span>						
				</div>

				<div class="input-group input text half">
					<label for="Data-CardCVC">CVV</label>
					<input type="tel" id="Data-CardCVC" data-stripe="cvc" autocomplete="off" placeholder="•••" required>
					<span class="inputHelp">3 digit code on the back of your card</span>
					<span class="inputError">CVV must be valid</span>
				</div>

				<div class="input-group input text">
					<label>Postal Code (for security)</label>
					<input type="text" id="Data-PostCode" data-stripe="address_zip" autocomplete="postal-code" placeholder="••••" required>
					<span class="inputError">Postal code must valid</span>
				</div>

				<div class="input-group input toggle-switch">
					<input type="checkbox" name="SaveCard" id="Data-SaveCard" <? if(isset($User)) echo 'checked'; else echo 'disabled'; ?> >
					<label for="Data-SaveCard">Save Card <? if(!isset($User)) echo '(must be logged in)'; ?></label>
					<span class="inputError"></span>
				</div>

				<input type="hidden" name="LinkID" id="Data-LinkID" value="<? echo $Link->ID; ?>">
				<input type="hidden" name="Amount" id="Data-Amount" value="5.00">
				<input type="hidden" name="ExistingCustomer" id="Data-ExistingCustomer" value="<? if($ExistingCards) echo 'true'; else 'false'; ?>">
				<input type="hidden" name="ExistingCustomerID" id="Data-ExistingCustomerID" value="<? if($ExistingCards) echo $Cards[0]->ID; ?>">
			</form>
		</section><!-- END Payment Section -->
		
		
		
	<!-- Confirm Donation Button -->
		<section>
			<div class="Section">
				<button class="Raised Large Block Green" id="ConfirmDonation"><i class="fa fa-heart"></i> Confirm Support</button>
				<span id="DonateError"></span>
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
	
	
</div></body> <!-- END container-fluid and body-->
</html>