<?php

defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );

/**
 * Helper class that sets up the included acf plugin
 *
 */
class AcfSetup {

	/*
	 * Static property to hold singelton instance
	 */
	static $instance = false;


	private function __construct() {
		// Builds and sets up all needed ACF settings
		include_once($this->get_acf_path() . 'acf.php');
		include_once('acf-build.php'); // build acf fields

		if( ! class_exists('acf') ) {
			add_filter('acf/settings/show_admin', '__return_false'); // hide acf from admin
			add_filter('acf/settings/path', array( $this, 'get_acf_path' ) ); // set acf path
			add_filter('acf/settings/dir', array( $this, 'get_acf_dir' ) ); // set dir location
		}
		if( function_exists('acf_add_options_page') ) {
			acf_add_options_page();
		}
	}

	/*
	 * Sets up the initially needed acf fields.
	 */
	public function build_acf() {
		include_once( ND_95W_PLUGIN_PATH . '/acf-build.php' );
	}

	/*
	 * Sets the path to the acf plugin
	 */
	public function get_acf_path() {
		return ND_95W_PLUGIN_PATH . '/includes/acf/';
	}

	/*
	 * Sets the dir to the acf plugin
	 */
	public function get_acf_dir() {
		return ND_95W_BASE_DIR . '/includes/acf/';
	}

	public static function get_instance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}

}

AcfSetup::get_instance();
