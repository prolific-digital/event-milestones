<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Events_Milestones
 */

class EM_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        // Set up reset user data handler
        add_action('admin_init', array($this, 'handle_reset_user_data'));
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, EM_PLUGIN_URL . 'admin/css/events-milestones-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, EM_PLUGIN_URL . 'admin/js/events-milestones-admin.js', array('jquery'), $this->version, false);
    }

    /**
     * Add the plugin settings menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        // Add the top-level admin menu
        $main_page = add_menu_page(
            __('Event Milestones', 'events-milestones'),
            __('Event Milestones', 'events-milestones'),
            'manage_options',
            'events-milestones',
            array($this, 'display_plugin_admin_page'),
            'dashicons-flag',
            25
        );

        // Add main dashboard as submenu
        add_submenu_page(
            'events-milestones',
            __('Dashboard', 'events-milestones'),
            __('Dashboard', 'events-milestones'),
            'manage_options',
            'events-milestones',
            array($this, 'display_plugin_admin_page')
        );
        
        // Add link to add new milestone
        add_submenu_page(
            'events-milestones',
            __('Add New Milestone', 'events-milestones'),
            __('Add New Milestone', 'events-milestones'),
            'manage_options',
            'post-new.php?post_type=em_milestone'
        );
        
        // Add link to add new reward
        add_submenu_page(
            'events-milestones',
            __('Add New Reward', 'events-milestones'),
            __('Add New Reward', 'events-milestones'),
            'manage_options',
            'post-new.php?post_type=em_reward'
        );
        
        // Add submenu pages
        add_submenu_page(
            'events-milestones',
            __('Settings', 'events-milestones'),
            __('Settings', 'events-milestones'),
            'manage_options',
            'events-milestones-settings',
            array($this, 'display_plugin_settings_page')
        );

        // Add submenu to the Tribe Events menu as well
        if (class_exists('Tribe__Events__Main')) {
            add_submenu_page(
                'edit.php?post_type=tribe_events',
                __('Event Milestones', 'events-milestones'),
                __('Event Milestones', 'events-milestones'),
                'manage_options',
                'admin.php?page=events-milestones'
            );
        }
    }

    /**
     * Render the main plugin admin page.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        include_once EM_PLUGIN_PATH . 'admin/partials/events-milestones-admin-display.php';
    }

    /**
     * Render the plugin settings page.
     *
     * @since    1.0.0
     */
    public function display_plugin_settings_page() {
        include_once EM_PLUGIN_PATH . 'admin/partials/events-milestones-admin-settings.php';
    }

    /**
     * Register the plugin settings.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // Add a section for multisite settings
        add_settings_section(
            'events_milestones_multisite_section',
            __('Multisite Settings', 'events-milestones'),
            array($this, 'render_multisite_section'),
            'events_milestones_settings'
        );

        // Register multisite settings
        register_setting(
            'events_milestones_settings',
            'em_source_site_id',
            array(
                'type' => 'integer',
                'description' => 'The blog ID to pull events from',
                'sanitize_callback' => array($this, 'sanitize_blog_id'),
            )
        );
        
        // Add field for source site ID
        add_settings_field(
            'em_source_site_id',
            __('Source Blog ID', 'events-milestones'),
            array($this, 'render_source_site_field'),
            'events_milestones_settings',
            'events_milestones_multisite_section'
        );

        // Register check-in settings
        register_setting(
            'events_milestones_settings',
            'em_checkin_radius',
            array(
                'type' => 'integer',
                'sanitize_callback' => 'absint',
                'default' => 10,
            )
        );

        // Add a section for check-in settings
        add_settings_section(
            'events_milestones_checkin_section',
            __('Check-in Settings', 'events-milestones'),
            array($this, 'render_checkin_section'),
            'events_milestones_settings'
        );

        // Add field for check-in radius
        add_settings_field(
            'em_checkin_radius',
            __('Check-in Radius (miles)', 'events-milestones'),
            array($this, 'render_checkin_radius_field'),
            'events_milestones_settings',
            'events_milestones_checkin_section'
        );
    }
    
    // This method has been removed as we now use the standard WordPress Settings API

    /**
     * Sanitize blog ID setting
     *
     * @since    1.0.0
     * @param    mixed    $input    The unsanitized input.
     * @return   int      The sanitized blog ID.
     */
    public function sanitize_blog_id($input) {
        $blog_id = absint($input);
        
        // Make sure the blog exists if we're in a multisite environment
        if (is_multisite() && !get_site($blog_id)) {
            add_settings_error(
                'em_source_site_id',
                'em_invalid_blog_id',
                __('The specified blog ID does not exist.', 'events-milestones'),
                'error'
            );
            
            // Return the current setting or default to current blog
            return get_option('em_source_site_id', get_current_blog_id());
        }
        
        // Check if The Events Calendar is active in this blog
        $tec_active = $this->check_tec_in_blog($blog_id);
        if (!$tec_active) {
            // Show a warning, but allow the setting change
            add_settings_error(
                'em_source_site_id',
                'em_tec_not_active',
                __('The Events Calendar is not active on the selected site. Events may not be displayed correctly.', 'events-milestones'),
                'warning'
            );
        }
        
        return $blog_id;
    }
    
    /**
     * Handle the reset user data action from settings page
     * 
     * @since    1.0.0
     */
    public function handle_reset_user_data() {
        // Check if we're processing a reset request
        if (!isset($_POST['em_action']) || $_POST['em_action'] !== 'reset_user_data') {
            return;
        }
        
        // Verify nonce
        if (!isset($_POST['em_reset_nonce']) || !wp_verify_nonce($_POST['em_reset_nonce'], 'em_reset_user_data')) {
            add_settings_error(
                'events_milestones_settings',
                'em_nonce_error',
                __('Security check failed. Please try again.', 'events-milestones'),
                'error'
            );
            return;
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            add_settings_error(
                'events_milestones_settings',
                'em_permission_error',
                __('You do not have permission to perform this action.', 'events-milestones'),
                'error'
            );
            return;
        }
        
        // Get and validate user ID
        $user_id = isset($_POST['em_user_id']) ? intval($_POST['em_user_id']) : 0;
        if (!$user_id || !get_user_by('id', $user_id)) {
            add_settings_error(
                'events_milestones_settings',
                'em_invalid_user',
                __('Invalid user selected.', 'events-milestones'),
                'error'
            );
            return;
        }
        
        // Check what to reset
        $reset_checkins = isset($_POST['em_reset_checkins']) && $_POST['em_reset_checkins'] == '1';
        $reset_milestones = isset($_POST['em_reset_milestones']) && $_POST['em_reset_milestones'] == '1';
        
        if (!$reset_checkins && !$reset_milestones) {
            add_settings_error(
                'events_milestones_settings',
                'em_nothing_selected',
                __('Please select at least one item to reset.', 'events-milestones'),
                'error'
            );
            return;
        }
        
        // Reset check-ins if requested
        if ($reset_checkins) {
            $this->reset_user_checkins($user_id);
        }
        
        // Reset milestones if requested
        if ($reset_milestones) {
            $this->reset_user_milestones($user_id);
        }
        
        // Success message
        add_settings_error(
            'events_milestones_settings',
            'em_reset_success',
            __('User data has been reset successfully.', 'events-milestones'),
            'success'
        );
    }
    
    /**
     * Reset a user's check-ins
     * 
     * @since    1.0.0
     * @param    int    $user_id    The user ID
     */
    private function reset_user_checkins($user_id) {
        // Delete the user meta that stores check-ins
        delete_user_meta($user_id, 'em_event_checkins');
        
        // Log the action
        error_log("Events Milestones: Reset check-ins for user #$user_id");
    }
    
    /**
     * Reset a user's milestone achievements
     * 
     * @since    1.0.0
     * @param    int    $user_id    The user ID
     */
    private function reset_user_milestones($user_id) {
        // Delete the user meta that stores milestone achievements
        delete_user_meta($user_id, 'em_achieved_milestones');
        
        // Get all user milestone posts for this user
        $user_milestone_posts = get_posts(array(
            'post_type' => 'em_user_milestone',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_em_user_id',
                    'value' => $user_id,
                    'compare' => '='
                )
            )
        ));
        
        // Delete each user milestone post
        foreach ($user_milestone_posts as $post) {
            wp_delete_post($post->ID, true); // true = force delete, bypass trash
        }
        
        // Log the action
        error_log("Events Milestones: Reset milestones for user #$user_id - Deleted " . count($user_milestone_posts) . " records.");
    }
    
    /**
     * Check if The Events Calendar is active in a blog
     *
     * @since    1.0.0
     * @param    int     $blog_id    The blog ID to check.
     * @return   bool                True if active, false otherwise.
     */
    private function check_tec_in_blog($blog_id) {
        // If not multisite or blog_id is current site, check directly
        if (!is_multisite() || $blog_id == get_current_blog_id()) {
            return class_exists('Tribe__Events__Main');
        }
        
        // Switch to the blog to check
        switch_to_blog($blog_id);
        $is_active = class_exists('Tribe__Events__Main');
        restore_current_blog();
        
        return $is_active;
    }

    /**
     * Render the multisite settings section.
     *
     * @since    1.0.0
     */
    public function render_multisite_section() {
        echo '<p>' . __('Configure multisite integration. These settings only apply if you are using WordPress multisite.', 'events-milestones') . '</p>';
    }

    /**
     * Render the source site field.
     *
     * @since    1.0.0
     */
    public function render_source_site_field() {
        $blog_id = get_option('em_source_site_id', get_current_blog_id());
        
        echo '<input type="number" min="1" name="em_source_site_id" value="' . esc_attr($blog_id) . '" class="regular-text" />';
        
        // If we're in a multisite environment, show a list of available sites
        if (is_multisite()) {
            $sites = get_sites(array('fields' => 'ids'));
            if (!empty($sites)) {
                echo '<p class="description">' . __('Available blog IDs: ', 'events-milestones');
                foreach ($sites as $site_id) {
                    $site = get_site($site_id);
                    echo '<code>' . $site_id . '</code> (' . $site->blogname . ') ';
                }
                echo '</p>';
            }
        }
        
        echo '<p class="description">' . __('The blog ID to pull events from. In a multisite environment, this determines which site\'s events will be displayed.', 'events-milestones') . '</p>';
        echo '<p class="description">' . __('Note: The selected blog must have The Events Calendar plugin active.', 'events-milestones') . '</p>';
    }

    /**
     * Render the check-in settings section.
     *
     * @since    1.0.0
     */
    public function render_checkin_section() {
        echo '<p>' . __('Configure settings for user check-ins.', 'events-milestones') . '</p>';
    }

    /**
     * Render the check-in radius field.
     *
     * @since    1.0.0
     */
    public function render_checkin_radius_field() {
        $radius = get_option('em_checkin_radius', 10);
        
        echo '<input type="number" id="em_checkin_radius" name="em_checkin_radius" value="' . esc_attr($radius) . '" min="1" step="1" />';
        echo '<p class="description">' . __('The maximum distance (in miles) that a user can be from an event venue to check in.', 'events-milestones') . '</p>';
    }
}