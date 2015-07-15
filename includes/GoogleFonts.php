<?php
defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );
include 'GoogleFontList.php';

/**
 * Helper class that sets up the included acf plugin
 *
 */
class GoogleFonts {

	use GoogleFontList;

	/*
	 * Static property to hold singelton instance
	 */
	static $instance = false;

	/**
	 * Creates the class and sets up all of the needed actions. 
	 */
	private function __construct() {
		// Brings in all needed styles
		$this->addStyles();
		$this->addEditorButton();
	}


	public function addStyles() {
		add_action('admin_enqueue_scripts', array( $this, 'loadFonts') );
	}

	public function loadFonts() {
		$url = $this->makeUrlString();
        wp_enqueue_style( 'googleFonts', $url);
    }
	/**
	 * Sets up the actions for the custom font selector
	 */
	private function addEditorButton() {
		add_filter( 'mce_buttons_3', array( $this, 'tinymceAddButtons' ) );
		add_filter( 'tiny_mce_before_init', array( $this, 'tinymceCustomOptions' ) );
	}

	/**
	 * Registers the custom TinyMCE editor font selector fields.
	 */
	public function tinymceAddButtons($buttons) {
		return array_merge( array( 'fontselect' ), $buttons );
	}

	/**
	 * Sets up the options for the custom TinyMCE editor font selector.
	 */
	public function tinymceCustomOptions($options) {
		global $wp_version;
		$font_string = $this->makeFontString();
		$url_string = $this->makeUrlString();
		$key = (version_compare( $wp_version, '3.9', '<' )) ? 'theme_advanced_fonts' : 'font_formats';

		$updated_options = $this->updateOptionsArray($options, $font_string, $url_string, $key);
		return $updated_options;
	}

	/**
	 * Updates the options array for the tinyMCE editory
	 */
	public function updateOptionsArray($options, $font_string, $url_string, $key) {
		// Set the font options
		if (!empty($options[$key])) {
			$options[$key] .= ';' . $font_string;
		} else {
			$options[$key] = $font_string;
		}

		if (!empty($options['content_css'])) {
			$options['content_css'] .= ',' . $url_string;
		} else {
			$options['content_css'] = $url_string;
		}
	
		return $options;
	}

	/**
	 * Wrapper function used to keep a singleton instance.
	 */
	public static function getInstance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}

}

GoogleFonts::getInstance();