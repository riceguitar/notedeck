<?php
$deckDir = plugin_dir_path( __FILE__ );
$deckUrl = plugin_dir_url( __FILE__ );
// Check if ACF is used with another plugin, if not already called, use this one
if( ! class_exists('acf') ) {
	
	// Load the ACF Core
	include_once( $deckDir . '/includes/acf/acf.php');

	add_filter('acf/settings/path', 'my_acf_settings_path');
	function my_acf_settings_path( $path ) {
	    $path = $deckDir . '/includes/acf/';
	    return $path;
	}
	 
	add_filter('acf/settings/dir', 'my_acf_settings_dir');
	function my_acf_settings_dir( $dir ) {
	    $dir = $deckUrl . '/includes/acf/';
	    return $dir;
	}

	//add_filter('acf/settings/show_admin', '__return_false');
}