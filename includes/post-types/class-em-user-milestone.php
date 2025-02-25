<?php
/**
 * The User Milestone post type.
 *
 * @since      1.0.0
 * @package    Events_Milestones
 */
class EM_User_Milestone {

    /**
     * Register the User Milestone post type.
     *
     * @since    1.0.0
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x('User Milestones', 'Post Type General Name', 'events-milestones'),
            'singular_name'         => _x('User Milestone', 'Post Type Singular Name', 'events-milestones'),
            'menu_name'             => __('User Milestones', 'events-milestones'),
            'name_admin_bar'        => __('User Milestone', 'events-milestones'),
            'archives'              => __('User Milestone Archives', 'events-milestones'),
            'attributes'            => __('User Milestone Attributes', 'events-milestones'),
            'parent_item_colon'     => __('Parent User Milestone:', 'events-milestones'),
            'all_items'             => __('User Milestones', 'events-milestones'),
            'add_new_item'          => __('Add New User Milestone', 'events-milestones'),
            'add_new'               => __('Add New', 'events-milestones'),
            'new_item'              => __('New User Milestone', 'events-milestones'),
            'edit_item'             => __('Edit User Milestone', 'events-milestones'),
            'update_item'           => __('Update User Milestone', 'events-milestones'),
            'view_item'             => __('View User Milestone', 'events-milestones'),
            'view_items'            => __('View User Milestones', 'events-milestones'),
            'search_items'          => __('Search User Milestone', 'events-milestones'),
            'not_found'             => __('Not found', 'events-milestones'),
            'not_found_in_trash'    => __('Not found in Trash', 'events-milestones'),
        );
        
        $args = array(
            'label'                 => __('User Milestone', 'events-milestones'),
            'description'           => __('User achievement tracking', 'events-milestones'),
            'labels'                => $labels,
            'supports'              => array('title'),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => 'events-milestones',
            'menu_position'         => 27,
            'menu_icon'             => 'dashicons-awards',
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capability_type'       => 'page',
        );
        
        register_post_type('em_user_milestone', $args);
    }

    /**
     * Check if a user has achieved any milestones.
     *
     * @since    1.0.0
     * @param    int       $user_id    The user ID.
     * @return   array                 Array of milestone IDs that were achieved.
     */
    public function check_user_milestones($user_id) {
        // Get all milestones
        $milestone_obj = new EM_Milestone();
        $milestones = $milestone_obj->get_milestones();

        $achieved_milestones = array();

        foreach ($milestones as $milestone) {
            // Skip if already achieved
            if ($this->has_achieved_milestone($user_id, $milestone->ID)) {
                continue;
            }

            // Check if user has achieved this milestone
            if ($milestone_obj->check_milestone_achievement($milestone->ID, $user_id)) {
                // Record the achievement
                $this->record_milestone_achievement($user_id, $milestone->ID);
                $achieved_milestones[] = $milestone->ID;
            }
        }

        return $achieved_milestones;
    }

    /**
     * Record a milestone achievement for a user.
     *
     * @since    1.0.0
     * @param    int    $user_id       The user ID.
     * @param    int    $milestone_id  The milestone ID.
     * @return   int                   The user milestone post ID.
     */
    public function record_milestone_achievement($user_id, $milestone_id) {
        $milestone = get_post($milestone_id);
        $user = get_user_by('id', $user_id);

        // Create the user milestone post
        $post_id = wp_insert_post(array(
            'post_title'  => sprintf(__('User %s achieved milestone: %s', 'events-milestones'), $user->user_login, $milestone->post_title),
            'post_status' => 'publish',
            'post_type'   => 'em_user_milestone',
            'post_author' => $user_id,
        ));

        // Store metadata
        update_post_meta($post_id, '_em_user_id', $user_id);
        update_post_meta($post_id, '_em_milestone_id', $milestone_id);
        update_post_meta($post_id, '_em_achievement_date', current_time('mysql'));

        // Update user meta for quick access
        $user_milestones = get_user_meta($user_id, 'em_achieved_milestones', true);
        if (!is_array($user_milestones)) {
            $user_milestones = array();
        }
        $user_milestones[] = $milestone_id;
        update_user_meta($user_id, 'em_achieved_milestones', array_unique($user_milestones));

        // Fire action for milestone achievement
        do_action('em_user_milestone_achieved', $user_id, $milestone_id, $post_id);

        return $post_id;
    }

    /**
     * Check if a user has achieved a milestone.
     *
     * @since    1.0.0
     * @param    int    $user_id       The user ID.
     * @param    int    $milestone_id  The milestone ID.
     * @return   bool                  True if the user has achieved the milestone, false otherwise.
     */
    public function has_achieved_milestone($user_id, $milestone_id) {
        $user_milestones = get_user_meta($user_id, 'em_achieved_milestones', true);
        
        if (!is_array($user_milestones)) {
            return false;
        }

        return in_array($milestone_id, $user_milestones);
    }

    /**
     * Get all milestones achieved by a user.
     *
     * @since    1.0.0
     * @param    int       $user_id    The user ID.
     * @return   array                 Array of milestone post objects.
     */
    public function get_user_achievements($user_id) {
        $user_milestones = get_user_meta($user_id, 'em_achieved_milestones', true);
        
        if (!is_array($user_milestones) || empty($user_milestones)) {
            return array();
        }

        return get_posts(array(
            'post_type' => 'em_milestone',
            'posts_per_page' => -1,
            'post__in' => $user_milestones,
            'orderby' => 'title',
            'order' => 'ASC',
        ));
    }

    /**
     * Get all rewards unlocked by a user via achieved milestones.
     *
     * @since    1.0.0
     * @param    int       $user_id    The user ID.
     * @return   array                 Array of reward post objects.
     */
    public function get_user_rewards($user_id) {
        $user_milestones = get_user_meta($user_id, 'em_achieved_milestones', true);
        
        if (!is_array($user_milestones) || empty($user_milestones)) {
            return array();
        }

        $reward_ids = array();

        foreach ($user_milestones as $milestone_id) {
            $milestone_rewards = get_post_meta($milestone_id, '_em_rewards', true);
            
            if (is_array($milestone_rewards)) {
                $reward_ids = array_merge($reward_ids, $milestone_rewards);
            }
        }

        if (empty($reward_ids)) {
            return array();
        }

        return get_posts(array(
            'post_type' => 'em_reward',
            'posts_per_page' => -1,
            'post__in' => array_unique($reward_ids),
            'orderby' => 'title',
            'order' => 'ASC',
        ));
    }
}