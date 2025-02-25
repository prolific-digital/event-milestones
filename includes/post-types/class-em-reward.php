<?php
/**
 * The Reward post type.
 *
 * @since      1.0.0
 * @package    Events_Milestones
 */
class EM_Reward {

    /**
     * Register the Reward post type.
     *
     * @since    1.0.0
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x('Rewards', 'Post Type General Name', 'events-milestones'),
            'singular_name'         => _x('Reward', 'Post Type Singular Name', 'events-milestones'),
            'menu_name'             => __('Rewards', 'events-milestones'),
            'name_admin_bar'        => __('Reward', 'events-milestones'),
            'archives'              => __('Reward Archives', 'events-milestones'),
            'attributes'            => __('Reward Attributes', 'events-milestones'),
            'parent_item_colon'     => __('Parent Reward:', 'events-milestones'),
            'all_items'             => __('All Rewards', 'events-milestones'),
            'add_new_item'          => __('Add New Reward', 'events-milestones'),
            'add_new'               => __('Add New', 'events-milestones'),
            'new_item'              => __('New Reward', 'events-milestones'),
            'edit_item'             => __('Edit Reward', 'events-milestones'),
            'update_item'           => __('Update Reward', 'events-milestones'),
            'view_item'             => __('View Reward', 'events-milestones'),
            'view_items'            => __('View Rewards', 'events-milestones'),
            'search_items'          => __('Search Reward', 'events-milestones'),
            'not_found'             => __('Not found', 'events-milestones'),
            'not_found_in_trash'    => __('Not found in Trash', 'events-milestones'),
            'featured_image'        => __('Featured Image', 'events-milestones'),
            'set_featured_image'    => __('Set featured image', 'events-milestones'),
            'remove_featured_image' => __('Remove featured image', 'events-milestones'),
            'use_featured_image'    => __('Use as featured image', 'events-milestones'),
            'insert_into_item'      => __('Insert into reward', 'events-milestones'),
            'uploaded_to_this_item' => __('Uploaded to this reward', 'events-milestones'),
            'items_list'            => __('Rewards list', 'events-milestones'),
            'items_list_navigation' => __('Rewards list navigation', 'events-milestones'),
            'filter_items_list'     => __('Filter rewards list', 'events-milestones'),
        );
        
        $args = array(
            'label'                 => __('Reward', 'events-milestones'),
            'description'           => __('Event rewards', 'events-milestones'),
            'labels'                => $labels,
            'supports'              => array('title', 'thumbnail'), // Removed 'editor' to disable WYSIWYG
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 26,
            'menu_icon'             => 'dashicons-awards',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'page',
        );
        
        register_post_type('em_reward', $args);
    }

    /**
     * Add meta boxes for the Reward post type.
     *
     * @since    1.0.0
     */
    public function add_meta_boxes() {
        add_meta_box(
            'em_reward_details',
            __('Reward Details', 'events-milestones'),
            array($this, 'render_details_meta_box'),
            'em_reward',
            'normal',
            'high'
        );
    }

    /**
     * Render the details meta box.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_details_meta_box($post) {
        // Add a nonce field
        wp_nonce_field('em_reward_details_meta_box', 'em_reward_details_meta_box_nonce');

        // Get existing values
        $reward_details = get_post_meta($post->ID, '_em_reward_details', true);
        $reward_description = get_post_meta($post->ID, '_em_reward_description', true);
        ?>
        <div class="reward-fields">
            <p>
                <label for="em_reward_description"><strong><?php _e('Reward Description:', 'events-milestones'); ?></strong></label>
                <textarea id="em_reward_description" name="em_reward_description" class="large-text" rows="5"><?php echo esc_textarea($reward_description); ?></textarea>
                <span class="description"><?php _e('The main description of this reward. This replaces the content editor.', 'events-milestones'); ?></span>
            </p>
            
            <p>
                <label for="em_reward_details"><strong><?php _e('Additional Details:', 'events-milestones'); ?></strong></label>
                <textarea id="em_reward_details" name="em_reward_details" class="large-text" rows="5"><?php echo esc_textarea($reward_details); ?></textarea>
                <span class="description"><?php _e('Additional information about this reward (redemption instructions, expiration, etc).', 'events-milestones'); ?></span>
            </p>
        </div>
        
        <style>
            .reward-fields textarea {
                margin-top: 5px;
                width: 100%;
            }
            .reward-fields .description {
                display: block;
                margin-top: 5px;
                font-style: italic;
                color: #666;
            }
        </style>
        <?php
    }

    /**
     * Save meta box data.
     *
     * @since    1.0.0
     * @param    int    $post_id    The post ID.
     */
    public function save_meta_boxes($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['em_reward_details_meta_box_nonce'])) {
            return;
        }

        // Verify that the nonce is valid
        if (!wp_verify_nonce($_POST['em_reward_details_meta_box_nonce'], 'em_reward_details_meta_box')) {
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

        // Save reward details
        if (isset($_POST['em_reward_details'])) {
            update_post_meta($post_id, '_em_reward_details', wp_kses_post($_POST['em_reward_details']));
        }
        
        // Save reward description
        if (isset($_POST['em_reward_description'])) {
            update_post_meta($post_id, '_em_reward_description', wp_kses_post($_POST['em_reward_description']));
            
            // Also update the post content to keep the data accessible through the standard WP methods
            // This makes the description available via get_the_content() and in templates
            wp_update_post(array(
                'ID' => $post_id,
                'post_content' => wp_kses_post($_POST['em_reward_description'])
            ));
        }
    }

    /**
     * Get all available rewards.
     *
     * @since    1.0.0
     * @return   array    Array of reward posts.
     */
    public function get_rewards() {
        return get_posts(array(
            'post_type' => 'em_reward',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));
    }

    /**
     * Get the milestones that unlock a reward.
     *
     * @since    1.0.0
     * @param    int       $reward_id    The reward ID.
     * @return   array                  Array of milestone post objects.
     */
    public function get_reward_milestones($reward_id) {
        $milestone_posts = get_posts(array(
            'post_type' => 'em_milestone',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_em_rewards',
                    'value' => $reward_id,
                    'compare' => 'LIKE',
                ),
            ),
        ));

        return $milestone_posts;
    }
}