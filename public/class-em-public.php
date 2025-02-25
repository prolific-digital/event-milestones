<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Events_Milestones
 */

class EM_Public {

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
     * @param    string    $plugin_name       The name of the plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, EM_PLUGIN_URL . 'public/css/events-milestones-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, EM_PLUGIN_URL . 'public/js/events-milestones-public.js', array('jquery'), $this->version, false);

        wp_localize_script($this->plugin_name, 'em_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('em_checkin_nonce'),
            'checkin_radius' => get_option('em_checkin_radius', 10),
            'is_user_logged_in' => is_user_logged_in(),
            'i18n' => array(
                'loading' => __('Loading nearby events...', 'events-milestones'),
                'no_events' => __('No events found nearby.', 'events-milestones'),
                'geolocation_error' => __('Unable to get your location.', 'events-milestones'),
                'geolocation_denied' => __('You denied permission to access your location.', 'events-milestones'),
                'geolocation_unavailable' => __('Location information is unavailable.', 'events-milestones'),
                'geolocation_timeout' => __('The request to get your location timed out.', 'events-milestones'),
                'geolocation_unknown' => __('An unknown error occurred while getting your location.', 'events-milestones'),
                'browser_no_geolocation' => __('Your browser does not support geolocation.', 'events-milestones'),
                'checkin_success' => __('Check-in successful!', 'events-milestones'),
                'checkin_error' => __('Check-in failed. Please try again.', 'events-milestones'),
                'achievement_unlocked' => __('Achievement unlocked!', 'events-milestones'),
                'close' => __('Close', 'events-milestones'),
                'check_in' => __('Check In', 'events-milestones'),
            ),
        ));
    }

    /**
     * Register the shortcodes.
     *
     * @since    1.0.0
     */
    public function register_shortcodes() {
        add_shortcode('em_milestones', array($this, 'milestones_shortcode'));
        add_shortcode('em_rewards', array($this, 'rewards_shortcode'));
        add_shortcode('em_checkin_button', array($this, 'checkin_button_shortcode'));
    }

    /**
     * Shortcode for displaying milestones.
     *
     * @since    1.0.0
     * @param    array    $atts    The shortcode attributes.
     * @return   string            The shortcode output.
     */
    public function milestones_shortcode($atts) {
        $atts = shortcode_atts(array(
            'user_id' => get_current_user_id(),
            'show_all' => 'true',
        ), $atts, 'em_milestones');

        // Convert string 'true'/'false' to boolean
        $show_all = filter_var($atts['show_all'], FILTER_VALIDATE_BOOLEAN);
        
        // If not logged in and no specific user_id provided, just show all milestones
        if ($atts['user_id'] == 0 && !$show_all) {
            $show_all = true;
        }

        // Get milestones
        $milestone_obj = new EM_Milestone();
        $milestones = $milestone_obj->get_milestones();

        // Get user milestones if needed
        $user_milestone_obj = new EM_User_Milestone();
        $achieved_milestones = array();
        
        if ($atts['user_id'] > 0) {
            $achieved_milestones = $user_milestone_obj->get_user_achievements($atts['user_id']);
            $achieved_milestone_ids = wp_list_pluck($achieved_milestones, 'ID');
        }

        ob_start();
        include EM_PLUGIN_PATH . 'public/partials/milestones-display.php';
        return ob_get_clean();
    }

    /**
     * Shortcode for displaying rewards.
     *
     * @since    1.0.0
     * @param    array    $atts    The shortcode attributes.
     * @return   string            The shortcode output.
     */
    public function rewards_shortcode($atts) {
        $atts = shortcode_atts(array(
            'user_id' => get_current_user_id(),
            'show_all' => 'true',
        ), $atts, 'em_rewards');

        // Convert string 'true'/'false' to boolean
        $show_all = filter_var($atts['show_all'], FILTER_VALIDATE_BOOLEAN);
        
        // If not logged in and no specific user_id provided, just show all rewards
        if ($atts['user_id'] == 0 && !$show_all) {
            $show_all = true;
        }

        // Get all rewards
        $reward_obj = new EM_Reward();
        $rewards = $reward_obj->get_rewards();

        // Get user rewards if needed
        $user_milestone_obj = new EM_User_Milestone();
        $unlocked_rewards = array();
        
        if ($atts['user_id'] > 0) {
            $unlocked_rewards = $user_milestone_obj->get_user_rewards($atts['user_id']);
            $unlocked_reward_ids = wp_list_pluck($unlocked_rewards, 'ID');
        }

        ob_start();
        include EM_PLUGIN_PATH . 'public/partials/rewards-display.php';
        return ob_get_clean();
    }

    /**
     * Shortcode for displaying check-in button.
     *
     * @since    1.0.0
     * @param    array    $atts    The shortcode attributes.
     * @return   string            The shortcode output.
     */
    public function checkin_button_shortcode($atts) {
        $atts = shortcode_atts(array(
            'text' => __('Check In', 'events-milestones'),
            'class' => 'em-checkin-button',
        ), $atts, 'em_checkin_button');

        ob_start();
        include EM_PLUGIN_PATH . 'public/partials/checkin-button-display.php';
        return ob_get_clean();
    }
}