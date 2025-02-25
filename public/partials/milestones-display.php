<?php
/**
 * Provide a public-facing view for the milestones shortcode
 *
 * @since      1.0.0
 * @package    Events_Milestones
 */
?>

<div class="events-milestones-wrapper">
  <div class="em-milestones-container">
    <?php if (empty($milestones)) : ?>
        <p class="em-no-milestones"><?php _e('No milestones available.', 'events-milestones'); ?></p>
    <?php else : ?>
        <div class="em-milestones-grid">
            <?php foreach ($milestones as $milestone) : 
                // Skip if not showing all and this milestone is not achieved
                if (!$show_all && !in_array($milestone->ID, $achieved_milestone_ids)) {
                    continue;
                }
                
                $is_achieved = $atts['user_id'] > 0 && in_array($milestone->ID, $achieved_milestone_ids);
                $milestone_class = $is_achieved ? 'em-milestone-achieved' : 'em-milestone-locked';
                
                // Get milestone details
                $criteria_type = get_post_meta($milestone->ID, '_em_criteria_type', true);
                $event_count = get_post_meta($milestone->ID, '_em_event_count', true);
                $timeframe_days = get_post_meta($milestone->ID, '_em_timeframe_days', true);
                $specific_events = get_post_meta($milestone->ID, '_em_specific_events', true);
                
                // Get rewards for this milestone
                $rewards = $milestone_obj->get_milestone_rewards($milestone->ID);
            ?>
                <div class="em-milestone-card <?php echo esc_attr($milestone_class); ?>">
                    <?php if (has_post_thumbnail($milestone->ID)) : ?>
                        <div class="em-milestone-thumbnail">
                            <?php echo get_the_post_thumbnail($milestone->ID, 'medium'); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="em-milestone-header">
                        <?php if ($is_achieved) : ?>
                            <span class="em-milestone-badge"><?php _e('Achieved', 'events-milestones'); ?></span>
                        <?php endif; ?>
                        <h3 class="em-milestone-title"><?php echo esc_html($milestone->post_title); ?></h3>
                    </div>
                    
                    <div class="em-milestone-content">
                        <div class="em-milestone-description">
                            <?php echo wpautop($milestone->post_content); ?>
                        </div>
                        
                        <div class="em-milestone-criteria">
                            <h4><?php _e('Requirements:', 'events-milestones'); ?></h4>
                            <?php if ($criteria_type == 'event_count') : ?>
                                <p><?php printf(__('Check in to %d events.', 'events-milestones'), $event_count); ?></p>
                            <?php elseif ($criteria_type == 'timeframe') : ?>
                                <p><?php printf(__('Check in to %d events within %d days.', 'events-milestones'), $event_count, $timeframe_days); ?></p>
                            <?php elseif ($criteria_type == 'specific_events') : ?>
                                <p><?php _e('Check in to all of these events:', 'events-milestones'); ?></p>
                                <ul class="em-specific-events-list">
                                    <?php 
                                    $multisite = new EM_Multisite();
                                    if (is_array($specific_events)) {
                                        foreach ($specific_events as $event_id) {
                                            $event = $multisite->get_event($event_id);
                                            if ($event) {
                                                echo '<li>' . esc_html($event->post_title) . '</li>';
                                            }
                                        }
                                    }
                                    ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($rewards)) : ?>
                            <div class="em-milestone-rewards">
                                <h4><?php _e('Rewards:', 'events-milestones'); ?></h4>
                                <ul class="em-rewards-list">
                                    <?php foreach ($rewards as $reward) : ?>
                                        <li><?php echo esc_html($reward->post_title); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
  </div>
</div>