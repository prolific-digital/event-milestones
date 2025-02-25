<?php
/**
 * Provide a public-facing view for the check-in button shortcode
 *
 * @since      1.0.0
 * @package    Events_Milestones
 */
?>

<div class="em-checkin-container">
    <button id="em-checkin-button" class="<?php echo esc_attr($atts['class']); ?>"><?php echo esc_html($atts['text']); ?></button>
    
    <!-- Modal for check-in -->
    <div id="em-checkin-modal" class="em-modal" style="display: none;">
        <div class="em-modal-content">
            <span class="em-modal-close">&times;</span>
            
            <div id="em-geolocation-error" class="em-error" style="display: none;"></div>
            
            <div id="em-loading" style="display: none;">
                <p><?php _e('Finding today\'s nearby events...', 'events-milestones'); ?></p>
                <div class="em-loading-spinner"></div>
                <p class="em-location-note"><?php _e('Please allow location access when prompted by your browser', 'events-milestones'); ?></p>
            </div>
            
            <h2><?php _e('Today\'s Nearby Events', 'events-milestones'); ?></h2>
            <div id="em-nearby-events" class="em-events-list"></div>
            
            <div id="em-checkin-result" style="display: none;"></div>
            
            <?php if (!is_user_logged_in()) : ?>
                <div class="em-login-required">
                    <p><?php _e('You must be logged in to check in to events.', 'events-milestones'); ?></p>
                    <a href="<?php echo wp_login_url(get_permalink()); ?>" class="button"><?php _e('Log In', 'events-milestones'); ?></a>
                </div>
            <?php endif; ?>
            
            <!-- Manual location entry form -->
            <div id="em-manual-location" style="display: none; margin-top: 20px; padding: 15px; background-color: #f9f9f9; border-radius: 5px;">
                <h3><?php _e('Enter Your Location', 'events-milestones'); ?></h3>
                <p class="description"><?php _e('Please enter your location to find nearby events:', 'events-milestones'); ?></p>
                
                <div class="em-manual-location-form">
                    <div class="em-form-row" style="margin-bottom: 10px; text-align: center;">
                        <button id="em-get-current-position" class="button"><?php _e('Use My Current Location', 'events-milestones'); ?></button>
                    </div>
                    
                    <p class="description" style="text-align: center; margin-bottom: 15px;"><?php _e('Or enter coordinates manually:', 'events-milestones'); ?></p>
                    
                    <div class="em-form-row" style="margin-bottom: 10px;">
                        <label for="em-manual-latitude"><?php _e('Latitude:', 'events-milestones'); ?></label>
                        <input type="number" id="em-manual-latitude" step="any" placeholder="e.g. 40.7128" style="width: 100%;">
                    </div>
                    
                    <div class="em-form-row" style="margin-bottom: 10px;">
                        <label for="em-manual-longitude"><?php _e('Longitude:', 'events-milestones'); ?></label>
                        <input type="number" id="em-manual-longitude" step="any" placeholder="e.g. -74.0060" style="width: 100%;">
                    </div>
                    
                    <div class="em-form-row" style="margin-bottom: 10px;">
                        <button id="em-submit-manual-location" class="button button-primary"><?php _e('Find Nearby Events', 'events-milestones'); ?></button>
                    </div>
                    
                    <p class="description" style="font-size: 12px;"><?php _e('Tips: You can find coordinates using Google Maps (right-click on a location and select "What\'s here?") or by searching online for your city\'s coordinates.', 'events-milestones'); ?></p>
                </div>
            </div>
            
            <div id="em-retry-geolocation" style="display: none; text-align: center; margin-top: 20px;">
                <button class="button" id="em-retry-button"><?php _e('Retry Automatic Location Detection', 'events-milestones'); ?></button>
                <p style="margin-top: 10px;">
                    <a href="#" id="em-manual-location-toggle"><?php _e('Or enter your location manually', 'events-milestones'); ?></a>
                </p>
            </div>
        </div>
    </div>
</div>