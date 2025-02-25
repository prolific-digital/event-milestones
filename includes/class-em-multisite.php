<?php
/**
 * Handles multisite functionality for the plugin.
 *
 * @since      1.0.0
 * @package    Events_Milestones
 */
class EM_Multisite {

    /**
     * The ID of the site to pull events from.
     *
     * @since    1.0.0
     * @access   private
     * @var      int    $source_site_id    The ID of the site to pull events from.
     */
    private $source_site_id;

    /**
     * Initialize the class.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Don't store source_site_id as a property anymore
        // We'll get it fresh each time we need it
    }

    /**
     * Get the source site ID.
     *
     * @since    1.0.0
     * @return   int    The source site ID.
     */
    public function get_source_site_id() {
        $blog_id = get_option('em_source_site_id', get_current_blog_id());
        
        // Ensure the blog ID is valid
        if (is_multisite() && !get_site($blog_id)) {
            // Default to current blog if the configured one doesn't exist
            $blog_id = get_current_blog_id();
        }
        
        return $blog_id;
    }

    /**
     * Set the source site ID.
     *
     * @since    1.0.0
     * @param    int    $site_id    The source site ID.
     */
    public function set_source_site_id($site_id) {
        update_option('em_source_site_id', $site_id);
    }

    /**
     * Check if multisite is enabled.
     *
     * @since    1.0.0
     * @return   bool    True if multisite is enabled, false otherwise.
     */
    public function is_multisite() {
        return is_multisite();
    }

    /**
     * Get events from the source site.
     *
     * @since    1.0.0
     * @param    array    $args    Arguments to pass to get_posts().
     * @return   array             Array of event posts.
     */
    public function get_events($args = array()) {
        // Get the source site ID
        $source_site_id = $this->get_source_site_id();
        $current_site_id = get_current_blog_id();
        
        error_log("EM Debug: get_events - Source site ID: {$source_site_id}, Current site ID: {$current_site_id}");
        
        if (!$this->is_multisite()) {
            error_log("EM Debug: Not multisite, using local events");
            return $this->get_local_events($args);
        }
        
        if ($source_site_id == $current_site_id) {
            error_log("EM Debug: Source site is current site, using local events");
            return $this->get_local_events($args);
        }

        // Make sure the site exists in the network
        if (!$this->site_exists($source_site_id)) {
            error_log("EM Debug: Source site {$source_site_id} does not exist, using local events");
            return $this->get_local_events($args);
        }

        error_log("EM Debug: Switching to source site {$source_site_id} to get events");
        
        // Switch to the source site to get events
        $switched = switch_to_blog($source_site_id);
        
        if (!$switched) {
            error_log("EM Debug: Failed to switch to source site {$source_site_id}, using local events");
            return $this->get_local_events($args);
        }

        // Limit query size to avoid memory issues
        if (!isset($args['posts_per_page'])) {
            $args['posts_per_page'] = 50; // Reasonable limit 
        }
        
        // Force the post type to be 'tribe_events' since we've verified it exists via DB check
        $args['post_type'] = 'tribe_events';
        error_log("EM Debug: Force using 'tribe_events' post type for query");

        $events = $this->get_local_events($args);
        error_log("EM Debug: Found " . count($events) . " events from source site {$source_site_id}");

        // Switch back to the current site
        restore_current_blog();

        return $events;
    }
    
    /**
     * Check if a site exists in the network
     *
     * @since    1.0.0
     * @param    int    $site_id    The site ID to check
     * @return   bool               True if site exists, false otherwise
     */
    private function site_exists($site_id) {
        if (!is_multisite()) {
            return $site_id == get_current_blog_id();
        }
        
        // Use the WordPress API function to check if site exists
        $site = get_site($site_id);
        return $site !== null;
    }

    /**
     * Get events from the current site.
     *
     * @since    1.0.0
     * @param    array    $args    Arguments to pass to get_posts().
     * @return   array             Array of event posts.
     */
    private function get_local_events($args = array()) {
        $current_blog_id = get_current_blog_id();
        error_log("EM Debug: get_local_events on blog ID: {$current_blog_id}");
        
        // Force the post type to be 'tribe_events' since we already verified it exists in the DB
        $event_post_type = 'tribe_events';
        
        // Set default args
        $default_args = array(
            'post_type' => $event_post_type,
            'post_status' => 'publish',
            'posts_per_page' => 50, // Limit for performance
            'orderby' => 'meta_value',
            'meta_key' => '_EventStartDate',
            'order' => 'ASC',
        );

        // Merge default args with user args
        $args = wp_parse_args($args, $default_args);
        
        error_log("EM Debug: Searching for events with args: " . json_encode($args));

        // Try using direct SQL since post type registration may not be complete in switched blog
        global $wpdb;
        
        try {
            $meta_query = isset($args['meta_query']) ? $args['meta_query'] : array();
            $start_date_condition = '';
            
            // Process meta query for start date
            foreach ($meta_query as $query) {
                if (isset($query['key']) && $query['key'] === '_EventStartDate' && isset($query['value'])) {
                    $start_date = $query['value'];
                    $start_date_condition = $wpdb->prepare(" AND pm.meta_value >= %s", $start_date);
                    break;
                }
            }
            
            // Get post limit
            $limit = isset($args['posts_per_page']) ? intval($args['posts_per_page']) : 50;
            
            // Direct SQL query to get events
            $events_query = $wpdb->prepare(
                "SELECT DISTINCT p.* 
                FROM {$wpdb->posts} p
                JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = %s
                AND p.post_status = 'publish'
                AND pm.meta_key = '_EventStartDate'
                {$start_date_condition}
                ORDER BY pm.meta_value ASC
                LIMIT %d",
                $event_post_type,
                $limit
            );
            
            error_log("EM Debug: Direct SQL query: " . $events_query);
            
            $event_objects = $wpdb->get_results($events_query);
            $events = array();
            
            // Convert DB results to proper WP_Post objects
            foreach ($event_objects as $event) {
                $events[] = new WP_Post($event);
            }
            
            error_log("EM Debug: Found " . count($events) . " events on blog ID: {$current_blog_id} using direct SQL");
            
            if (empty($events)) {
                // Fallback to get_posts if direct SQL returns nothing
                error_log("EM Debug: No events found with direct SQL, trying get_posts as fallback");
                try {
                    $events = get_posts($args);
                    error_log("EM Debug: Fallback get_posts found " . count($events) . " events");
                } catch (Exception $e) {
                    error_log("EM Error: Exception in fallback get_posts: " . $e->getMessage());
                }
            }
            
            // Debug first event
            if (count($events) > 0) {
                $first_event = $events[0];
                error_log("EM Debug: First event: ID=" . $first_event->ID . ", Title=" . $first_event->post_title);
                
                // Check if the first event has a venue
                $venue_id = get_post_meta($first_event->ID, '_EventVenueID', true);
                if ($venue_id) {
                    $venue_lat = get_post_meta($venue_id, '_VenueLat', true);
                    $venue_lng = get_post_meta($venue_id, '_VenueLng', true);
                    error_log("EM Debug: First event venue ID: {$venue_id}, Lat: {$venue_lat}, Lng: {$venue_lng}");
                } else {
                    error_log("EM Debug: First event has no venue");
                }
            }
            
            return $events;
        } catch (Exception $e) {
            error_log("EM Error: Exception in get_local_events: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Get sites available in the network.
     *
     * @since    1.0.0
     * @return   array    Array of site information.
     */
    public function get_network_sites() {
        // This method is now deprecated in favor of manual source_site_id input
        // to prevent memory issues in large multisite networks
        return array();
    }

    /**
     * Check if a site has The Events Calendar activated.
     *
     * @since    1.0.0
     * @param    int    $site_id    The site ID to check.
     * @return   bool               True if site has Events Calendar, false otherwise.
     */
    private function site_has_events_calendar($site_id) {
        // Switch to the site to check
        $current_site_id = get_current_blog_id();
        switch_to_blog($site_id);

        $has_events = class_exists('Tribe__Events__Main');

        // Switch back to the current site
        switch_to_blog($current_site_id);

        return $has_events;
    }

    /**
     * Get an event by ID from the source site.
     *
     * @since    1.0.0
     * @param    int     $event_id    The event ID.
     * @return   WP_Post              The event post object or null if not found.
     */
    public function get_event($event_id) {
        // Get the source site ID
        $source_site_id = $this->get_source_site_id();
        $current_site_id = get_current_blog_id();
        
        if (!$this->is_multisite()) {
            return get_post($event_id);
        }
        
        if ($source_site_id == $current_site_id) {
            return get_post($event_id);
        }

        // Make sure the site exists in the network
        if (!$this->site_exists($source_site_id)) {
            return get_post($event_id);
        }

        // Switch to the source site to get the event
        $switched = switch_to_blog($source_site_id);
        
        if (!$switched) {
            return get_post($event_id);
        }

        $event = get_post($event_id);

        // Switch back to the current site
        restore_current_blog();

        return $event;
    }
}