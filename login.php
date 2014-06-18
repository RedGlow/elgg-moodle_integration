<?php

require_once("../../engine/start.php");

// Check if we're logged in and with which user
$is_logged_in = elgg_is_logged_in();
$is_user_right = false;
if($is_logged_in) {
	$logged_in_user = elgg_get_logged_in_user_entity();
	$expected_username = $_GET['username'];
	$is_user_right = $logged_in_user->username == $expected_username;
}

if($is_logged_in && !$is_user_right) {
	// Logged in with the wrong user: logout first
	logout();
}
if($is_user_right) {
	// Already logged in with the correct username, just go to the destination URL
	header('Location: ' . $_GET['group_url']);
} else {
	// Not logged in: proceed with login procedure
	header('Location: ' . $_GET['callback_url'] . '?group_url=' . urlencode($_GET['group_url']));
}

?>
