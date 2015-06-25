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



	private function __construct() {

		// Sets up global properties.
		if ( ! defined( 'ND_95W_BASE_FILE' ) )
		    define( 'ND_95W_BASE_FILE', __FILE__ );
		if ( ! defined( 'ND_95W_BASE_DIR' ) )
		    define( 'ND_95W_BASE_DIR', dirname( ND_95W_BASE_FILE ) );
		if ( ! defined( 'ND_95W_PLUGIN_URL' ) )
		    define( 'ND_95W_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		if ( ! defined( 'ND_95W_PLUGIN_PATH') )
			define( 'ND_95W_PLUGIN_PATH', plugin_dir_path( __FILE__) );

		// Loads and Sets up ACF
		include_once 'includes/AcfSetup.php';
		
		include_once 'includes/GoogleFonts.php';

		// Creates the post type.
		add_action( 'init', array( $this, 'register_post_type' ) );

		// Changes the page template for NoteDeck posts.
		add_filter( 'template_include', array( $this, 'set_page_template' ) );

	}

	public static function get_instance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}

	/*
	 * Creates the Note Deck post type.
	 */
	public function register_post_type() {
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
	public function set_page_template($template) {
		$theme = "";
		// Post ID
	    $post_id = get_the_ID();
	 
	    // For all other CPT
	    if ( get_post_type( $post_id ) != 'deck_decks' ) {
	        return $template;
	    }
	 
	    // Else use custom template
	    if ( is_single() ) {
	        return $this->get_page_template('default');
	    }
	    
	    return $template;
	}

	/*
	 * Gets the file path for the desired page template.
	 */
	private function get_page_template($template) {

    	return ND_95W_BASE_DIR . '/includes/templates/notedeck_' . $template . '.php';

	}

	public function stylesheet() {
		return ND_95W_PLUGIN_URL . '/css/main.css';
	}

	private function vimeo_id($video_url) {
    	preg_match("/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/", $video_url, $output_array);
	    if ( $output_array[5] ) {
	    	return $output_array[5];
	    }
  	    return false;
	}

	public function youtube_id($video_url) {
		if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video_url, $match)) {
	    	return $match[1];
		}
		return false;
	}

	public function get_video_player($video_url, $source) {
		if ($source == 'youtube') {
			$embed_url = 'src="//youtube.com/embed/';
			$video_id = $this->youtube_id($video_url);
		} else if ($source == 'vimeo') {
			$embed_url = 'src="//player.vimeo.com/video/';
			$video_id = $this->vimeo_id($video_url);
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

$NoteDeck_95W = NoteDeck_95W::get_instance();


?>