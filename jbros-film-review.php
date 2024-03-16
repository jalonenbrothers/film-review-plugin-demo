<?php
/**
 * Plugin Name: Jbros Film Review
 * Plugin URI: https://ossi.jalonenbrothers.com/wordpress/jbros-film-review
 * Description: A plugin that integrates the Anthropic API with a Gutenberg block for film reviews.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://ossi.jalonenbrothers.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: jbros-film-review
 * Domain Path: /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The main plugin class.
 */
class Jbros_Film_Review {

    /**
     * The plugin instance.
     *
     * @var Jbros_Film_Review
     */
    private static $instance;

    /**
     * The plugin absolute path.
     *
     * @var string
     */
    private $plugin_path;

    /**
     * Constructor.
     */
    private function __construct() {
        $this->plugin_path = plugin_dir_path( __FILE__ );
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies.
     */
    private function load_dependencies() {
        require_once $this->plugin_path . 'includes/api/class-anthropic-api.php';
        require_once $this->plugin_path . 'includes/api/class-tmdb-api.php';
        require_once $this->plugin_path . 'public/class-jbros-film-review-public.php';
        require_once $this->plugin_path . 'public/blocks/blocks.php';
        //require_once $this->plugin_path . 'includes/post-types.php';
        require_once $this->plugin_path . 'includes/class-jbros-film-review-post-types.php';
        //require_once $this->plugin_path . 'includes/custom-fields.php';
        require_once $this->plugin_path . 'includes/class-jbros-film-review-settings.php';
    }

    /**
     * Register all hooks related to the admin area functionality.
     */
    private function define_admin_hooks() {
        // Initialize the settings page
        $jbros_film_review_settings = new Jbros_Film_Review_Settings();
    
        // Add the settings page to the admin menu
        add_action( 'admin_menu', array( $jbros_film_review_settings, 'add_settings_page' ) );
    }

    /**
     * Register all hooks related to the public-facing functionality.
     */
    private function define_public_hooks() {
        $public = new Jbros_Film_Review_Public();
        add_action( 'enqueue_block_assets', array( $public, 'enqueue_block_assets' ) );
        $public->init();
    }

    /**
     * Get the plugin instance.
     *
     * @return Jbros_Film_Review
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

/**
 * Initialize the plugin.
 */
function jbros_film_review_init() {
    Jbros_Film_Review::get_instance();
}
add_action( 'plugins_loaded', 'jbros_film_review_init' );