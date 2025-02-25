/**
 * Admin JavaScript for the Events Milestones plugin.
 *
 * @since      1.0.0
 * @package    Events_Milestones
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Handle milestone criteria type selection
        $('#em_criteria_type').on('change', function() {
            var criteriaType = $(this).val();
            $('.criteria-section').hide();
            $('#criteria_' + criteriaType).show();
        });
        
        // Pre-submit validation - Ensure event count is valid
        $('form#post').on('submit', function(e) {
            var criteriaType = $('#em_criteria_type').val();
            var eventCount;
            
            if (criteriaType === 'event_count') {
                eventCount = $('#em_event_count').val();
                if (!eventCount || parseInt(eventCount) < 1) {
                    alert('Please enter a valid number of events (must be at least 1)');
                    $('#em_event_count').focus();
                    e.preventDefault();
                    return false;
                }
            } else if (criteriaType === 'timeframe') {
                eventCount = $('#em_event_count_timeframe').val();
                if (!eventCount || parseInt(eventCount) < 1) {
                    alert('Please enter a valid number of events for the timeframe (must be at least 1)');
                    $('#em_event_count_timeframe').focus();
                    e.preventDefault();
                    return false;
                }
                
                var timeframeDays = $('#em_timeframe_days').val();
                if (!timeframeDays || parseInt(timeframeDays) < 1) {
                    alert('Please enter a valid timeframe in days (must be at least 1)');
                    $('#em_timeframe_days').focus();
                    e.preventDefault();
                    return false;
                }
            }
            
            return true;
        });
        
        // Ensure values aren't lost when switching between criteria types
        var eventCountValue = $('#em_event_count').val();
        var timeframeEventCountValue = $('#em_event_count_timeframe').val();
        
        // Sync the event count values between fields
        $('#em_event_count, #em_event_count_timeframe').on('change', function() {
            var value = $(this).val();
            if ($(this).attr('id') === 'em_event_count') {
                $('#em_event_count_timeframe').val(value);
            } else {
                $('#em_event_count').val(value);
            }
        });
    });

})(jQuery);