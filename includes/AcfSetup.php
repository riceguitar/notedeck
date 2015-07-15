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
		include_once('acf-build.php'); // build acf fields

		if( !class_exists('acf') ) {
			add_filter('acf/settings/show_admin', '__return_false'); // hide acf from admin
			add_filter('acf/settings/path', array( $this, 'getAcfPath' ) ); // set acf path
			add_filter('acf/settings/dir', array( $this, 'getAcfDir' ) ); // set dir location
			include_once($this->getAcfPath() . 'acf.php');
		}
		
		if( function_exists('acf_add_options_page') ) {
			acf_add_options_page(array(
					'page_title' 	=> 'NoteDeck Settings',
					'menu_title'	=> 'NoteDeck',
					'menu_slug' 	=> 'notedeck-settings',
					'capability'	=> 'edit_posts',
					'redirect'		=> false
			));		}
	}

	/*
	 * Sets up the initially needed acf fields.
	 */
	public function buildAcf() {
		include_once( ND_95W_PLUGIN_PATH . '/acf-build.php' );
	}

	/*
	 * Sets the path to the acf plugin
	 */
	public function getAcfPath() {

		return ND_95W_PLUGIN_PATH . '/includes/acf/';
	}

	/*
	 * Sets the dir to the acf plugin
	 */
	public function getAcfDir() {
		return ND_95W_PLUGIN_URL . '/includes/acf/';
	}

	public static function getInstance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}

}

AcfSetup::getInstance();
