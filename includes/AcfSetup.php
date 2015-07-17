<?php

defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );

/*
 |------------------------------------------------------
 | Helper class that sets up the included acf plugin
 |------------------------------------------------------
 */
class AcfSetup {

	/*
	 * Static property to hold singelton instance
	 */
	static $instance = false;


	private function __construct() {
		// Builds and sets up all needed ACF settings
		if( !class_exists('acf') ) {

			add_filter('acf/settings/path', array( $this, 'getAcfPath' ) ); // set acf path
			add_filter('acf/settings/dir', array( $this, 'getAcfDir' ) ); // set dir location
			add_filter('acf/settings/show_admin', '__return_false'); // hide acf from admin
			include_once(ND_95W_PLUGIN_PATH . 'includes/acf/acf.php');
			include ('acf-limited-build.php');
		}
		$this->addNoteDeckPage();
	}

	/*
	 * Sets the path to the acf plugin
	 */
	public function getAcfPath($path) {
		$path = ND_95W_PLUGIN_PATH . 'includes/acf/';
		return $path;
	}

	/*
	 * Sets the dir to the acf plugin
	 */
	public function getAcfDir($dir) {
		$dir = ND_95W_PLUGIN_URL . 'includes/acf/';
		return $dir;
	}

	public function addNoteDeckPage() {
		$page = array(
			'page_title' 	=> 'NoteDeck Settings',
			'menu_title'	=> 'NoteDeck',
			'menu_slug' 	=> 'notedeck-settings',
			'capability'	=> 'edit_posts',
			'redirect'		=> false
		);
		if( function_exists('acf_add_options_page') ) {
			acf_add_options_page($page);		
		}
	}

	public function addLicensedFields() {
		include('acf-build.php');
	}

	/**
	 * Returns a singleton instance of the class
	 */
	public static function getInstance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}
}

AcfSetup::getInstance();