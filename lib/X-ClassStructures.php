<?php 

/*	
	This page contains the class structures
	for all Charity-X objects.
*/

session_start();
require($_SERVER["DOCUMENT_ROOT"].'/lib/MySQLi.php'); // Used to simplify database access


//
// CLASS: User
// METHODS: __construct(), Sync(), FB_Sync(), Save(), AddPoints(), __toString()
//
class User{
	public $ID;
	public $Email;
	public $Name;
	public $FirstName;
	public $LastName;
	public $AgeRange;
	public $FBLink;
	public $Gender;
	public $Locale;
	public $Picture;
	public $Timezone;
	public $Verified;
	public $Score;
	public $Friends;
	public $StripeCard;
	public $DateUpdated;
	public $DateJoined;
	
	//
	// Constructor.
	//
	public function __construct($id = null) {
		$this->ID = isset($id) ? $id : ID::create();
		$this->Score = 0;
    }
	
	//
	// Retrieve user profile data using our database. 
	//
	public function Sync(){
		// Construct the SQL to select the User with this ID
		$SQL = new SQL("SELECT Email, Name, FirstName, LastName, FBLink, Picture, Score, StripeID, DateUpdated, Datejoined FROM Users WHERE ID = '$this->ID'");
		
		// Query the database
		if($status = DB::Query($SQL, $result)){
			$this->Email = $result[0]['Email'];
			$this->Name = $result[0]['Name'];
			$this->FirstName = $result[0]['FirstName'];
			$this->LastName = $result[0]['LastName'];
			$this->FBLink = $result[0]['FBLink'];
			$this->Picture = $result[0]['Picture'];
			$this->Score = $result[0]['Score'];
			$this->StripeCard = new StripeCard($result[0]['StripeID']);
			$this->DateUpdated = $result[0]['DateUpdated'];
			$this->DateJoined = $result[0]['DateJoined'];
		}

		return $status;
    }
	
	//
	// Retrieve user profile data using the facebook API.
	//
	public function FB_Sync(){
		// Require the Facebook API
		require_once($_SERVER["DOCUMENT_ROOT"].'/lib/FB_Database.php');
		
		// Construct the FB request to get the user values 
		$fb_request = '/me?fields=id, name, email, first_name, last_name, picture.width(200).height(200), gender, link, locale, timezone';
		
		// Query FB
		if($status = FB::Query($fb_request, $result)){
			// Store the results.
			$pic = json_decode($result['picture'], true);
			$this->ID = $result['id'];
			$this->Email = $result['email'];
			$this->Name = $result['name'];
			$this->FirstName = $result['first_name'];
			$this->LastName = $result['last_name'];
			$this->Picture = $pic['url'];
			$this->Gender = $result['gender'];
			$this->FBLink = $result['link'];
			$this->Locale = $result['locale'];
			$this->TimeZone = $result['timezone'];
		}
		
		// Return the status
		return $status;
	}
	
	//
	// Save not-null user profile data to our database. 
	//
	public function Save(){
		// Construct the SQL to insert this user
		$SQL = new SQL("INSERT INTO Users (ID, Email, Name, FirstName, LastName, FBLink, Picture, Score, StripeID, DateUpdated, DateJoined) VALUES ('$this->ID', '$this->Email', '$this->Name', '$this->FirstName', '$this->LastName', '$this->FBLink', '$this->Picture', '$this->Score', '$this->StripeCard', NOW(), NOW()) 
		ON DUPLICATE KEY UPDATE 
		Email=".Coalesce($this->Email,'Email').", 
		Name=".Coalesce($this->Name,'Name').", 
		FirstName=".Coalesce($this->FirstName,'FirstName').", 
		LastName=".Coalesce($this->LastName,'LastName').", 
		FBLink=".Coalesce($this->FBLink,'FBLink').", 
		Picture=".Coalesce($this->Picture,'Picture').", 
		StripeID=".Coalesce($this->StripeCard,'StripeID').", 
		DateUpdated=NOW()");
		
		// Query the database
		return DB::Query($SQL);
    }
	
	//
	// Add points to the user profile and save to our database.
	//
	public function AddPoints($points){
		// Construct the SQL to update this user's points
		$SQL = new SQL("UPDATE Users SET Score = Score + ".$points.", DateUpdated = NOW() WHERE ID = '$this->ID'");		
		
		// Query the database
		return DB::Query($SQL);
	}
	
	//
	// Returns the ID when the class object is used as a string.
	//
	public function __toString(){
		 return (string)$this->ID;
	}
	
} // END User



//
// CLASS: Friends
// DETAILS: A list of user profiles who are the friends of a user.
// METHODS: Add(), Sync()
//
class Friends{
	public $List;
	public $Number;
	
	//
	// Constructor.
	//
	public function __construct($array = null){
		$this->List = array();
		$this->Number = 0;
		if($array != null){
			foreach($array as $f_id){
				$this->Add(new User($f_id));
			}
		}
	}
	
	//
	// Add a friendship connection to the friend list
	//
	public function Add(User $friend){
		array_push($this->List, $friend);
	}
	
	//
	// Retrieve user profile data for each friend using our database.
	//
	public function Sync(){
		foreach($this->List as $Friend){
			$Friend->Sync();
			$this->Add($Friend);
			$this->Length++;
		}
		return true;
	}
	
} // END Friends



//
// CLASS: Charity
// DETAILS: A list of charity profiles.
// METHODS:
//
class Charity{
	public $ID;
	public $ABN;
	public $Name;
	public $Email;
	public $Phone;
	public $Website;
	public $Address;
	public $Address2;
	public $City;
	public $State;
	public $PostCode;
	public $Categories;
	public $Size;
	
	//
	// Constructor.
	//
	public function __construct($id = null) {
		$this->ID = isset($id) ? $id : ID::create();
    }
	
	//
	// Retrieve charity profile data using our database. 
	//
	public function Sync(){
		// Construct the SQL to select the charity with this ID
		$SQL = new SQL("SELECT * FROM Charities WHERE ID = '$this->ID'");
		
		// Query the database
		if($status = DB::Query($SQL, $result)){
			$this->Name = $result[0]['Name'];
			$this->Description = $result[0]['Description'];
			$this->Email = $result[0]['Email'];
			$this->Phone = $result[0]['Phone'];
			$this->Website = $result[0]['Website'];
			$this->Address = $result[0]['Address'];
			
			
			
			
			
		}
		
		// Return the status
		return $status;
    }
	
	//
	// Save not-null charity profile data to our database. 
	//
	public function Save() {
		// Construct SQL to insert this charity
		$SQL = new SQL("INSERT INTO Charities (ID, Name, Description, Email, Phone, Website, Address, DateCreated) VALUES ('$this->ID', '$this->Name', '$this->Description', '$this->Email', '$this->Phone', '$this->Website', '$this->Address', NOW())");
		
		// Query the database
		return DB::Query($SQL);
    }
	
	//
	// Returns the ID when the class object is used as a string.
	//
	public function __toString(){
		 return (string)$this->ID;
	}
	
} // END Charity



//
// CLASS: Challenge
// DETAILS: A social charitable challenge.
// METHODS:
//
class Challenge{
	public $ID;
	public $Title;
	public $Description;
	public $Target;
	public $StartDate;
	public $EndDate;
	public $Image;
	public $Charity1;
	public $Charity1Percent;
	public $Charity2;
	public $Charity2Percent;
	public $Charity3;
	public $Charity3Percent;
	public $LinkURLID;
	public $User;
	public $DateCreated;
	
	//
	// Constructor.
	//
	public function __construct($id = null) {
		$this->ID = isset($id)? $id : ID::create();
	}
	
	//
	// Retrieve challenge data using our database. 
	//
	public function Sync(){
		// Construct the SQL to select the Challenge with this ID
		$SQL = new SQL("SELECT Title, Description, Target, StartDate, EndDate, Image, Charity1ID, Charity1Percent, Charity2ID, Charity2Percent, Charity3ID, Charity3Percent, LinkURLID, UserID, DateCreated FROM Challenges WHERE ID = '$this->ID'");
		
		// Query the database
		if($status = DB::Query($SQL, $result)){
			// Store the Challenge values
			$this->Title = $result[0]['Title'];
			$this->Description = $result[0]['Description'];
			$this->Target = $result[0]['Target'];
			$this->StartDate = $result[0]['StartDate'];
			$this->EndDate = $result[0]['EndDate'];
			$this->Image = $result[0]['Image'];
			$this->Charity1 = $result[0]['Charity1ID'];
			$this->Charity1Percent = $result[0]['Charity1Percent'];
			$this->Charity2 = $result[0]['Charity2ID'];
			$this->Charity2Percent = $result[0]['Charity2Percent'];
			$this->Charity3 = $result[0]['Charity3ID'];
			$this->Charity3Percent = $result[0]['Charity3Percent'];
			$this->LinkURLID = $result[0]['LinkURLID'];
			$this->User = new User($result[0]['UserID']);
			$this->DateCreated = $result[0]['DateCreated'];
		}
		
		// Return the status
		return $status;
    }
	
	//
	// Save not-null challenge data to our database. 
	//
	public function Save() {
		// Construct the SQL to insert this challenge
		$SQL = new SQL("INSERT INTO Challenges (ID, Title, Description, Target, StartDate, EndDate, Image, Charity1ID, Charity1Percent, Charity2ID, Charity2Percent, Charity3ID, Charity3Percent, LinkURLID, UserID, DateCreated) VALUES('$this->ID', '$this->Title', '$this->Description', '$this->Target', '$this->StartDate', '$this->EndDate','$this->Image', '$this->Charity1', '$this->Charity1Percent', '$this->Charity2', '$this->Charity2Percent', '$this->Charity3', '$this->Charity3Percent', '$this->LinkURLID', '$this->User', NOW()) ON DUPLICATE KEY UPDATE Title='$this->Title', Description='$this->Description'");
		
		// Query the database
		return DB::Query($SQL);
    }
	
	//
	// Returns the ID when the class object is used as a string.
	//
	public function __toString(){
		 return (string)$this->ID;
	}
	
} // END Challenge



//
// CLASS: ChallengeList
// DETAILS: A class of static functions to generate an array of challenges.
// METHODS: static GetTrending(), static Search()
//
class Challenges{
	//
	// Search for top 10 trending challenges on our database.
	//
	public static function GetTrending(){
		
		// Construct the SQL to select top 10 challenges
		$SQL = new SQL("SELECT ID, Title, Description, Image, Charity1ID, Charity1Percent, Charity2ID, Charity2Percent, Charity3ID, Charity3Percent, LinkURLID, UserID, DateCreated FROM Challenges LIMIT 10");
		
		// Query the database
		if(DB::Query($SQL, $result)){
			// Create an array of the Challenge's found
			foreach($result as $r){
				// Create Challenge object
				$Challenge = new Challenge($r['ID']);
				$Challenge->Title = $r['Title'];
				$Challenge->Description = $r['Description'];
				$Challenge->Image = $r['Image'];
				$Challenge->Charity1 = $r['Charity1ID'];
				$Challenge->Charity1Percent = $r['Charity1Percent'];
				$Challenge->Charity2 = $r['Charity2ID'];
				$Challenge->Charity2Percent = $r['Charity2Percent'];
				$Challenge->Charity3 = $r['Charity3ID'];
				$Challenge->Charity3Percent = $r['Charity3Percent'];
				$Challenge->LinkURLID = $r['LinkURLID'];
				$Challenge->User = $r['UserID'];
				$Challenge->DateCreated = $r['DateCreated'];
				
				// Add Challenge to array
				$Challenges[] = $Challenge;
			}
		}
		
		// Return the array of challenges
		return $Challenges;
	}
	
	//
	// Search for challenges on our database that use a given term.
	//
	public static function Search($term){
		if($term == ""){
			return self::GetTrending();
		} else {
			$terms = explode(" ",$term);
			
			// Construct the SQL to select the top 10 matches to the terms
			$SQL = new SQL("SELECT ID, Title, Description, Image, Charity1ID, Charity1Percent, Charity2ID, Charity2Percent, Charity3ID, Charity3Percent, LinkURLID, UserID, DateCreated FROM Challenges WHERE Title LIKE '%".$term[0]."%'");
			for($i=1; $i < count($terms); $i++){
				$SQL->Append("OR LIKE '%".$terms[$i]."%'");
			}
			$SQL->Append("LIMIT 10");
			
			// Query the database
			if(DB::Query($SQL, $result)){
				// Create an array of the Challenge's found
				foreach($result as $r){
					// Create Challenge object 
					$Challenge = new Challenge($r['ID']);
					$Challenge->Title = $r['Title'];
					$Challenge->Description = $r['Description'];
					$Challenge->Image = $r['Image'];
					$Challenge->Charity1 = $r['Charity1ID'];
					$Challenge->Charity1Percent = $r['Charity1Percent'];
					$Challenge->Charity2 = $r['Charity2ID'];
					$Challenge->Charity2Percent = $r['Charity2Percent'];
					$Challenge->Charity3 = $r['Charity3ID'];
					$Challenge->Charity3Percent = $r['Charity3Percent'];
					$Challenge->LinkURLID = $r['LinkURLID'];
					$Challenge->User = $r['UserID'];
					$Challenge->DateCreated = $r['DateCreated'];
					
					// Add Challenge to the array
					$Challenges[] = $Challenge;
				}
			}
			
			// Return the array of challenges
			return $Challenges;
		}
	}
	
} // END Challenges



//
// CLASS: Link
// DETAILS: A URL linking to a specific challenge.
// METHODS: __construct(), Sync(), Save(), BuildURL(), SetActive(), __toString()
//
class Link{
	public $ID;
	public $URLID;
	public $URL;
	public $User;
	public $Challenge;
	public $Active;
	public $DateCreated;
	private $Domain = "charity-x.org?l=";
	
	//
	// Constructor.
	//
	public function __construct($id = null) {
		$this->ID = isset($id) ? $id : ID::create(10);
	}
	
	//
	// Retrieve a Link/URL data using our database. 
	//
	public function Sync() {
		// Construct SQL to select link with this URLID or else this ID
		$SQL = new SQL("SELECT ID, URLID, URL, UserID, ChallengeID, Active, DateCreated FROM Links");
		if(isset($this->URLID))
		   $SQL->Append("WHERE URLID = '$this->URLID'");
		else
		   $SQL->Append("WHERE ID = '$this->ID'");
		
		// Query the database
		if($status = DB::Query($SQL, $result)){
			$this->ID = $result[0]['ID'];
			$this->URLID = $result[0]['URLID'];
			$this->URL = $result[0]['URL'];
			$this->User = ($result[0]['UserID'] != 0)? new User($result[0]['UserID']) : null;
			$this->Challenge = new Challenge($result[0]['ChallengeID']);
			$this->Active = ($result[0]['Active'] == 1)? true : false;
			$this->DateCreated = $result[0]['DateCreated'];
		}
		
		// Return the status
		return $status;
	}
	
	//
	// Save not-null Link/URL data to our database. 
	//
	public function Save() {
		// Construct the SQL to insert this Link
		$SQL = new SQL("INSERT IGNORE INTO Links (ID, URLID, URL, UserID, ChallengeID, Active, DateCreated) VALUES ('$this->ID', '$this->URLID', '$this->URL', '$this->User', '$this->Challenge', '$this->Active', NOW())");
		//".($this->Active)? 1 : 0 ."
		
		// Query the database
		return DB::Query($SQL);
    }
	
	//
	//
	//
	public function BuildDefaultURL($Challenge){
		// New Link for user - Use this ID (already generated) for Link
		$this->URLID = $this->ID;
		$this->URL = $this->Domain.$this->ID;
		$this->Challenge = $Challenge;
		$this->Active = 0;
	}
	
	
	
	//
	// Build a URL to a challenge for a user.
	//
	public function BuildURL($Challenge, $User = null){
		// Test if $Challenge is a Challenge object
		if(!($Challenge instanceof Challenge))
			return false;
		else
			$this->Challenge = $Challenge;
		
		// Test if $User is set and if it is a User object
		if(isset($User) && ($User instanceof User)){
			$this->User = $User;
			
			// Construct SQL to select if this link already exists
			$SQL = new SQL("SELECT ID, URLID, URL, Active, DateCreated FROM Links WHERE UserID='$User->ID' AND ChallengeID='$Challenge->ID'");
			
			// Query the database
			if(DB::Query($SQL, $result)){
				// User already has a link
				$this->ID = $result[0]['ID'];
				$this->URLID = $result[0]['URLID'];
				$this->URL = $result[0]['URL'];
				$this->Active = $result[0]['Active'];
				$this->DateCreated = $result[0]['DateCreated'];
			} else { 
				// New Link for user - Use this ID (already generated) for Link
				$this->URLID = $this->ID;
				$this->URL = $this->Domain.$this->ID;
				$this->Active = 0;
			}
		} else { 
			// No logged in user - Set as default link
			$this->ID = $Challenge->LinkURLID;
			$this->URLID = $Challenge->LinkURLID;
			$this->URL = $this->Domain.$Challenge->LinkURLID;
			$this->Active = 1;
		}
		
		// Return true
		return true;
	}
	
	//
	// Set the link to active.
	//
	public function SetActive() {
		$status = true;
		if($this->Active == 0){
			$this->Active = 1;
			$SQL = new SQL("UPDATE Links SET Active='$this->Active' WHERE ID = '$this->ID'");
			// Query the database
			$status = DB::Query($SQL);
		}
		return $status;
    }
	
	//
	// Returns the ID when the class object is used as a string.
	//
	public function __toString(){
		 return (string)$this->ID;
	}
	
}


//
// CLASS: Donation
// DETAILS: A donation made by a user to a challenge.
// METHODS: __construct(), Sync(), Save(), __toString()
//
class Donation{
	public $ID;
	public $StripeChargeID;
	public $ReceiptNo;
	public $User;
	public $Amount;
	public $Link;
	public $Challenge;
	public $Charity1;
	public $Charity1Percent;
	public $Charity2;
	public $Charity2Percent;
	public $Charity3;
	public $Charity3Percent;
	public $Points;
	public $DateCreated;
	
	//
	// Constructor.
	//
	public function __construct($id = null){
		$this->ID = isset($id) ? $id : ID::create();
		$this->User = new User();
		$this->Amount = 0.00;
		$this->Link = new Link();
		$this->Challenge = new Challenge();
		$this->Points = new Points();
	}
	
	//
	// Retrieve donation data using our database. 
	//
	public function Sync(){
		// Construct the SQL to select the donation with this ID
		$SQL = new SQL("SELECT ID, StripeChargeID, ReceiptNo, UserID, Amount, LinkID, ChallengeID, Charity1ID, Charity1Percent, Charity2ID, Charity2Percent, Charity3ID, Charity3Percent, PointsID, DateCreated FROM Donations WHERE ID = '".$this->ID."'");

		// Query the database
		if($status = DB::Query($SQL, $result)){
			$this->ID = $result[0]['ID'];
			$this->StripeChargeID = $result[0]['StripeChargeID'];
			$this->ReceiptNo = $result[0]['ReceiptNo'];
			$this->User = new User($result[0]['UserID']);
			$this->Amount = $result[0]['Amount'];
			$this->Link = new Link($result[0]['LinkID']);
			$this->Challenge = new Challenge($result[0]['ChallengeID']);
			$this->Charity1 = $result[0]['Charity1ID'];
			$this->Charity1Percent = $result[0]['Charity1Percent'];
			$this->Charity2 = $result[0]['Charity2ID'];
			$this->Charity2Percent = $result[0]['Charity2Percent'];
			$this->Charity3 = $result[0]['Charity3ID'];
			$this->Charity3Percent = $result[0]['Charity3Percent'];
			$this->Points = new Points($result[0]['PointsID']);
			$this->Points->Sync();
			$this->DateCreated = $result[0]['DateCreated'];
		}
		
		// Return the status
		return $status;
	}
	
	//
	// Save not-null donation data to our database. 
	//
	public function Save(){
		// Save the points
		$this->Points->Save();
		
		// Construct the SQL to insert this donation
		$SQL = new SQL("INSERT INTO Donations (ID, StripeChargeID, ReceiptNo, UserID, Amount, LinkID, ChallengeID, Charity1ID, Charity1Percent, Charity2ID, Charity2Percent, Charity3ID, Charity3Percent, PointsID, DateCreated) VALUES ('$this->ID', '$this->StripeChargeID', '$this->ReceiptNo', '$this->User', '$this->Amount', '$this->Link', '$this->Challenge', '$this->Charity1', '$this->Charity1Percent', '$this->Charity2', '$this->Charity2Percent', '$this->Charity3', '$this->Charity3Percent', '$this->Points', NOW())");
		
		// Query the database
		return DB::Query($SQL);
	}
	
	//
	// Returns the ID when the class object is used as a string.
	//
	public function __toString(){
		 return (string)$this->ID;
	}
	
}



//
// CLASS: StripeCard
// DETAILS: A user card details stored as a Stripe customer token.
// METHODS: __construct(), Sync(), Save(), IsActive(), __toString()
//
class StripeCard{
	public $ID;
	public $User;
	public $Active;
	public $Brand;
	public $ExpiryMonth;
	public $ExpiryYear;
	public $Last4;
	public $DateCreated;
	
	//
	// Constructor.
	//
	public function __construct($id = null) {
		$this->ID = $id;
		$this->Active = false;
    }
	
	//
	// Retrieve Stripe customer card data using our database. 
	//
	public function Sync(){
		// Construct the SQL to select the StripeCard using this ID
		$SQL = new SQL("SELECT UserID, Brand, ExpiryMonth, ExpiryYear, Last4, DateCreated FROM StripeCards WHERE ID = '$this->ID'");
		
		// Query the database
		if($status = DB::Query($SQL, $result)){
			$this->User = new User($result[0]['UserID']);
			$this->Brand = $result[0]['Brand'];
			$this->ExpiryMonth = $result[0]['ExpiryMonth'];
			$this->ExpiryYear = $result[0]['ExpiryYear'];
			$this->Last4 = $result[0]['Last4'];
			$this->Active = IsActive();
			$this->DateCreated = $result[0]['DateCreated'];
		}
		
		// Return the status
		return $status;
    }
	
	//
	// Save Stripe customer card data to our database. 
	//
	public function Save() {
		// Construct the SQl to insert a new StripeCard
		$SQL = new SQL("INSERT INTO StripeCards (ID, UserID, Brand, ExpiryMonth, ExpiryYear, Last4, DateCreated) VALUES ('$this->ID', '$this->User->ID', '$this->Brand', '$this->ExpiryMonth', '$this->ExpiryYear', '$this->Last4', NOW())");
		
		// Query the database
		return DB::Query($SQL);
    }
	
	//
	// Test if the card is active.
	//
	public function IsActive(){
		// Compaire the expiry date to the current date
		$expiry = $this->ExpiryYear.$this->ExpiryMonth;
		$current = date("Y").date("n");
		return ($expiry < $current);
	}
	
	//
	// Returns the ID when the class object is used as a string.
	//
	public function __toString(){
		 return (string)$this->ID;
	}
	
}

//
// CLASS: StripeCards
// DETAILS: A static class used to return an array of Stripe Cards owned by a given user.
// METHODS: static OwnedBy()
//
class StripeCards{
	//
	// Static method to return an array of Stripe Cards owned by a given user
	//
	public static function OwnedBy($ID){
		// Construct the SQL to search for all cards with user ID
		$SQL = new SQL("SELECT ID, UserID, Brand, ExpiryMonth, ExpiryYear, Last4, DateCreated FROM StripeCards WHERE UserID = '$ID'");
		
		// Query the database
		if(DB::Query($SQL, $result)){
			// For each card add it to the list
			foreach($result as $r){
				$Card = new StripeCard($r['ID']);
				$Card->User = $r['UserID'];
				$Card->Brand = $r['Brand'];
				$Card->ExpiryMonth = $r['ExpiryMonth'];
				$Card->ExpiryYear = $r['ExpiryYear'];
				$Card->Last4 = $r['Last4'];
				$Card->Active = $Card->IsActive();
				$Card->DateCreated = $r['DateCreated'];
				
				// Add Card to array
				$Cards[] = $Card;
			}
		}
		
		// Return the array of cards
		return $Cards;
	}
}


//
// CLASS: Points
// DETAILS: Points used to represent user overall score.
// METHODS: __construct(), Sync(), Save(), calc(), __toString()
//
class Points{
	public $ID;
	public $User;	
	public $Points;
	public $Rate;
	public $Action;
	public $Link;
	public $DateCreated;
	
	//
	// Constructor.
	//
	public function __construct($id = null){
		$this->ID = isset($id) ? $id : ID::create();
		$this->Points = 0;
		$this->Rate = 1;
	}
	
	//
	// Retrieve points data using our database. 
	//
	public function Sync(){
		// Construct the SQL to select the Points with this ID
		$SQL = new SQL("SELECT ID, UserID, Points, Rate, Action, LinkID, DateCreated FROM Points WHERE ID = '$this->ID'");
		
		// Query the database
		if($status = DB::Query($SQL, $result)){
			$this->ID = $result[0]['ID'];
			$this->User = new User($result[0]['UserID']);
			$this->Points = $result[0]['Points'];
			$this->Link = new Link($result[0]['LinkID']);
			$this->DateCreated = $result[0]['DateCreated'];
			if($this->Link != null)
				$this->Link->Sync();
		}
		
		// Return the status
		return $status;
	}
	
	//
	// Save not-null points data to our database. 
	//
	public function Save(){
		// Validate fields
		if(!isset($this->Points) || !isset($this->User)){
			return false;
		}
		
		// Construct the SQL to insert this Points
		$SQL = new SQL("INSERT INTO Points (ID, UserID, Points, Rate, Action, LinkID, DateCreated) VALUES ('$this->ID', '$this->User', '$this->Points', '$this->Rate', '$this->Action', '$this->Link', NOW())");
		
		// Query the database
		if($status = DB::Query($SQL)){
			$this->User->AddPoints($this->Points);
		}
		
		// Return the status
		return $status;
	}
	
	//
	// Calculate points based on donation function
	//
	public function calc($donation){
		$this->Points = round(round(pow($donation * 5, 1.2)) * $this->Rate);
	}
	
	//
	// Returns the ID when the class object is used as a string.
	//
	public function __toString(){
		 return (string)$this->ID;
	}
	
}



//
// CLASS: Medal
// DETAILS: A medal used to represent user achievement.
// METHODS: __construct(), __toString()
//
class Medal{
	public $ID;
	public $Name;
	public $Description;
	public $Image;
	
	//
	// Constructor.
	//
	public function __construct($ID, $name, $description, $image){
		$this->ID = isset($id) ? $id : ID::create();
		$this->Name = $name;
		$this->Description = $description;
		$this->Image = $image;
	}
	
	//
	// Returns the ID when the class object is used as a string.
	//
	public function __toString(){
		 return (string)$this->ID;
	}
}



//
// CLASS: ID
// DETAILS: Used to create new IDs. 
// Example: $ID = ID::create();
//
class ID{
	public static function create($length = 12){
		return rand(pow(10, $length), pow(10, $Length+1)-1);
	}
}


//
// FUNCTION: Coalesce
// DETAILS: Returns the first not-null value.
//
function Coalesce($val1, $val2){
	if(!empty($val1))
		return "'".(string)$val1."'";
	else if(!empty($val2))
		return (string)$val2;
	else
		return null;
}

//
// CLASS: Sanitize
// DETAILS: Used to sanitize input values and optinally also validate them. 
// Example: $Email = Sanitize::Email($_POST['email'], $valid);
//
class Sanitize{
	// Text
	public static function Text($value, &$result = null){
		$value = filter_var($value, FILTER_SANITIZE_STRING);
		$result = true;
		return $value;
	}
	// Int
	public static function Int($value, &$result = null){
		$value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
		$result = (filter_var($int, FILTER_VALIDATE_INT) === 0 || !filter_var($int, FILTER_VALIDATE_INT) === false);
		return $value;
	}
	// Float
	public static function Float($value, &$result = null){
		$value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
		$result = filter_var($value, FILTER_VALIDATE_FLOAT);
		return $value;
	}
	// Bool
	public static function Bool($value, &$result = null){
		$value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
		$result = ($value != null);
		return $value;
	}
	// Email
	public static function Email($value, &$result = null){
		$value = filter_var($value, FILTER_SANITIZE_EMAIL);
		$result = filter_var($value, FILTER_VALIDATE_EMAIL);
		return $value;
	}
	// Date
	public static function Date($value, &$result = null){
		$value = isset($value)? date('Y-m-d H:i:s', strtotime($value)) : null;
		$result = checkdate(Date('m', $value), Date('d', $value), Date('Y', $value));
		return $value;
	}
	// URL
	public static function URL($value, &$result = null){
		$value = filter_var($value, FILTER_SANITIZE_URL);
		$result = filter_var($value, FILTER_VALIDATE_URL);
		return $value;
	}
	// Currency
	public static function Currency($value, &$result = null){
		$value = round(filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION), 2);
		$result = (filter_var($value, FILTER_VALIDATE_FLOAT) && ($value >= 0));
		return $value;
	}
}

?>