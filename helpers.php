<?php

/**
 * Get an absolute URL pointing to the root plugin directory.
 *
 * @return the URL pointing to the root plugin directory.
 */
function get_plugin_root_url() {
	global $CONFIG;

	// get the name of the plugin in the filesystem
	$file_dirname = dirname(__FILE__);
	$path_parts = pathinfo($file_dirname);
	$plugin_path_name = $path_parts['basename'];

	// get the URL of the website
	$site_url = $CONFIG->site->url;

	// merge them
	$full_url = $site_url . "mod/" . $plugin_path_name . "/";
	return $full_url;
}


/**
 * Get the URL where the login landing page for Moodle is located.
 *
 * @return The login landing page.
 */
function get_login_url() {
	return get_plugin_root_url() . "login.php";
}

function get_token_login_url() {
	return get_plugin_root_url() . "token_login.php";
}

?>
