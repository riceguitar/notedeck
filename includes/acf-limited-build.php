<?php

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array (
	'key' => 'group_55a6a6817ff43',
	'title' => 'Licensing',
	'fields' => array (
		array (
			'key' => 'field_55a6a68e9ded3',
			'label' => 'License Key',
			'name' => 'license_key',
			'type' => 'text',
			'instructions' => 'Enter your license key here to register your version of NoteDeck. If you want to deactivate a license simply delete it here.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => 'Your license key...',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
			'readonly' => 0,
			'disabled' => 0,
		),
	),
	'location' => array (
		array (
			array (
				'param' => 'options_page',
				'operator' => '==',
				'value' => 'notedeck-settings',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'acf_after_title',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
));

endif;