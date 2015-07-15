<?php

/******************************************************************************************************************************
 * SPECIAL TRAIT THAT HOLDS ALL OF THE GOOGLE FONTS, AND ALL OF THE HELPER FUNCTIONS THAT WORK WITH THE FONTS ARRAY
 ******************************************************************************************************************************/

trait GoogleFontList {

	/*
	 * Master list of all google fonts to be added to the plugin.
	 */
	protected $fonts = array(
		"Open+Sans" => array(
			'name' => "Open Sans",
			'editor_settings' => "Open Sans, sans-serif",
		),
		"Josefin+Slab" => array(
			'name' => "Josefin Slab",
			'editor_settings' => "Josefin Slab, serif",
		),
		"Arvo" => array(
			'name' => "Arvo",
			'editor_settings' => "Arvo, serif",
		),
		"Lato" => array(
			'name' => "Lato",
			'editor_settings' => "Lato, sans-serif",
		),
		"Vollkorn" => array(
			'name' => "Vollkorn",
			'editor_settings' => "Vollkorn, serif",
		),
		"Abril+Fatface" => array(
			'name' => "Abril Fatface",
			'editor_settings' => "Abril Fatface, display",
		),
		"Ubuntu" => array(
			'name' => "Ubuntu",
			'editor_settings' => "Ubuntu, sans-serif",
		),
		"PT+Sans" => array(
			'name' => "PT Sans",
			'editor_settings' => "PT Sans, sans-serif",
		),
		"Old+Standard+TT" => array(
			'name' => "Old Standard TT",
			'editor_settings' => "Old Standard TT, serif",
		),
		"Droid+Sans" => array(
			'name' => "Droid Sans",
			'editor_settings' => "Droid Sans, sans-serif",
		),
	);

	/**
	 * Makes the font string that is needed for the TinyMce editor custom options.
	 */
	public function makeFontString() {
		$font_array = array();
		foreach($this->fonts as $font) {
			$font_array[] = $font['name'] . '=' . $font['editor_settings'];
		}

		return implode(';', $font_array);
	}

	/**
	 *
	 */
	public function makeUrlString() {
		$google_url = '//fonts.googleapis.com/css?family=';
		$fonts = array_keys($this->fonts);
		
		return $google_url . implode('|', $fonts);
	}
}