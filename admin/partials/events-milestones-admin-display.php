<?php
/**
 * Provide a admin area view for the plugin
 *
 * @since      1.0.0
 * @package    Events_Milestones
 */
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="em-admin-container">
        <div class="em-admin-section">
            <h2><?php _e('Overview', 'events-milestones'); ?></h2>
            <p><?php _e('Welcome to Events Milestones. This plugin allows you to create milestones and rewards that users can achieve by checking in to events.', 'events-milestones'); ?></p>
        </div>

        <div class="em-admin-section">
            <h2><?php _e('Getting Started', 'events-milestones'); ?></h2>
            <ol>
                <li><?php _e('Create <strong>Rewards</strong> that users can earn.', 'events-milestones'); ?></li>
                <li><?php _e('Create <strong>Milestones</strong> with criteria and attach rewards to them.', 'events-milestones'); ?></li>
                <li><?php _e('Configure the plugin <strong>Settings</strong> for multisite integration and check-in parameters.', 'events-milestones'); ?></li>
                <li><?php _e('Use the provided shortcodes on your pages to show milestones, rewards, and check-in buttons.', 'events-milestones'); ?></li>
            </ol>
        </div>

        <div class="em-admin-section">
            <h2><?php _e('Shortcodes', 'events-milestones'); ?></h2>
            <table class="widefat fixed">
                <thead>
                    <tr>
                        <th><?php _e('Shortcode', 'events-milestones'); ?></th>
                        <th><?php _e('Description', 'events-milestones'); ?></th>
                        <th><?php _e('Attributes', 'events-milestones'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>[em_milestones]</code></td>
                        <td><?php _e('Displays available milestones.', 'events-milestones'); ?></td>
                        <td>
                            <ul>
                                <li><code>user_id</code> - <?php _e('User ID to display milestones for. Default: current user.', 'events-milestones'); ?></li>
                                <li><code>show_all</code> - <?php _e('Show all milestones or only those achieved. Values: true/false. Default: true.', 'events-milestones'); ?></li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td><code>[em_rewards]</code></td>
                        <td><?php _e('Displays available rewards.', 'events-milestones'); ?></td>
                        <td>
                            <ul>
                                <li><code>user_id</code> - <?php _e('User ID to display rewards for. Default: current user.', 'events-milestones'); ?></li>
                                <li><code>show_all</code> - <?php _e('Show all rewards or only those unlocked. Values: true/false. Default: true.', 'events-milestones'); ?></li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td><code>[em_checkin_button]</code></td>
                        <td><?php _e('Displays the check-in button.', 'events-milestones'); ?></td>
                        <td>
                            <ul>
                                <li><code>text</code> - <?php _e('Button text. Default: "Check In".', 'events-milestones'); ?></li>
                                <li><code>class</code> - <?php _e('CSS class for the button. Default: "em-checkin-button".', 'events-milestones'); ?></li>
                            </ul>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="em-admin-section">
            <h2><?php _e('Quick Links', 'events-milestones'); ?></h2>
            <div class="em-admin-quick-links">
                <a href="<?php echo admin_url('edit.php?post_type=em_milestone'); ?>" class="button button-primary"><?php _e('Manage Milestones', 'events-milestones'); ?></a>
                <a href="<?php echo admin_url('edit.php?post_type=em_reward'); ?>" class="button button-primary"><?php _e('Manage Rewards', 'events-milestones'); ?></a>
                <a href="<?php echo admin_url('admin.php?page=events-milestones-settings'); ?>" class="button button-primary"><?php _e('Settings', 'events-milestones'); ?></a>
                <a href="<?php echo admin_url('edit.php?post_type=em_user_milestone'); ?>" class="button"><?php _e('View User Achievements', 'events-milestones'); ?></a>
            </div>
        </div>
    </div>
</div>