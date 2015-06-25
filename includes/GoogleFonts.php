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
		$this->add_styles();
		$this->add_editor_buttons();
	}


	public function add_styles() {
		add_action('admin_enqueue_scripts', array( $this, 'load_fonts') );
	}

	public function load_fonts() {
		$url = $this->make_url_string();
        wp_enqueue_style( 'googleFonts', $url);
    }
	/**
	 * Sets up the actions for the custom font selector
	 */
	private function add_editor_buttons() {
		add_filter( 'mce_buttons_3', array( $this, 'tinymce_add_buttons' ) );
		add_filter( 'tiny_mce_before_init', array( $this, 'tinymce_custom_options' ) );
	}

	/**
	 * Registers the custom TinyMCE editor font selector fields.
	 */
	public function tinymce_add_buttons($buttons) {
		return array_merge( array( 'fontselect' ), $buttons );
	}

	/**
	 * Sets up the options for the custom TinyMCE editor font selector.
	 */
	public function tinymce_custom_options($options) {
		global $wp_version;
		$font_string = $this->make_font_string();
		$url_string = $this->make_url_string();
		$key = (version_compare( $wp_version, '3.9', '<' )) ? 'theme_advanced_fonts' : 'font_formats';

		$updated_options = $this->update_options_array($options, $font_string, $url_string, $key);
		return $updated_options;
	}

	/**
	 * Updates the options array for the tinyMCE editory
	 */
	public function update_options_array($options, $font_string, $url_string, $key) {
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
	public static function get_instance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}

}

GoogleFonts::get_instance();