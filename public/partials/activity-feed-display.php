<?php
/**
 * Template for displaying the user activity feed.
 *
 * @since      1.1.0
 * @package    Events_Milestones
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get the user object
$user = get_user_by('id', $atts['user_id']);
?>

<div class="em-activity-feed">
    <h2 class="em-activity-feed-title">
        <?php 
        if (get_current_user_id() == $atts['user_id']) {
            _e('Your Activity', 'events-milestones');
        } else {
            echo sprintf(__('%s\'s Activity', 'events-milestones'), $user->display_name);
        }
        ?>
    </h2>
    
    <?php if (empty($activity_feed)) : ?>
        <p class="em-activity-empty"><?php _e('No activity recorded yet.', 'events-milestones'); ?></p>
    <?php else : ?>
        <ul class="em-activity-list">
            <?php foreach ($activity_feed as $activity) : ?>
                <li class="em-activity-item em-activity-<?php echo $activity['type']; ?>">
                    <div class="em-activity-meta">
                        <span class="em-activity-date"><?php echo $activity['date']; ?></span>
                    </div>
                    
                    <div class="em-activity-content">
                        <?php if ($activity['type'] == 'checkin') : ?>
                            <div class="em-activity-icon">
                                <span class="dashicons dashicons-location"></span>
                            </div>
                            <div class="em-activity-details">
                                <span class="em-activity-action"><?php _e('Checked in to', 'events-milestones'); ?></span>
                                <span class="em-activity-title"><?php echo esc_html($activity['title']); ?></span>
                            </div>
                        <?php elseif ($activity['type'] == 'milestone') : ?>
                            <div class="em-activity-icon">
                                <span class="dashicons dashicons-awards"></span>
                            </div>
                            <div class="em-activity-details">
                                <span class="em-activity-action"><?php _e('Achieved milestone', 'events-milestones'); ?></span>
                                <span class="em-activity-title"><?php echo esc_html($activity['title']); ?></span>
                                <?php if (!empty($activity['description'])) : ?>
                                    <div class="em-activity-description"><?php echo esc_html($activity['description']); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>