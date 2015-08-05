<?php 
/*
	Plugin Name: Notedeck
	Plugin URI: http://notedeck.co
	Description: Create a full page Deck of slides of different types of Slides. Users can take notes and email them to themselves.
	Version: 1.0
	Author: 95 West
	Author URI: https://95west.co/
*/

defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );

class NoteDeck_95W {

	// Static property to hold singelton instance
	static $instance = false;

	private $licenseStatus;

	private $licesnseKey;

	private $pluginVersion = '1.0';

	/**
	 * Constructor that sets global properties and includes a few self invoking plugin dependencies
	 */
	private function __construct() 
	{
		// The main NoteDeck plugin file.
		if (!defined('ND_95W_BASE_FILE')) {
			define('ND_95W_BASE_FILE', __FILE__);
		}
		// Url to the main NoteDeck plugin file.
		if (!defined('ND_95W_PLUGIN_URL')) {
		    define('ND_95W_PLUGIN_URL', plugin_dir_url(__FILE__));
		}
		// Path to the main NoteDeck plugin file.
		if (!defined('ND_95W_PLUGIN_PATH')) {
			define('ND_95W_PLUGIN_PATH', plugin_dir_path(__FILE__));
		}
		// Name of the plugin.
		if (!defined('ND_95W_SOFTWARE_NAME')) {
			define('ND_95W_SOFTWARE_NAME', 'Notedeck' );
		}
		// Remote url used for licensing and updates.
		if (!defined('ND_95W_REMOTE_URL')) {
			define('ND_95W_REMOTE_URL', 'http://www.notedeck.co/edd-api' );
		}

		include_once 'includes/AcfSetup.php';
		include_once 'includes/NoteDeckLicensing.php';
		include_once 'includes/NoteDeckUpdater.php';
		include_once 'includes/GoogleFonts.php';

		$this->validateRegistration();
	}

	/**
	 * Validates the plugin and initializes it on validation.
	 */
	public function validateRegistration()
	{
		// Small admin tweaks needed for unregistered plugin.
		add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'));
		$this->licenseStatus = NoteDeckLicensing::getInstance()->getStatus();
		$this->licenseKey = NoteDeckLicensing::getInstance()->getLicenseKey();
		if ($this->licenseStatus == 'valid') {
			$this->initializePlugin();
		}
	}

	/**
	 * Registers and sets up needed hooks, and initializes all fields.
	 */
	public function initializePlugin()
	{
		// Creates the post type.
		add_action( 'init', array( $this, 'registerPostTypes' ) );
		
		// Changes the page template for NoteDeck posts.
		add_filter( 'template_include', array( $this, 'setPageTemplate' ) );

		// Sets up the licensed ACF fields
		AcfSetup::getInstance()->addLicensedFields();

		// Checks for updates to the plugin.
		add_action( 'admin_init', array($this, 'checkForUpdates'));	
	}

	/**
	 * Calls the NoteDeckUpdater class and checks to see if there are any updates avaliable
	 */
	public function checkForUpdates()
	{
		new NoteDeckUpdater( ND_95W_REMOTE_URL, ND_95W_BASE_FILE, array(
				'version' 	=> $this->pluginVersion, 	// current version number
				'license' 	=> $this->licenseKey, 		// license key
				'item_name' => ND_95W_SOFTWARE_NAME, 	// name of this plugin
				'author' 	=> '95west',  // author of this plugin
				'url'		=> home_url()
			)
		);
	}

	/*
	 * Creates the Note Deck post type.
	 */
	public function registerPostTypes() 
	{
		register_post_type( 'deck_decks',
		    array(
		      'labels' => array(
		        'name' => __( 'Decks' ),
		        'singular_name' => __( 'Deck' )
		      ),
		      'public' => true,
		      'has_archive' => true,
		      'menu_icon' => 'dashicons-images-alt2',
		      'rewrite' => array('slug' => 'deck'),
		    )
	  	);
	}

	/*
	 * Sets the page to the NoteDeck Template for NoteDeck posts
	 */
	public function setPageTemplate($template) 
	{
	    $post_id = get_the_ID();
	 
	    // For all other Custom Post Types
	    if ( get_post_type( $post_id ) != 'deck_decks' ) {
	        return $template;
	    }
	 
	    // Else use custom template
	    if ( is_single() ) {
	    	// If notedeck needs to display inside of the theme
	    	if (get_field('in_theme')) {
	    		$this->setInTheme();
	    		return $template;
	    	}
	        return $this->getPageTemplate('default');
	    }
	    
	    return $template;
	}

	/*
	 * Gets the file path for the desired page template.
	 */
	private function getPageTemplate($template) 
	{
    	return ND_95W_PLUGIN_PATH . '/includes/templates/notedeck_' . $template . '.php';
	}

	/**
	 * Registers all needed hooks for Decks displayed in theme.
	 */
	private function setInTheme() 
	{
		// Replaces the page content with a DeckPost
		add_filter('the_content', array($this, 'inThemeContent'));
		// Enques in theme dependencies
		add_action('wp_enqueue_scripts', array($this, 'enqueueInThemeDependenices'), 1);
	}

	/**
	 * Enqueues all of the needed scripts and styles that are needed when NoteDeck displays in the theme.
	 */
	public function enqueueInThemeDependenices() 
	{
		$fonts = GoogleFonts::getInstance();
		// All Scripts Needed
		wp_enqueue_script('jQueryMin', 'http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js');
		wp_enqueue_script('lightslider', ND_95W_PLUGIN_URL . 'js/min/lightslider.min.js');

		// All Stylesheets Needed
		wp_enqueue_style('lightsliderCss', ND_95W_PLUGIN_URL . 'css/lightslider.min.css');
		wp_enqueue_style('bootstrap-min', ND_95W_PLUGIN_URL . '/includes/bootstrap/css/bootstrap.min.css');
		wp_enqueue_style('ND_95W_CSS', ND_95W_PLUGIN_URL . 'css/main.css');
		wp_enqueue_style('GoogleFonts', $fonts->makeUrlString());
	}

	/**
	 * Enques all of the needed scripts for the backend.
	 */
	public function enqueueAdminScripts()
	{
		// Admin style sheet.
		wp_enqueue_style('ND_95W_ADMIN_CSS', ND_95W_PLUGIN_URL . 'css/admin.css');
	}

	/**
	 * Brings in the template needed for NoteDeck to display in the current theme's wrapper.
	 */
	public function inThemeContent() 
	{
		include $this->getPageTemplate('in_theme');
	}

	/**
	 * Helper function that returns the url for the main NoteDeck stylesheet.
	 */
	public function stylesheet() 
	{
		return ND_95W_PLUGIN_URL . '/css/main.css';
	}

	/**
	 * Helper function that returns the vimeo video id from a vimeo url.
	 */
	public function vimeoId($video_url) 
	{
    	preg_match("/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/", $video_url, $output_array);
	    if ( $output_array[5] ) {
	    	return $output_array[5];
	    }
  	    return false;
	}

	/**
	 * Helper function that returns the youtube video id from a youtube url.
	 */
	public function youtubeId($video_url) 
	{
		if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video_url, $match)) {
	    	return $match[1];
		}
		return false;
	}

	/**
	 * Helper function that returns the iFram code from either a youtube or vimeo video url.
	 */
	public function getVideoPlayer($video_url, $source) 
	{
		if ($source == 'youtube') {
			$embed_url = 'src="//youtube.com/embed/';
			$video_id = $this->youtubeId($video_url);
		} else if ($source == 'vimeo') {
			$embed_url = 'src="//player.vimeo.com/video/';
			$video_id = $this->vimeoId($video_url);
		}

		if ($video_id == false) {
			return '<p class="invalid-url">Invalid Video URL</p>';
		}

	    $video_embed_code = '<div class="video-container">
	    						<iframe 
	    							' . $embed_url . $video_id .'" 
	                            	width="960" 
	                            	height="540" 
	                            	frameborder="0" 
	                            	webkitallowfullscreen mozallowfullscreen allowfullscreen>
	                            </iframe>
	                        </div>';
	    return $video_embed_code;
	}

	/**
	 * Returns a singelton instance of the class
	 */
	public static function getInstance() 
	{
		if ( !self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	////End Class
}

$NoteDeck_95W = NoteDeck_95W::getInstance();

?>