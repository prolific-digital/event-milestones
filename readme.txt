=== Events Milestones ===
Contributors: prolificdigital
Tags: events, milestones, rewards, gamification, check-in
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.0
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

An add-on for The Events Calendar that introduces a system for managing event-related milestones and rewards.

== Description ==

Events Milestones is a WordPress plugin that extends The Events Calendar to provide a milestone and reward system for users who check in to events. It allows event organizers to create achievements that users can unlock by attending events, encouraging participation and engagement.

= Features =

* **Milestone Management**: Create and manage milestones with different achievement criteria (number of events, specific events, events within a timeframe).
* **Reward System**: Attach rewards to milestones, which users can unlock upon achievement.
* **Geolocation Check-in**: Users can check in to events using their device's geolocation (within a 5-mile radius).
* **Multisite Support**: Pull events from any site in a WordPress multisite network.
* **Shortcodes**: Easily display milestones, rewards, and check-in buttons anywhere on your site.
* **Featured Images**: Support for featured images on both milestones and rewards for visual engagement.
* **Automatic Updates**: Plugin includes the ability to receive updates directly from GitHub.

= Requirements =

* WordPress 5.0 or higher
* The Events Calendar 5.0 or higher (Pro version is optional but supported)
* PHP 7.0 or higher

== Installation ==

1. Upload the `events-milestones` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure The Events Calendar is installed and activated
4. Go to Events Milestones in the admin menu to configure the plugin

== Usage ==

= Creating Milestones =

1. Go to Events Milestones > Add New Milestone
2. Enter a title and description for the milestone
3. Add a featured image that represents the milestone (optional)
4. Set the criteria for achieving the milestone:
   * Number of events: User must check in to a specific number of events
   * Events in timeframe: User must check in to a specific number of events within a certain number of days
   * Specific events: User must check in to all of the selected events
5. Select the rewards that will be unlocked when the milestone is achieved
6. Click Publish

= Creating Rewards =

1. Go to Events Milestones > Add New Reward
2. Enter a title and description for the reward
3. Add a featured image that represents the reward (optional)
4. Optionally add additional details in the Reward Details section
5. Click Publish

= Displaying Content =

Use these shortcodes to display content on your site:

* `[em_milestones]` - Displays available milestones
  * Attributes: `user_id`, `show_all`
* `[em_rewards]` - Displays available rewards
  * Attributes: `user_id`, `show_all`
* `[em_checkin_button]` - Displays the check-in button
  * Attributes: `text`, `class`

= Multisite Configuration =

If you're using WordPress multisite, you can configure which site to pull events from:

1. Go to Events Milestones > Settings
2. Select the source site from the dropdown
3. Click Save Changes

== Frequently Asked Questions ==

= Do users need to be logged in to check in to events? =

Yes, users must be logged in to check in to events and track their progress towards milestones.

= How does the check-in system work? =

When a user clicks the check-in button, the plugin will use the browser's geolocation API to get the user's coordinates. It will then find events within a 5-mile radius and let the user check in to an event if they are near the venue location. If location detection fails, users may need to switch networks or try again.

= Can I customize the appearance of the milestones and rewards? =

Yes, you can use custom CSS to style the milestone and reward elements. The plugin provides CSS classes for each element and is designed to work well with most WordPress themes.

= How do I reset a user's progress? =

Administrators can reset a user's check-ins and milestone achievements through the Events Milestones > Settings page using the Reset User Data tool.

== Screenshots ==

1. Milestone display with featured images
2. Rewards display showing unlocked and locked rewards
3. Admin interface for creating milestones
4. Admin interface for creating rewards
5. Check-in modal for users

== Changelog ==

= 1.1.0 =
* Added featured image support for milestones and rewards
* Fixed milestone event count values to correctly save values above 1
* Improved geolocation error handling and messaging
* Set geolocation radius to 5 miles
* Updated plugin author information
* Fixed form validation issues in milestone admin
* Fixed CSS layout conflicts with themes
* Streamlined milestone and reward criteria styling
* Improved admin settings layout
* Removed duplicate directory structure
* Added automatic updates from GitHub repository

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.1.0 =
This update adds featured image support for milestones and rewards, fixes milestone event counting, improves geolocation, and resolves several UI issues. Upgrade recommended for all users.