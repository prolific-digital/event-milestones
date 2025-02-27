<?php
/**
 * Provide a admin area view for the plugin settings
 *
 * @since      1.0.0
 * @package    Events_Milestones
 */
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php
        // Output settings fields
        settings_fields('events_milestones_settings');
        do_settings_sections('events_milestones_settings');
        submit_button();
        ?>
    </form>
    
    <div class="events-milestones-admin-help">
        <h3><?php _e('Help & Information', 'events-milestones'); ?></h3>
        
        <div class="events-milestones-card">
            <h4><?php _e('Multisite Configuration', 'events-milestones'); ?></h4>
            <p>
                <?php _e('When using Events Milestones in a multisite environment, you can specify which site to pull events from using the Source Blog ID setting.', 'events-milestones'); ?>
            </p>
            <p>
                <?php _e('The events must be created with The Events Calendar plugin on the source blog.', 'events-milestones'); ?>
            </p>
        </div>
        
        <div class="events-milestones-card">
            <h4><?php _e('Shortcode Usage', 'events-milestones'); ?></h4>
            <p>
                <?php _e('Use the shortcode [em_milestones] on any page to display the user\'s earned milestones.', 'events-milestones'); ?>
            </p>
            <p>
                <?php _e('Use the shortcode [em_rewards] to show a user\'s earned rewards.', 'events-milestones'); ?>
            </p>
            <p>
                <?php _e('Use the shortcode [em_checkin_button] to display a check-in button for events.', 'events-milestones'); ?>
            </p>
            <p>
                <?php _e('Use the shortcode [em_activity_feed] to display a chronological feed of user\'s check-ins and milestone achievements.', 'events-milestones'); ?>
            </p>
        </div>
        
        <div class="events-milestones-card" style="background-color: #fef8ee; border-left: 4px solid #f0b849;">
            <h4><?php _e('Reset User Data', 'events-milestones'); ?></h4>
            <p>
                <?php _e('Use this tool to reset a user\'s check-ins and milestone achievements. This action cannot be undone.', 'events-milestones'); ?>
            </p>
            <form method="post" action="" class="em-reset-form">
                <?php wp_nonce_field('em_reset_user_data', 'em_reset_nonce'); ?>
                <div class="em-form-row">
                    <label for="em_user_id"><?php _e('User:', 'events-milestones'); ?></label>
                    <select name="em_user_id" id="em_user_id" required>
                        <option value=""><?php _e('Select a user', 'events-milestones'); ?></option>
                        <?php
                        $users = get_users(array('fields' => array('ID', 'display_name')));
                        foreach ($users as $user) {
                            echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . ' (ID: ' . $user->ID . ')</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="em-form-row reset-options">
                    <div class="reset-option">
                        <label><input type="checkbox" name="em_reset_checkins" value="1" required> <?php _e('Reset check-ins', 'events-milestones'); ?></label>
                    </div>
                    <div class="reset-option">
                        <label><input type="checkbox" name="em_reset_milestones" value="1" required> <?php _e('Reset milestones', 'events-milestones'); ?></label>
                    </div>
                </div>
                <div class="em-form-row button-row">
                    <input type="hidden" name="em_action" value="reset_user_data">
                    <button type="submit" class="button button-secondary" onclick="return confirm('<?php esc_attr_e('WARNING: This will permanently delete all check-ins and milestone achievements for the selected user. This action cannot be undone. Are you sure?', 'events-milestones'); ?>');"><?php _e('Reset User Data', 'events-milestones'); ?></button>
                </div>
            </form>
        </div>
    </div>
    
    <style>
    .em-form-row {
        margin-bottom: 15px;
    }
    .em-form-row label {
        display: inline-block;
        min-width: 100px;
    }
    .em-form-row select {
        min-width: 250px;
        padding: 5px;
    }
    .reset-options {
        margin: 20px 0;
    }
    .reset-option {
        margin-bottom: 10px;
    }
    .reset-option label {
        display: flex;
        align-items: center;
    }
    .reset-option input[type="checkbox"] {
        margin-right: 8px;
    }
    .button-row {
        margin-top: 25px;
    }
    </style>
</div>