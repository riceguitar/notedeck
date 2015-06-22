<?php

defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );

/**
 * Helper class that sets up the included acf plugin
 *
 */
class GoogleFonts {

	/*
	 * Static property to hold singelton instance
	 */
	static $instance = false;


	private function __construct() {
		
		$this->add_admin_styles();
		$this->add_styles();

		$this->add_editor_buttons();
	}

	private function add_admin_styles() {

	}

	private function add_styles() {

	}

	private function add_editor_buttons() {
		add_filter( 'mce_buttons_1', array( $this, 'tinymce_add_buttons' ) );
		add_filter( 'tiny_mce_before_init', array( $this, 'tinymce_custom_options' ) );
	}

	public function tinymce_add_buttons($buttons) {
		return array_merge(
			array( 'fontselect' ),
			$buttons
		);
	}

	public static function get_instance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}

}

GoogleFonts::get_instance();