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
            'radius' => get_option('em_checkin_radius', 10), // Used by JS for radius parameter
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
        add_shortcode('em_activity_feed', array($this, 'activity_feed_shortcode'));
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
    
    /**
     * Shortcode for displaying user activity feed.
     *
     * @since    1.1.0
     * @param    array    $atts    The shortcode attributes.
     * @return   string            The shortcode output.
     */
    public function activity_feed_shortcode($atts) {
        $atts = shortcode_atts(array(
            'user_id' => get_current_user_id(),
            'limit' => 10,
        ), $atts, 'em_activity_feed');
        
        // If not logged in and no specific user_id provided, show a login message
        if ($atts['user_id'] == 0) {
            return '<div class="em-login-required">' . __('Please log in to view your activity.', 'events-milestones') . '</div>';
        }
        
        // Get user check-ins
        $checkin_obj = new EM_Checkin();
        $user_checkins = $checkin_obj->get_user_checkins($atts['user_id']);
        
        // Get user milestone achievements
        $milestone_obj = new EM_User_Milestone();
        $achieved_milestones = $milestone_obj->get_user_achievements($atts['user_id']);
        
        // Create combined activity array with timestamps for sorting
        $activity_feed = array();
        
        // Add check-ins to activity feed
        foreach ($user_checkins as $checkin) {
            $activity_feed[] = array(
                'type' => 'checkin',
                'event_id' => $checkin['event_id'],
                'title' => $checkin['event_title'],
                'timestamp' => $checkin['timestamp'],
                'date' => $checkin['date'],
            );
        }
        
        // Add milestone achievements to activity feed
        foreach ($achieved_milestones as $milestone) {
            // Get the achievement date from user milestone post
            $args = array(
                'post_type' => 'em_user_milestone',
                'posts_per_page' => 1,
                'meta_query' => array(
                    array(
                        'key' => '_em_user_id',
                        'value' => $atts['user_id'],
                    ),
                    array(
                        'key' => '_em_milestone_id',
                        'value' => $milestone->ID,
                    ),
                ),
            );
            
            $achievement_posts = get_posts($args);
            
            if (!empty($achievement_posts)) {
                $achievement_post = $achievement_posts[0];
                $achievement_date = get_post_meta($achievement_post->ID, '_em_achievement_date', true);
                $achievement_timestamp = strtotime($achievement_date);
                
                $activity_feed[] = array(
                    'type' => 'milestone',
                    'milestone_id' => $milestone->ID,
                    'title' => $milestone->post_title,
                    'timestamp' => $achievement_timestamp,
                    'date' => date_i18n(get_option('date_format'), $achievement_timestamp),
                    'description' => get_post_meta($milestone->ID, '_em_description', true),
                );
            }
        }
        
        // Sort by timestamp (newest first)
        usort($activity_feed, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        
        // Limit the number of items if specified
        if ($atts['limit'] > 0) {
            $activity_feed = array_slice($activity_feed, 0, $atts['limit']);
        }
        
        ob_start();
        include EM_PLUGIN_PATH . 'public/partials/activity-feed-display.php';
        return ob_get_clean();
    }
}