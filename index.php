<?php
/**
 * Plugin Name: Rename Featured Image
 * Description: This plugin helps to rename the featured images attached to Post with its post title. It also updates the media post title and attachment guid. Also updates featured image name when the post is published or updated.
 * Version: 1.0
 * Author: hrishiv90
 */


/**
 * Main file, includes plugin classes and registers constants
 */

/**
 * Path to plugins root folder
 */
if ( !defined( 'wpRFP_ROOT' ) ) {
    define( 'wpRFP_ROOT', plugin_dir_path( __FILE__ ) );
}

/**
 * Base Name of plugin
 */
if ( !defined( 'wpRFP_BASENAME' ) ) {
    define( 'wpRFP_BASENAME', plugin_basename( __FILE__ ) );
}

/**
 * wpRFP
 * 
 * The starting point of wpRFP.
 * 
 * @package wpRFP
 * @since wpRFP 1.0
 */
class wpRFP {
    
    /**
     * @var string
     */
    public $version = '1.0';
    
    /**
     * Define Text Domain
     * 
     * @since wpRFP 1.0
     */
    public $wpRFP_text_domain = 'wpRFP';

    /**
     * Class Constructor
     * 
     * @since wpRFP 1.0
     */
    public function __construct() {
        
        // Define version constant
        define( 'wpRFP_VERSION', $this->version );
        
        // Add plugin jQuery and Style
        add_action( 'wp_enqueue_scripts', array( $this, 'wpRFP_enqueue_assets' ), 999 );
        
        // Include all files from admin directory
        foreach ( glob( wpRFP_ROOT . 'admin/*.php' ) as $lib_filename ) {
            require_once $lib_filename;
        }
        
        // Add Settings Link
        add_filter( 'plugin_action_links_' . wpRFP_BASENAME,  array( $this, 'wpRFP_settings_link' ) );
        
        // Register Widgets
        add_action( 'widgets_init', array( $this, 'wpRFP_register' ) );
    }
    
    /**
     * Add settings link on plugin page
     *
     * @since wpRFP 1.0
     */
    public function wpRFP_settings_link( $links ) {
        $settings_link = '<a href="' . admin_url( 'options-general.php?page=wpRFP-options' ) . '" title="' . __( 'Rename Featured Images', $this->wpRFP_text_domain ) . '">' . __( 'Settings', $this->wpRFP_text_domain ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }
    
    /**
     * Enqueues plugin scripts and styles
     * 
     * @since wpRFP 1.0
     */
    public function wpRFP_enqueue_assets() {
    }
    
    /**
     * Registers all wpRFP
     * 
     * @since rtWidget 1.0
     */
    public function wpRFP_register() {
        $wpRFP_options = get_option( 'wpRFP_options' );
    }
}

/**
 * Instantiate Main Class
 */
global $wpRFP;
$wpRFP = new wpRFP();