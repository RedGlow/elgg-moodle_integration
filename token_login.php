<?php

require_once("../../engine/start.php");

// Get expired tokens
$current_time = time();
$old_tokens_data = elgg_get_entities_from_metadata(array(
	'type' => 'object',
	'subtype' => 'elgg-moodle-token-data',
	'metadata_name_value_pair' => array(
		'name' => 'timeout',
		'value' => $current_time,
		'operand' => '<='
	)
));

// Delete expired tokens
elgg_set_ignore_access(true);
foreach($old_tokens_data as $old_token) {
	$old_token->delete();
}
elgg_set_ignore_access(false);

// Search for given token
$get_token = $_GET['token'];
$tokens_data = elgg_get_entities_from_metadata(array(
	'type' => 'object',
	'subtype' => 'elgg-moodle-token-data',
	'metadata_name_value_pair' => array(
		'name' => 'token',
		'value' => $get_token
	)
));

if ( is_array($tokens_data) && count($tokens_data) > 0 ) {
	// Sanity checks
	assert($tokens_data[0]->token == $get_token);

	// Get out user guid from found token
	$token_data = $tokens_data[0];
	$user_guid = $token_data->user_guid;
	
	// Get out connected user
	$user = get_user($user_guid);

	// Login
	login($user);

	// Redirect to destination
	header('Location: ' . $_GET['group_url']);
}
else
{
	echo "Wrong or expired login token.";
}
?>
