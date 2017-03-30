
/*****  INDEX PAGE FUNCTIONS  *****/

$(function() {
	"use strict";
	
	// Get the trending challenges (no search term)
	ChallengeSearch("");
	
	// Open Search input
	$(".PrimaryAppBar").on("click", "#SearchAction", function(){
		$("#AppBarMainActionArea").html('<div class="NavIcon" id="CloseSearchAction"><i class="fa fa-arrow-left"></i></div>');
		$("#AppBarTitleArea").html('<div class="TextField"><input type="text" id="SearchEvent" placeholder="Search challenges"></div>');
		$("#SearchEvent").focus();
	});
	
	// Close Search input
	$(".PrimaryAppBar").on("click", "#CloseSearchAction", function(){
		$("#AppBarMainActionArea").html('<div class="NavIcon" id="NavAction"><i class="fa fa-bars"></i></div>');
		$("#AppBarTitleArea").html('<div class="Logo" id="Logo"><a href="http://charity-x.org"><img src="images/Charity-X_Logo_Small.png" alt="Charity-X"></a></div>');
		$("#SearchEvent").val("");
		ChallengeSearch("");
	});
	
	// Search challenges on key press of the input 
	$(".PrimaryAppBar").on("keyup", "#SearchEvent", function() {
		var term = $("#SearchEvent").val();
		ChallengeSearch(term);
	});


	// Method for searching challenges by a search term
	function ChallengeSearch(term){
		$("#CopyLink").hide();
		$("#ChallengeSuggestions").html('<div class="loading"><i class="fa fa-smile-o fa-spin fa-3x fa-fw"></i><br>Loading</div>');
		console.log("Loading Challenges...");

		$.post("php/ChallengeSearch.php",
		{term: term },
		function(data, status){
			if(status === "success" && data.status === "success"){
				console.log("Load Challenges:"+data.status);
				// Remove the loading icon
				$("#ChallengeSuggestions").html("");

				// For each challenge found
				$.each(data.challenges, function(i, item) {
					// Contstruct html to display challenge
					var challenges_html = "";
					challenges_html += "<section class=\"Card\" data-challengeid=\""+ item.ID +"\" data-urlid=\""+ item.LinkURLID +"\" data-title=\""+ item.Title +"\">";
					challenges_html += "<div class=\"RichMedia\"><div><img src=\"images/"+ item.Image +"\"></div></div>"; //Image
					challenges_html += "<div class=\"Header\"><div class=\"Title\">"+ item.Title +"</div>"; // Header Title
					challenges_html += "<div class=\"Subhead\">0 shares | 0 donations</div></div>"; // Header Subhead
					challenges_html += "<div class=\"SupportingText\"><p>"+ item.Description +"</p></div>";
					challenges_html += "<div class=\"Actions Border\">"; //Action Buttons 
					challenges_html += "<button class=\"Flat Accent ShareEvent\">Share</button>"; // Share button
					challenges_html += "<a href=\"http://charity-x.org?l="+ item.LinkURLID +"\"><button class=\"Flat\">Donate</button></a>"; // Donate button
					challenges_html += "<button class=\"Flat BtnIcon Right\" disabled><i class=\"fa fa-chevron-down\"></i></button>"; // More button
					challenges_html += "</div></section>";

					// Append the html to the challenge suggestions section
					$("#ChallengeSuggestions").append(challenges_html);
				});
			} else {
				// Display an error
				console.log("Load Challenges "+data.status+": "+data.errorMsg);
				$("#ChallengeSuggestions").html("Load Challenges "+data.status+": "+data.errorMsg);
			}
		}, "json");
	}
	
});