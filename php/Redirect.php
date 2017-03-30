<?php
session_start();
// For develpment only - Will redirect to the landing page not an active session
if($_SESSION['REDIRECT'] != "NothingToSeeHere...")
	header('location:http://charity-x.org/hi/');

// To access app click the copyright symbol on the landing page. Enter 'ineedcoffee' in the dialog box.
?>