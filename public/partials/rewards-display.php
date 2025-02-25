<?php
/**
 * Provide a public-facing view for the rewards shortcode
 *
 * @since      1.0.0
 * @package    Events_Milestones
 */
?>

<div class="events-milestones-wrapper">
  <div class="em-rewards-container">
    <?php if (empty($rewards)) : ?>
        <p class="em-no-rewards"><?php _e('No rewards available.', 'events-milestones'); ?></p>
    <?php else : ?>
        <div class="em-rewards-grid">
            <?php foreach ($rewards as $reward) : 
                // Skip if not showing all and this reward is not unlocked
                if (!$show_all && !in_array($reward->ID, $unlocked_reward_ids)) {
                    continue;
                }
                
                $is_unlocked = $atts['user_id'] > 0 && in_array($reward->ID, $unlocked_reward_ids);
                $reward_class = $is_unlocked ? 'em-reward-unlocked' : 'em-reward-locked';
                
                // Get reward details
                $reward_details = get_post_meta($reward->ID, '_em_reward_details', true);
                
                // Get milestones that unlock this reward
                $reward_obj = new EM_Reward();
                $milestones = $reward_obj->get_reward_milestones($reward->ID);
            ?>
                <div class="em-reward-card <?php echo esc_attr($reward_class); ?>">
                    <?php if (has_post_thumbnail($reward->ID)) : ?>
                        <div class="em-reward-thumbnail">
                            <?php echo get_the_post_thumbnail($reward->ID, 'medium'); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="em-reward-header">
                        <?php if ($is_unlocked) : ?>
                            <span class="em-reward-badge"><?php _e('Unlocked', 'events-milestones'); ?></span>
                        <?php else : ?>
                            <span class="em-reward-badge locked"><?php _e('Locked', 'events-milestones'); ?></span>
                        <?php endif; ?>
                        <h3 class="em-reward-title"><?php echo esc_html($reward->post_title); ?></h3>
                    </div>
                    
                    <div class="em-reward-content">
                        <div class="em-reward-description">
                            <?php echo wpautop($reward->post_content); ?>
                        </div>
                        
                        <?php if (!empty($reward_details)) : ?>
                            <div class="em-reward-details">
                                <?php echo wpautop($reward_details); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($milestones) && !$is_unlocked) : ?>
                            <div class="em-reward-milestones">
                                <h4><?php _e('How to Unlock:', 'events-milestones'); ?></h4>
                                <p><?php _e('Complete one of these milestones:', 'events-milestones'); ?></p>
                                <ul class="em-milestones-list">
                                    <?php foreach ($milestones as $milestone) : ?>
                                        <li><?php echo esc_html($milestone->post_title); ?></li>
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