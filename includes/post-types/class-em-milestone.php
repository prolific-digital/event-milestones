<?php
/**
 * The Milestone post type.
 *
 * @since      1.0.0
 * @package    Events_Milestones
 */
class EM_Milestone {

    /**
     * Register the Milestone post type.
     *
     * @since    1.0.0
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x('Milestones', 'Post Type General Name', 'events-milestones'),
            'singular_name'         => _x('Milestone', 'Post Type Singular Name', 'events-milestones'),
            'menu_name'             => __('Milestones', 'events-milestones'),
            'name_admin_bar'        => __('Milestone', 'events-milestones'),
            'archives'              => __('Milestone Archives', 'events-milestones'),
            'attributes'            => __('Milestone Attributes', 'events-milestones'),
            'parent_item_colon'     => __('Parent Milestone:', 'events-milestones'),
            'all_items'             => __('All Milestones', 'events-milestones'),
            'add_new_item'          => __('Add New Milestone', 'events-milestones'),
            'add_new'               => __('Add New', 'events-milestones'),
            'new_item'              => __('New Milestone', 'events-milestones'),
            'edit_item'             => __('Edit Milestone', 'events-milestones'),
            'update_item'           => __('Update Milestone', 'events-milestones'),
            'view_item'             => __('View Milestone', 'events-milestones'),
            'view_items'            => __('View Milestones', 'events-milestones'),
            'search_items'          => __('Search Milestone', 'events-milestones'),
            'not_found'             => __('Not found', 'events-milestones'),
            'not_found_in_trash'    => __('Not found in Trash', 'events-milestones'),
            'featured_image'        => __('Featured Image', 'events-milestones'),
            'set_featured_image'    => __('Set featured image', 'events-milestones'),
            'remove_featured_image' => __('Remove featured image', 'events-milestones'),
            'use_featured_image'    => __('Use as featured image', 'events-milestones'),
            'insert_into_item'      => __('Insert into milestone', 'events-milestones'),
            'uploaded_to_this_item' => __('Uploaded to this milestone', 'events-milestones'),
            'items_list'            => __('Milestones list', 'events-milestones'),
            'items_list_navigation' => __('Milestones list navigation', 'events-milestones'),
            'filter_items_list'     => __('Filter milestones list', 'events-milestones'),
        );
        
        $args = array(
            'label'                 => __('Milestone', 'events-milestones'),
            'description'           => __('Event milestones', 'events-milestones'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail'),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 25,
            'menu_icon'             => 'dashicons-flag',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'page',
        );
        
        register_post_type('em_milestone', $args);
    }

    /**
     * Add meta boxes for the Milestone post type.
     *
     * @since    1.0.0
     */
    public function add_meta_boxes() {
        add_meta_box(
            'em_milestone_criteria',
            __('Milestone Criteria', 'events-milestones'),
            array($this, 'render_criteria_meta_box'),
            'em_milestone',
            'normal',
            'high'
        );

        add_meta_box(
            'em_milestone_rewards',
            __('Milestone Rewards', 'events-milestones'),
            array($this, 'render_rewards_meta_box'),
            'em_milestone',
            'normal',
            'high'
        );
    }

    /**
     * Render the criteria meta box.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_criteria_meta_box($post) {
        // Add a nonce field
        wp_nonce_field('em_milestone_criteria_meta_box', 'em_milestone_criteria_meta_box_nonce');

        // Get existing values
        $criteria_type = get_post_meta($post->ID, '_em_criteria_type', true);
        $event_count = get_post_meta($post->ID, '_em_event_count', true);
        $timeframe_days = get_post_meta($post->ID, '_em_timeframe_days', true);
        $specific_events = get_post_meta($post->ID, '_em_specific_events', true);

        if (!is_array($specific_events)) {
            $specific_events = array();
        }

        // Get all events for dropdown
        $multisite = new EM_Multisite();
        $events = $multisite->get_events();
        ?>
        <p>
            <label for="em_criteria_type"><?php _e('Criteria Type:', 'events-milestones'); ?></label>
            <select id="em_criteria_type" name="em_criteria_type">
                <option value="event_count" <?php selected($criteria_type, 'event_count'); ?>><?php _e('Number of Events', 'events-milestones'); ?></option>
                <option value="timeframe" <?php selected($criteria_type, 'timeframe'); ?>><?php _e('Events in Timeframe', 'events-milestones'); ?></option>
                <option value="specific_events" <?php selected($criteria_type, 'specific_events'); ?>><?php _e('Specific Events', 'events-milestones'); ?></option>
            </select>
        </p>

        <div id="criteria_event_count" class="criteria-section" <?php echo $criteria_type != 'event_count' ? 'style="display: none;"' : ''; ?>>
            <p>
                <label for="em_event_count"><?php _e('Number of Events:', 'events-milestones'); ?></label>
                <input type="number" id="em_event_count" name="em_event_count" value="<?php echo esc_attr($event_count ? $event_count : 1); ?>" min="1" step="1" required />
                <span class="description"><?php _e('The number of events a user must check in to.', 'events-milestones'); ?></span>
            </p>
        </div>

        <div id="criteria_timeframe" class="criteria-section" <?php echo $criteria_type != 'timeframe' ? 'style="display: none;"' : ''; ?>>
            <p>
                <label for="em_event_count_timeframe"><?php _e('Number of Events:', 'events-milestones'); ?></label>
                <input type="number" id="em_event_count_timeframe" name="em_event_count" value="<?php echo esc_attr($event_count ? $event_count : 1); ?>" min="1" step="1" required />
                <span class="description"><?php _e('The number of events a user must check in to within the timeframe.', 'events-milestones'); ?></span>
            </p>
            <p>
                <label for="em_timeframe_days"><?php _e('Timeframe (days):', 'events-milestones'); ?></label>
                <input type="number" id="em_timeframe_days" name="em_timeframe_days" value="<?php echo esc_attr($timeframe_days ? $timeframe_days : 7); ?>" min="1" step="1" required />
                <span class="description"><?php _e('The timeframe in days within which the user must check in to the specified number of events.', 'events-milestones'); ?></span>
            </p>
        </div>

        <div id="criteria_specific_events" class="criteria-section" <?php echo $criteria_type != 'specific_events' ? 'style="display: none;"' : ''; ?>>
            <p>
                <label><?php _e('Select Specific Events:', 'events-milestones'); ?></label>
                <div class="specific-events-container">
                    <?php foreach ($events as $event) : ?>
                        <label>
                            <input type="checkbox" name="em_specific_events[]" value="<?php echo esc_attr($event->ID); ?>" 
                                <?php checked(in_array($event->ID, $specific_events)); ?>>
                            <?php echo esc_html($event->post_title); ?>
                        </label><br>
                    <?php endforeach; ?>
                </div>
                <span class="description"><?php _e('Select the specific events a user must check in to in order to achieve this milestone.', 'events-milestones'); ?></span>
            </p>
        </div>

        <script>
            jQuery(document).ready(function($) {
                // Function to update required attributes based on visible section
                function updateRequiredFields() {
                    var criteriaType = $('#em_criteria_type').val();
                    
                    // Remove required attribute from all inputs first
                    $('#em_event_count, #em_event_count_timeframe, #em_timeframe_days').prop('required', false);
                    
                    // Add required attribute only to inputs in the visible section
                    if (criteriaType === 'event_count') {
                        $('#em_event_count').prop('required', true);
                    } else if (criteriaType === 'timeframe') {
                        $('#em_event_count_timeframe, #em_timeframe_days').prop('required', true);
                    } else if (criteriaType === 'specific_events') {
                        // No number inputs required for specific events
                    }
                }
                
                // Handle criteria type change
                $('#em_criteria_type').on('change', function() {
                    var criteriaType = $(this).val();
                    $('.criteria-section').hide();
                    $('#criteria_' + criteriaType).show();
                    
                    // Update required fields
                    updateRequiredFields();
                });
                
                // Initialize on page load
                updateRequiredFields();
            });
        </script>
        <?php
    }

    /**
     * Render the rewards meta box.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_rewards_meta_box($post) {
        // Add a nonce field
        wp_nonce_field('em_milestone_rewards_meta_box', 'em_milestone_rewards_meta_box_nonce');

        // Get existing values
        $rewards = get_post_meta($post->ID, '_em_rewards', true);

        if (!is_array($rewards)) {
            $rewards = array();
        }

        // Get all rewards for dropdown
        $reward_posts = get_posts(array(
            'post_type' => 'em_reward',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));
        ?>
        <p>
            <label><?php _e('Select Rewards:', 'events-milestones'); ?></label>
            <div class="rewards-container">
                <?php if (empty($reward_posts)) : ?>
                    <p><?php _e('No rewards available. Please create some rewards first.', 'events-milestones'); ?></p>
                <?php else : ?>
                    <?php foreach ($reward_posts as $reward) : ?>
                        <label>
                            <input type="checkbox" name="em_rewards[]" value="<?php echo esc_attr($reward->ID); ?>" 
                                <?php checked(in_array($reward->ID, $rewards)); ?>>
                            <?php echo esc_html($reward->post_title); ?>
                        </label><br>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <span class="description"><?php _e('Select the rewards to be unlocked when this milestone is achieved.', 'events-milestones'); ?></span>
        </p>
        <?php
    }

    /**
     * Save meta box data.
     *
     * @since    1.0.0
     * @param    int    $post_id    The post ID.
     */
    public function save_meta_boxes($post_id) {
        // Check if our nonce is set for criteria
        if (!isset($_POST['em_milestone_criteria_meta_box_nonce'])) {
            return;
        }

        // Verify that the nonce is valid
        if (!wp_verify_nonce($_POST['em_milestone_criteria_meta_box_nonce'], 'em_milestone_criteria_meta_box')) {
            return;
        }

        // Check if our nonce is set for rewards
        if (!isset($_POST['em_milestone_rewards_meta_box_nonce'])) {
            return;
        }

        // Verify that the nonce is valid
        if (!wp_verify_nonce($_POST['em_milestone_rewards_meta_box_nonce'], 'em_milestone_rewards_meta_box')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save criteria data
        if (isset($_POST['em_criteria_type'])) {
            update_post_meta($post_id, '_em_criteria_type', sanitize_text_field($_POST['em_criteria_type']));
        }

        // Make sure we have a valid event count (greater than zero)
        if (isset($_POST['em_event_count']) && intval($_POST['em_event_count']) > 0) {
            update_post_meta($post_id, '_em_event_count', intval($_POST['em_event_count']));
        }

        if (isset($_POST['em_timeframe_days']) && intval($_POST['em_timeframe_days']) > 0) {
            update_post_meta($post_id, '_em_timeframe_days', intval($_POST['em_timeframe_days']));
        }

        if (isset($_POST['em_specific_events'])) {
            update_post_meta($post_id, '_em_specific_events', array_map('intval', $_POST['em_specific_events']));
        } else {
            update_post_meta($post_id, '_em_specific_events', array());
        }

        // Save rewards data
        if (isset($_POST['em_rewards'])) {
            update_post_meta($post_id, '_em_rewards', array_map('intval', $_POST['em_rewards']));
        } else {
            update_post_meta($post_id, '_em_rewards', array());
        }
    }

    /**
     * Check if a user has achieved a milestone.
     *
     * @since    1.0.0
     * @param    int       $milestone_id    The milestone ID.
     * @param    int       $user_id         The user ID.
     * @return   bool                       True if the user has achieved the milestone, false otherwise.
     */
    public function check_milestone_achievement($milestone_id, $user_id) {
        // Get milestone criteria
        $criteria_type = get_post_meta($milestone_id, '_em_criteria_type', true);
        $event_count = get_post_meta($milestone_id, '_em_event_count', true);
        $timeframe_days = get_post_meta($milestone_id, '_em_timeframe_days', true);
        $specific_events = get_post_meta($milestone_id, '_em_specific_events', true);

        // Get user check-ins
        $checkin = new EM_Checkin();
        $user_checkins = $checkin->get_user_checkins($user_id);

        // Check milestone criteria
        switch ($criteria_type) {
            case 'event_count':
                return count($user_checkins) >= $event_count;

            case 'timeframe':
                return $checkin->count_user_checkins($user_id, $timeframe_days) >= $event_count;

            case 'specific_events':
                if (!is_array($specific_events) || empty($specific_events)) {
                    return false;
                }

                // Create array of user's checked-in event IDs
                $checked_event_ids = array_column($user_checkins, 'event_id');
                
                // Check if all required events are in the user's checked-in events
                $missing_events = array_diff($specific_events, $checked_event_ids);
                return empty($missing_events);

            default:
                return false;
        }
    }

    /**
     * Get all available milestones.
     *
     * @since    1.0.0
     * @return   array    Array of milestone posts.
     */
    public function get_milestones() {
        return get_posts(array(
            'post_type' => 'em_milestone',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));
    }

    /**
     * Get rewards for a milestone.
     *
     * @since    1.0.0
     * @param    int       $milestone_id    The milestone ID.
     * @return   array                     Array of reward post objects.
     */
    public function get_milestone_rewards($milestone_id) {
        $reward_ids = get_post_meta($milestone_id, '_em_rewards', true);
        
        if (!is_array($reward_ids) || empty($reward_ids)) {
            return array();
        }

        return get_posts(array(
            'post_type' => 'em_reward',
            'posts_per_page' => -1,
            'post__in' => $reward_ids,
            'orderby' => 'title',
            'order' => 'ASC',
        ));
    }
}