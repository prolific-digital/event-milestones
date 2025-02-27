=== Events Milestones ===
Contributors: prolificdigital
Donate link: https://prolificdigital.com
Tags: events, gamification, milestones, rewards
Requires at least: 5.9
Tested up to: 6.5
Stable tag: 1.1.1
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

An add-on for The Events Calendar that introduces a gamification system with milestones and rewards based on event check-ins.

== Description ==

Events Milestones is an extension for The Events Calendar that adds a gamification layer to your events. The plugin allows users to check in to events and earn milestones and rewards based on their participation.

### Features

* **User Check-ins**: Allow users to check in to events within a certain radius
* **Milestone System**: Create milestones that users can achieve by checking in to events
* **Rewards System**: Attach rewards to milestones to incentivize participation
* **Multisite Support**: Check in to events across a WordPress network
* **Shortcodes**: Display user milestones, rewards, check-in buttons, and activity feeds
* **Automatic Updates**: Plugin updates automatically from GitHub repository

### Shortcodes

* `[em_milestones]` - Displays user milestones
* `[em_rewards]` - Displays user rewards
* `[em_checkin_button]` - Displays a check-in button for events
* `[em_activity_feed]` - Displays a user's activity feed (check-ins and milestone achievements)

== Installation ==

1. Upload the `events-milestones` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Events Milestones in the admin menu to configure the plugin

== Changelog ==

= 1.1.1 =
* Added new activity feed shortcode `[em_activity_feed]` to display user's check-ins and achievements
* Added styling for the activity feed

= 1.1.0 =
* Fixed hardcoded 5-mile radius issue in geolocation functionality
* Fixed issue with demo events appearing when no real events were found
* Added automatic update capability via GitHub
* Added featured image support for milestones and rewards
* Fixed milestone event count values
* Fixed CSS conflicts with themes
* Improved geolocation handling with better error messages
* Various bug fixes and improvements

= 1.0.0 =
* Initial release

== Frequently Asked Questions ==

= Does this plugin work without The Events Calendar? =

While the plugin will activate without The Events Calendar, most of its functionality is dependent on it. You'll see a notice recommending you to install The Events Calendar.

= How does the check-in system work? =

Users click a check-in button which uses their browser's geolocation API to determine if they are within a configurable radius of the event's venue. If they are, the check-in is recorded.

= Can I customize the radius for check-ins? =

Yes, the radius for check-ins can be configured in the plugin settings.

== Screenshots ==

1. Milestones display
2. Rewards display 
3. Check-in button and nearby events
4. Admin milestones management
5. Activity feed showing user check-ins and achievements

== Upgrade Notice ==

= 1.1.1 =
Added new activity feed feature to show users' check-ins and milestone achievements.