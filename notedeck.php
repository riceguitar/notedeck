<?php 
/*
	Plugin Name: Notedeck
	Plugin URI: http://notedeck.co
	Description: Slide Deck Plugin with the ability to take notes
	Version: 0.1.1
	Author: 95 West
	Author URI: https://95west.co/
*/

defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );

class NoteDeck_95W {

	/*
	 * Static property to hold singelton instance
	 */
	static $instance = false;

	public $license_status;

	public static function getInstance() 
	{
		if ( !self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	private function __construct() 
	{

		// Sets up global properties.
		if ( ! defined( 'ND_95W_BASE_FILE' ) )
		    define( 'ND_95W_BASE_FILE', __FILE__ );

		if ( ! defined( 'ND_95W_PLUGIN_URL' ) )
		    define( 'ND_95W_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

		if ( ! defined( 'ND_95W_PLUGIN_PATH') )
			define( 'ND_95W_PLUGIN_PATH', plugin_dir_path( __FILE__) );

		if ( ! defined( 'ND_95W_SOFTWARE_NAME') );
			define( 'ND_95W_SOFTWARE_NAME', 'Notedeck' );

		if ( ! defined( 'ND_95W_REMOTE_URL') );
			define( 'ND_95W_REMOTE_URL', 'http://www.notedeck.co/' );

		// Loads and Sets up ACF
		include_once 'includes/AcfSetup.php';
		// Handles Licensing
		include_once 'includes/NoteDeckLicensing.php';
		// Loads all needed Google Fonts
		include_once 'includes/GoogleFonts.php';

		$this->validateRegistration();
	}

	public function validateRegistration()
	{
		$this->license_status = NoteDeckLicensing::getInstance()->getStatus();
		if ($this->license_status == 'valid') {
			$this->initializePlugin();
		}
		return $this->unregisteredPlugin();
	}


	public function unregisteredPlugin()
	{

	}

	public function initializePlugin()
	{
		// Creates the post type.
		add_action( 'init', array( $this, 'registerPostTypes' ) );
		// Changes the page template for NoteDeck posts.
		add_filter( 'template_include', array( $this, 'setPageTemplate' ) );
		// Small admin tweaks
		add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'));
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
	 
	    // For all other CPT
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
    	return ND_95W_BASE_DIR . '/includes/templates/notedeck_' . $template . '.php';
	}

	private function setInTheme() 
	{
		add_filter('the_content', array($this, 'inThemeContent'));
		add_action('wp_enqueue_scripts', array($this, 'enqueueInThemeDependenices'));
	}

	public function enqueueInThemeDependenices() 
	{
		$fonts = GoogleFonts::getInstance();
		// All Scripts Needed
		wp_enqueue_script('jQueryMin', 'http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js');
		wp_enqueue_script('lightslider', ND_95W_PLUGIN_URL . 'js/min/lightslider.min.js');

		// All Stylesheets Needed
		wp_enqueue_style('lightsliderCss', ND_95W_PLUGIN_URL . 'css/lightslider.min.css');
		wp_enqueue_style('bootstrap-min', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css');
		wp_enqueue_style('ND_95W_CSS', ND_95W_PLUGIN_URL . 'css/main.css');
		wp_enqueue_style('GoogleFonts', $fonts->make_url_string());
	}

	public function enqueueAdminScripts()
	{
		wp_enqueue_style('ND_95W_ADMIN_CSS', ND_95W_PLUGIN_URL . 'css/admin.css');
	}

	public function inThemeContent() 
	{
		include $this->getPageTemplate('in_theme');
	}

	public function stylesheet() 
	{
		return ND_95W_PLUGIN_URL . '/css/main.css';
	}

	private function vimeoId($video_url) 
	{
    	preg_match("/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/", $video_url, $output_array);
	    if ( $output_array[5] ) {
	    	return $output_array[5];
	    }
  	    return false;
	}

	public function youtubeId($video_url) 
	{
		if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video_url, $match)) {
	    	return $match[1];
		}
		return false;
	}

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

	////End Class
}

$NoteDeck_95W = NoteDeck_95W::getInstance();


?>