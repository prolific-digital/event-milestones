/**
 * Public JavaScript for the Events Milestones plugin.
 *
 * @since      1.0.0
 * @package    Events_Milestones
 */
(function($) {
    'use strict';

    // Store user's geolocation
    var userLocation = {
        latitude: null,
        longitude: null
    };

    /**
     * Common button styles for consistency
     */
    var buttonStyles = {
        primary: 'display: inline-block; padding: 8px 16px; background-color: #0073aa; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: 500; margin-top: 10px;',
        secondary: 'display: inline-block; padding: 8px 16px; background-color: #f7f7f7; color: #555; border: 1px solid #ccc; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: 500; margin-top: 10px;'
    };
    
    $(document).ready(function() {
        // Initialize event handlers
        
        // Modal handling
        var modal = $('#em-checkin-modal');
        var btn = $('#em-checkin-button');
        var close = $('.em-modal-close');
        
        // Open modal
        btn.on('click', function() {
            modal.show();
            getUserLocation();
        });
        
        // Close modal
        close.on('click', function() {
            modal.hide();
            resetModal();
        });
        
        // Close modal when clicking outside
        $(window).on('click', function(event) {
            if ($(event.target).is(modal)) {
                modal.hide();
                resetModal();
            }
        });
        
        // Handle check-in button clicks on events
        $(document).on('click', '.em-event-checkin', function() {
            var eventId = $(this).data('event-id');
            checkinToEvent(eventId);
        });
        
        // Handle retry button click
        $(document).on('click', '#em-retry-button', function() {
            getUserLocation();
        });
        
        // Style the retry button whenever it's shown
        $(document).on('DOMNodeInserted', '#em-retry-geolocation', function() {
            var $retryButton = $('#em-retry-button');
            if ($retryButton.length && !$retryButton.attr('style')) {
                $retryButton.attr('style', buttonStyles.primary);
            }
        });
        
        // Toggle manual location form
        $(document).on('click', '#em-manual-location-toggle', function(e) {
            e.preventDefault();
            $('#em-geolocation-error').hide();
            $('#em-retry-geolocation').hide();
            $('#em-manual-location').show();
            
            // Focus on the first input field for better UX
            setTimeout(function() {
                $('#em-manual-latitude').focus();
            }, 100);
        });
        
        // Handle manual location submission
        $(document).on('click', '#em-submit-manual-location', function() {
            var latitudeStr = $('#em-manual-latitude').val().trim().replace(',', '.');
            var longitudeStr = $('#em-manual-longitude').val().trim().replace(',', '.');
            
            // Validate input
            if (!latitudeStr || !longitudeStr) {
                alert('Please enter both latitude and longitude values.');
                return;
            }
            
            var latitude = parseFloat(latitudeStr);
            var longitude = parseFloat(longitudeStr);
            
            // Validate as numbers
            if (isNaN(latitude) || isNaN(longitude)) {
                alert('Please enter valid numeric coordinates.');
                return;
            }
            
            // Validate range
            if (latitude < -90 || latitude > 90 || longitude < -180 || longitude > 180) {
                alert('Please enter valid coordinates. Latitude must be between -90 and 90, and longitude between -180 and 180.');
                return;
            }
            
            // Store the location
            userLocation.latitude = latitude;
            userLocation.longitude = longitude;
            
            console.log("Manual coordinates entered:", latitude, longitude);
            
            // Hide the form and get nearby events
            $('#em-manual-location').hide();
            $('#em-geolocation-error').hide();
            $('#em-retry-geolocation').hide();
            $('#em-loading').show();
            
            // Process the nearby events using the main function to avoid code duplication
            getNearbyEvents();
        });
        
        // Autocomplete coordinates from user's device
        $(document).on('click', '#em-get-current-position', function(e) {
            e.preventDefault();
            
            // Show loading state on the button
            var $button = $(this);
            var originalText = $button.text();
            $button.text('Getting location...');
            $button.prop('disabled', true);
            
            // Check if geolocation is available
            if (!("geolocation" in navigator)) {
                alert('Geolocation is not supported by your browser. Please enter coordinates manually.');
                $button.text(originalText);
                $button.prop('disabled', false);
                return;
            }
            
            // Use cached location if available
            if (userLocation.latitude && userLocation.longitude) {
                $('#em-manual-latitude').val(userLocation.latitude);
                $('#em-manual-longitude').val(userLocation.longitude);
                $button.text(originalText);
                $button.prop('disabled', false);
                return;
            }
            
            // Define success callback
            function successCallback(position) {
                // Update form fields
                $('#em-manual-latitude').val(position.coords.latitude);
                $('#em-manual-longitude').val(position.coords.longitude);
                
                // Store for future use
                userLocation.latitude = position.coords.latitude;
                userLocation.longitude = position.coords.longitude;
                
                // Restore button
                $button.text(originalText);
                $button.prop('disabled', false);
            }
            
            // Define error callback
            function errorCallback(error) {
                console.error("Error getting coordinates for form:", error);
                
                // For localhost/testing, use default coordinates instead of showing error
                if (window.location.hostname === 'localhost' || 
                    window.location.hostname === '127.0.0.1' ||
                    window.location.hostname.includes('test')) {
                    
                    console.log("Using default coordinates for testing");
                    $('#em-manual-latitude').val(40.7128);
                    $('#em-manual-longitude').val(-74.0060);
                    $button.text(originalText);
                    $button.prop('disabled', false);
                    return;
                }
                
                let errorMessage = 'Could not get your location. ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage += 'Location permission was denied.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage += 'Location information is unavailable.';
                        break;
                    case error.TIMEOUT:
                        errorMessage += 'Request timed out.';
                        break;
                    default:
                        errorMessage += 'An unknown error occurred.';
                }
                errorMessage += ' Please enter coordinates manually.';
                
                alert(errorMessage);
                
                // Restore button
                $button.text(originalText);
                $button.prop('disabled', false);
            }
            
            // Detect if this is an Apple device
            const isAppleDevice = /Mac|iPhone|iPad|iPod/i.test(navigator.userAgent);
            
            // Special handling for macOS/iOS devices
            if (isAppleDevice) {
                // For Apple devices, try with low accuracy first
                navigator.geolocation.getCurrentPosition(
                    successCallback,
                    function(firstError) {
                        console.warn("First geolocation attempt failed:", firstError);
                        
                        // Second attempt with different options
                        navigator.geolocation.getCurrentPosition(
                            successCallback,
                            errorCallback,
                            {
                                enableHighAccuracy: true,
                                timeout: 15000,
                                maximumAge: 0
                            }
                        );
                    },
                    {
                        enableHighAccuracy: false,
                        timeout: 10000,
                        maximumAge: 300000
                    }
                );
            } else {
                // Standard approach for non-Apple devices
                const options = {
                    enableHighAccuracy: true,   // Request high accuracy
                    timeout: 10000,             // 10 second timeout
                    maximumAge: 60000           // Accept cached position up to 1 minute old
                };
                
                // Get the current position
                navigator.geolocation.getCurrentPosition(
                    successCallback, 
                    errorCallback,
                    options
                );
            }
            
            // Set a backup timeout to restore the button if nothing happens
            setTimeout(function() {
                if ($button.prop('disabled')) {
                    $button.text(originalText);
                    $button.prop('disabled', false);
                    alert("Location detection timed out. Please enter coordinates manually.");
                }
            }, 15000);
        });
    });

    /**
     * Get the user's current location and load today's nearby events.
     * Using the native Geolocation API with special handling for macOS/iOS.
     */
    function getUserLocation() {
        // Show loading
        $('#em-loading').show();
        $('#em-geolocation-error').hide();
        $('#em-nearby-events').empty();
        $('#em-retry-geolocation').hide();
        
        // Check if geolocation is available in navigator
        if (!("geolocation" in navigator)) {
            // Browser doesn't support geolocation
            $('#em-loading').hide();
            $('#em-geolocation-error').html(em_ajax.i18n.geolocation_error + '<br>' + em_ajax.i18n.browser_no_geolocation).show();
            $('#em-retry-geolocation').show();
            
            // Automatically show manual entry form
            setTimeout(function() {
                $('#em-manual-location-toggle').trigger('click');
            }, 500);
            return;
        }
        
        // If we've already successfully retrieved the user's location in this session, reuse it
        if (userLocation.latitude && userLocation.longitude) {
            console.log("Using cached location:", userLocation);
            getNearbyEvents();
            return;
        }
        
        // Detect if this is an Apple device (macOS or iOS)
        const isAppleDevice = /Mac|iPhone|iPad|iPod/i.test(navigator.userAgent);
        console.log("Device info: " + navigator.userAgent + ", Is Apple device: " + isAppleDevice);
        
        // Success callback function for geolocation
        function positionSuccess(position) {
            console.log("Geolocation successful:", position);
            
            // Store position
            userLocation.latitude = position.coords.latitude;
            userLocation.longitude = position.coords.longitude;
            
            // Hide loading if it's still showing
            $('#em-loading').hide();
            
            // Get nearby events
            getNearbyEvents();
        }
        
        // Error callback function for geolocation
        function positionError(error) {
            console.error("Geolocation error:", error);
            $('#em-loading').hide();
            
            let errorMessage = '';
            
            // Check error code to provide appropriate message
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage = em_ajax.i18n.geolocation_denied;
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage = em_ajax.i18n.geolocation_unavailable;
                    break;
                case error.TIMEOUT:
                    errorMessage = em_ajax.i18n.geolocation_timeout;
                    break;
                default:
                    errorMessage = em_ajax.i18n.geolocation_unknown;
            }
            
            // Show detailed error message
            $('#em-geolocation-error').html('<div style="background-color: #fff4f4; padding: 15px; border-left: 4px solid #dc3232; border-radius: 3px; margin-bottom: 15px;">' +
                '<h3 style="margin-top: 0; color: #dc3232; font-size: 16px;">' + em_ajax.i18n.geolocation_error + '</h3>' +
                '<p style="margin-bottom: 10px;">' + errorMessage + ' (Error code: ' + error.code + ')</p>' +
                '<p style="margin-bottom: 10px;">You must enable location services to check in to events:</p>' +
                '<ul style="margin-left: 20px; margin-bottom: 0;">' +
                '<li>Ensure location services are enabled in your device settings and browser</li>' +
                '<li>Allow this site to access your location when prompted</li>' +
                '<li><strong>Try switching to a different network or mobile data</strong></li>' +
                '<li>Try refreshing the page</li>' +
                '</ul>' +
                '</div>').show();
            
            // Show styled retry button
            $('#em-retry-geolocation').html(
                '<div style="text-align: center; margin-top: 15px; margin-bottom: 15px;">' +
                '<button id="em-retry-button" style="' + buttonStyles.primary + '">Try Again</button>' +
                '</div>'
            ).show();
            
            // Log detailed error information for troubleshooting
            console.error("Detailed geolocation error:", {
                code: error.code,
                message: error.message,
                timestamp: new Date().toISOString(),
                userAgent: navigator.userAgent,
                isAppleDevice: /Mac|iPhone|iPad|iPod/i.test(navigator.userAgent)
            });
        }
        
        // On macOS/iOS Chrome, special handling needed for kCLErrorLocationUnknown
        if (isAppleDevice) {
            console.log("Using Apple device geolocation method");
            
            // For Apple devices, try various approaches
            var positionOptions = {
                enableHighAccuracy: true,   // Request high accuracy
                timeout: 15000,            // Longer timeout to wait for permission
                maximumAge: 0              // Don't use cached positions
            };
            
            // Try getCurrentPosition first
            navigator.geolocation.getCurrentPosition(
                positionSuccess,
                function(firstError) {
                    console.warn("First geolocation attempt failed:", firstError);
                    $('#em-loading').hide();
                    
                    // Display detailed error for troubleshooting
                    $('#em-geolocation-error').html('<div style="background-color: #fff4f4; padding: 15px; border-left: 4px solid #dc3232; border-radius: 3px; margin-bottom: 15px;">' +
                        '<h3 style="margin-top: 0; color: #dc3232; font-size: 16px;">Geolocation error: ' + firstError.message + ' (Error code: ' + firstError.code + ')</h3>' +
                        '<p style="margin-bottom: 10px;">You must enable location services to check in to events. Please check your browser and device settings.</p>' +
                        '<p style="margin-bottom: 0;"><strong>Network issues can affect geolocation:</strong> If you\'re on WiFi, try switching to mobile data or a different network.</p>' +
                        '</div>').show();
                    // Show styled retry button
                    $('#em-retry-geolocation').html(
                        '<div style="text-align: center; margin-top: 15px; margin-bottom: 15px;">' +
                        '<button id="em-retry-button" style="' + buttonStyles.primary + '">Try Again</button>' +
                        '</div>'
                    ).show();
                    
                    // Try watchPosition as a second attempt (works differently on some browsers)
                    var watchId = navigator.geolocation.watchPosition(
                        function(position) {
                            // Success with watchPosition
                            navigator.geolocation.clearWatch(watchId);
                            console.log("Got position from watchPosition");
                            
                            // Hide error and use the position
                            $('#em-geolocation-error').hide();
                            $('#em-retry-geolocation').hide();
                            positionSuccess(position);
                        },
                        function(secondError) {
                            // Watch also failed
                            navigator.geolocation.clearWatch(watchId);
                            console.warn("watchPosition also failed:", secondError);
                            
                            // Update error message with additional details
                            $('#em-geolocation-error').html('<div style="background-color: #fff4f4; padding: 15px; border-left: 4px solid #dc3232; border-radius: 3px; margin-bottom: 15px;">' +
                                '<h3 style="margin-top: 0; color: #dc3232; font-size: 16px;">Geolocation failed with both methods</h3>' +
                                '<p style="margin-bottom: 10px;">Error: ' + secondError.message + ' (Error code: ' + secondError.code + ')</p>' +
                                '<p style="margin-bottom: 10px;">Enable location services in your device settings and browser:</p>' +
                                '<ol style="margin-left: 20px; margin-bottom: 0;">' +
                                '<li>Check browser settings (Chrome/Safari)</li>' +
                                '<li>Check system settings (Settings > Privacy > Location Services)</li>' +
                                '<li><strong>Try switching to a different network or mobile data</strong></li>' +
                                '<li>Try a different browser if issues persist</li>' +
                                '</ol>' +
                                '</div>').show();
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        }
                    );
                    
                    // Clear watch after 12 seconds regardless
                    setTimeout(function() {
                        navigator.geolocation.clearWatch(watchId);
                    }, 12000);
                },
                positionOptions
            );
        } else {
            // Standard approach for non-Apple devices
            var positionOptions = {
                enableHighAccuracy: true,   // Get high accuracy if available
                timeout: 15000,             // Time to wait for a position
                maximumAge: 60000           // Accept a position up to 1 minute old
            };
            
            // Call the native Geolocation API
            navigator.geolocation.getCurrentPosition(
                positionSuccess,  // Success callback
                positionError,    // Error callback
                positionOptions   // Options
            );
        }
        
        // Set a backup timeout to ensure we don't leave the user waiting too long
        setTimeout(function() {
            if ($('#em-loading').is(':visible')) {
                $('#em-loading').hide();
                console.warn("Geolocation timed out after backup timeout");
                $('#em-geolocation-error').html("Location detection is taking longer than expected. Please try again or enter your coordinates manually.").show();
                $('#em-retry-geolocation').show();
                
                // No demo events for production use
                
                // Show manual entry form
                setTimeout(function() {
                    $('#em-manual-location-toggle').trigger('click');
                }, 500);
            }
        }, 20000);
    }
    
    // Removed demoEvents function - no longer using demo events
    
    // Removing getSourceSiteEvents function as we no longer need it

    /**
     * Get nearby events based on user's location.
     */
    function getNearbyEvents() {
        console.log("Getting nearby events for location:", userLocation);
        
        // Make sure we have valid coordinates
        if (!userLocation.latitude || !userLocation.longitude) {
            $('#em-loading').hide();
            $('#em-geolocation-error').html(em_ajax.i18n.geolocation_error).show();
            $('#em-retry-geolocation').show();
            return;
        }
        
        // Format coordinates to ensure they're valid numbers
        var lat = parseFloat(userLocation.latitude);
        var lng = parseFloat(userLocation.longitude);
        
        // Check for invalid coordinates
        if (isNaN(lat) || isNaN(lng) || 
            lat < -90 || lat > 90 || lng < -180 || lng > 180) {
            console.error("Invalid coordinates:", lat, lng);
            $('#em-loading').hide();
            $('#em-geolocation-error').html("Invalid coordinates detected. Please try again.").show();
            $('#em-retry-geolocation').show();
            return;
        }
        
        // Use the radius from the configuration data
        var radius = em_ajax.radius ? parseInt(em_ajax.radius) : 10;
        
        console.log("Sending AJAX request with:", {
            action: 'em_get_nearby_events',
            latitude: lat,
            longitude: lng,
            radius: radius
        });
        
        // Track the ajax request
        var ajaxRequest;
        
        // Use standard jQuery AJAX parameter format with additional safeguards
        ajaxRequest = $.ajax({
            type: 'POST',
            url: em_ajax.ajax_url,
            dataType: 'json',
            data: {
                action: 'em_get_nearby_events',
                nonce: em_ajax.nonce,
                latitude: lat.toString(),
                longitude: lng.toString(),
                radius: radius.toString()
            },
            timeout: 15000, // 15 second timeout
            success: function(response) {
                console.log("AJAX response:", response);
                
                // Enhanced logging to debug multiple events at same venue
                if (response.success && response.data && response.data.events) {
                    console.log("All events from server:", response.data.events);
                    
                    // Group events by venue to detect duplicates
                    var eventsByVenue = {};
                    response.data.events.forEach(function(event) {
                        if (!eventsByVenue[event.venue]) {
                            eventsByVenue[event.venue] = [];
                        }
                        eventsByVenue[event.venue].push(event);
                    });
                    
                    // Log venues with multiple events
                    for (var venue in eventsByVenue) {
                        if (eventsByVenue[venue].length > 1) {
                            console.log("Multiple events at venue '" + venue + "':", eventsByVenue[venue]);
                        }
                    }
                }
                
                $('#em-loading').hide();
                
                if (response.success && response.data && response.data.events) {
                    if (response.data.events.length > 0) {
                        displayNearbyEvents(response.data.events);
                    } else {
                        // No events found
                        $('#em-nearby-events').html('<p class="em-no-events">No events found for today. Try again another day!</p>');
                    }
                } else {
                    // Handle error in response
                    const message = response.data && response.data.message 
                        ? response.data.message 
                        : "Error getting nearby events. Please try again.";
                    
                    $('#em-geolocation-error').html(message).show();
                    $('#em-retry-geolocation').show();
                    
                    // Log the error for troubleshooting
                    console.error("AJAX success but response error:", response);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX error:", status, error);
                
                // More detailed error logging
                if (xhr.responseText) {
                    try {
                        console.error("Response text:", xhr.responseText.substring(0, 500) + "...");
                    } catch (e) {
                        console.error("Could not log response text:", e);
                    }
                }
                
                $('#em-loading').hide();
                
                let errorMessage = "Error getting nearby events. ";
                
                // Provide specific message based on error type
                if (status === 'timeout') {
                    errorMessage += "Request timed out. ";
                } else if (status === 'parsererror') {
                    errorMessage += "Could not parse server response. ";
                } else if (status === 'abort') {
                    errorMessage += "Request was aborted. ";
                } else if (xhr.status) {
                    errorMessage += `Server returned error ${xhr.status}. `;
                }
                
                errorMessage += "Please try again or use manual coordinates.";
                $('#em-geolocation-error').html(errorMessage).show();
                
                // Show retry button
                $('#em-retry-geolocation').show();
                
                // Log detailed information for troubleshooting
                console.error("AJAX error details:", {
                    status: status,
                    error: error,
                    timestamp: new Date().toISOString(),
                    url: em_ajax.ajax_url,
                    readyState: xhr.readyState,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText ? xhr.responseText.substring(0, 500) : null
                });
            }
        });
        
        // Set a backup timeout just in case
        setTimeout(function() {
            if (ajaxRequest && ajaxRequest.readyState && ajaxRequest.readyState < 4) {
                ajaxRequest.abort();
                console.error("AJAX request manually aborted after timeout");
                $('#em-loading').hide();
                $('#em-geolocation-error').html("Request took too long. Please try again.").show();
                $('#em-retry-geolocation').show();
                
                // Log timeout error for troubleshooting
                console.error("AJAX request timed out after 20 seconds", {
                    timestamp: new Date().toISOString(),
                    url: em_ajax.ajax_url,
                    coordinates: {
                        latitude: userLocation.latitude,
                        longitude: userLocation.longitude
                    }
                });
            }
        }, 20000);
    }

    /**
     * Display nearby events in the modal.
     *
     * @param {Array} events The array of nearby events.
     */
    function displayNearbyEvents(events) {
        var container = $('#em-nearby-events');
        container.empty();
        
        if (events.length === 0) {
            container.html('<p class="em-no-events">' + em_ajax.i18n.no_events + '</p>');
            return;
        }
        
        // Format date
        function formatDate(dateString) {
            try {
                // Parse MySQL date format (YYYY-MM-DD HH:MM:SS)
                var parts = dateString.split(/[- :]/);
                var date = new Date(parts[0], parts[1]-1, parts[2], parts[3], parts[4], parts[5]);
                
                // Format date nicely
                const options = { 
                    hour: '2-digit',
                    minute: '2-digit'
                };
                return date.toLocaleTimeString(undefined, options);
            } catch (e) {
                console.error("Date parsing error:", e);
                return dateString;
            }
        }
        
        // Get today's date in YYYY-MM-DD format for filtering
        var today = new Date();
        var todayStr = today.getFullYear() + '-' + 
                       ('0' + (today.getMonth() + 1)).slice(-2) + '-' + 
                       ('0' + today.getDate()).slice(-2);
        
        console.log("Today's date for filtering:", todayStr);
        
        // Filter to only show today's events
        var todaysEvents = events.filter(function(event) {
            var eventDateParts = event.start_date.split(' ');
            var eventDateStr = eventDateParts[0]; // Get the YYYY-MM-DD part
            return eventDateStr === todayStr;
        });
        
        console.log("Events filtered for today:", todaysEvents.length, "out of", events.length);
        
        if (todaysEvents.length === 0) {
            container.html('<p class="em-no-events" style="padding: 20px; text-align: center; color: #555; background-color: #f9f9f9; border-radius: 5px; font-size: 16px;">No events happening today</p>');
            return;
        }
        
        // Helper function to create event HTML
        function createEventHtml(event) {
            var eventHtml = '<div class="em-event-item" style="margin-bottom: 20px; padding: 15px; border-radius: 5px; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">' +
                '<h3 class="em-event-title" style="margin-top: 0; margin-bottom: 10px; color: #333; font-size: 18px;">' + event.title + '</h3>' +
                '<div class="em-event-meta" style="margin-bottom: 15px; color: #555; font-size: 14px;">' +
                '<p style="margin: 5px 0;"><strong>' + 'Time: ' + '</strong>' + formatDate(event.start_date) + '</p>';
            
            // Add end time if different from start time
            var startTimeParts = event.start_date.split(' ')[1].split(':'); // HH:MM:SS
            var endTimeParts = event.end_date.split(' ')[1].split(':');
            
            if (startTimeParts[0] !== endTimeParts[0] || startTimeParts[1] !== endTimeParts[1]) {
                eventHtml += '<p style="margin: 5px 0;"><strong>' + 'Ends: ' + '</strong>' + formatDate(event.end_date) + '</p>';
            }
            
            eventHtml += '<p style="margin: 5px 0;"><strong>' + 'Venue: ' + '</strong>' + event.venue + '</p>' +
                '</div>';
            
            // Add check-in button if user is logged in
            if (em_ajax.is_user_logged_in) {
                eventHtml += '<button class="em-event-checkin button button-primary" data-event-id="' + event.id + '" style="display: inline-block; padding: 8px 16px; background-color: #0073aa; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: 500;">' + em_ajax.i18n.check_in + '</button>';
            }
            
            eventHtml += '</div>';
            
            return eventHtml;
        }
        
        // Sort events by start time
        todaysEvents.sort(function(a, b) {
            return new Date(a.start_date) - new Date(b.start_date);
        });
        
        // Display events
        $.each(todaysEvents, function(index, event) {
            container.append(createEventHtml(event));
        });
    }

    /**
     * Check in to an event.
     *
     * @param {number} eventId The event ID.
     */
    function checkinToEvent(eventId) {
        $.ajax({
            type: 'POST',
            url: em_ajax.ajax_url,
            data: {
                action: 'em_checkin',
                nonce: em_ajax.nonce,
                event_id: eventId
            },
            beforeSend: function() {
                // Disable check-in button
                $('.em-event-checkin[data-event-id="' + eventId + '"]').prop('disabled', true).text('Checking in...');
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    $('#em-nearby-events').hide();
                    var resultHtml = '<div class="em-success">' + response.data.message + '</div>';
                    
                    // Show unlocked milestones if any
                    if (response.data.milestones_achieved && response.data.milestones_achieved.length > 0) {
                        resultHtml += '<div class="em-achievements">' +
                            '<h3>' + em_ajax.i18n.achievement_unlocked + '</h3>' +
                            '<p>' + 'You have unlocked new milestones!' + '</p>' +
                            '</div>';
                    }
                    
                    resultHtml += '<button id="em-close-modal" class="em-checkin-button">' + em_ajax.i18n.close + '</button>';
                    
                    $('#em-checkin-result').html(resultHtml).show();
                    
                    // Close button
                    $('#em-close-modal').on('click', function() {
                        $('#em-checkin-modal').hide();
                        resetModal();
                        // Refresh the page to update milestones/rewards display
                        window.location.reload();
                    });
                } else {
                    // Show error message
                    var errorHtml = '<div class="em-error">' + response.data.message + '</div>';
                    $('.em-event-checkin[data-event-id="' + eventId + '"]').after(errorHtml).prop('disabled', false).text(em_ajax.i18n.check_in);
                }
            },
            error: function() {
                // Show error message
                var errorHtml = '<div class="em-error">' + em_ajax.i18n.checkin_error + '</div>';
                $('.em-event-checkin[data-event-id="' + eventId + '"]').after(errorHtml).prop('disabled', false).text(em_ajax.i18n.check_in);
            }
        });
    }

    // Removed preset locations functionality - requiring real geolocation
    
    /**
     * Reset the modal to its initial state.
     */
    function resetModal() {
        $('#em-loading').hide();
        $('#em-geolocation-error').hide();
        $('#em-nearby-events').empty().show();
        $('#em-checkin-result').empty().hide();
        $('#em-retry-geolocation').hide();
        $('#em-manual-location').hide();
        
        // Reset manual location form values if they exist
        $('#em-manual-latitude').val('');
        $('#em-manual-longitude').val('');
    }

})(jQuery);