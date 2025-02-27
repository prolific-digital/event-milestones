<?php
/**
 * Plugin Name: Events Milestones
 * Plugin URI: https://prolificdigital.com
 * Description: An add-on for The Events Calendar that introduces a system for managing event-related milestones and rewards.
 * Version: 1.1.0
 * Author: Prolific Digital
 * Author URI: https://prolificdigital.com
 * Text Domain: events-milestones
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * 
 * This plugin is an add-on for The Events Calendar.
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('EM_VERSION', '1.1.0');
define('EM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('EM_PLUGIN_BASENAME', plugin_basename(__FILE__));

// We no longer need to define constants for source site ID
// as we're using standard WordPress options API

/**
 * Check if The Events Calendar is active in target site
 * 
 * @param int $blog_id The blog ID to check, defaults to current blog
 * @return bool Whether The Events Calendar is active in the specified blog
 */
function em_check_tec_in_blog($blog_id = null) {
    if (is_null($blog_id)) {
        $blog_id = get_current_blog_id();
    }
    
    // For current blog, check if The Events Calendar is active locally
    if ($blog_id == get_current_blog_id()) {
        // First try checking if the tribe_events post type exists
        if (post_type_exists('tribe_events')) {
            return true;
        }
        
        // As a fallback, check for the class
        if (class_exists('Tribe__Events__Main')) {
            return true;
        }
        
        return false;
    }
    
    // For remote blog in multisite, we need a different approach
    if (is_multisite()) {
        global $wpdb;
        
        // Get the blog's table prefix
        $blog_prefix = $wpdb->get_blog_prefix($blog_id);
        
        // Check if the blog exists
        if (!get_site($blog_id)) {
            return false;
        }
        
        // Check if the post type exists in this blog by looking for posts of that type
        $query = $wpdb->prepare(
            "SELECT COUNT(*) 
             FROM {$blog_prefix}posts 
             WHERE post_type = %s 
             LIMIT 1",
            'tribe_events'
        );
        
        error_log("EM Debug: Running direct DB query to check for tribe_events post type: " . $query);
        $count = $wpdb->get_var($query);
        error_log("EM Debug: Result of tribe_events post count: " . $count);
        
        return ($count > 0);
    }
    
    return false;
}

// Check if The Events Calendar is active
function em_check_dependencies() {
    if (!class_exists('Tribe__Events__Main')) {
        add_action('admin_notices', 'em_dependency_notice');
        return false;
    }
    return true;
}

// Dependency notice
function em_dependency_notice() {
    ?>
    <div class="notice notice-warning is-dismissible">
        <p><?php _e('Events Milestones works best with The Events Calendar plugin. Please install and activate it for full functionality.', 'events-milestones'); ?></p>
    </div>
    <?php
}

// Include required files
require_once EM_PLUGIN_PATH . 'includes/class-em-loader.php';
require_once EM_PLUGIN_PATH . 'includes/class-events-milestones.php';
require_once EM_PLUGIN_PATH . 'includes/class-em-multisite.php';

// Initialize the plugin
function em_init() {
    $plugin = new Events_Milestones();
    $plugin->run();
    
    // Display notice if dependency is not met
    em_check_dependencies();
    
    // Clean up old files from previous implementations
    em_cleanup_old_files();
}

// Clean up old files from previous implementations
function em_cleanup_old_files() {
    $files_to_remove = array(
        __DIR__ . '/em-constants.php',
        __DIR__ . '/source_site_id.txt'
    );
    
    // Also check the uploads directory
    $upload_dir = wp_upload_dir();
    $files_to_remove[] = $upload_dir['basedir'] . '/em_source_site_id.txt';
    
    foreach ($files_to_remove as $file) {
        if (file_exists($file)) {
            @unlink($file);
        }
    }
}

add_action('plugins_loaded', 'em_init');