<?php
/**
 * The main plugin class.
 *
 * @since      1.0.0
 * @package    Events_Milestones
 */

class Events_Milestones {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      EM_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->plugin_name = 'events-milestones';
        $this->version = EM_VERSION;

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_post_types();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        // The class responsible for orchestrating the actions and filters of the core plugin.
        require_once EM_PLUGIN_PATH . 'includes/class-em-loader.php';

        // The class responsible for defining all actions that occur in the admin area.
        require_once EM_PLUGIN_PATH . 'admin/class-em-admin.php';

        // The class responsible for defining all actions that occur in the public-facing side of the site.
        require_once EM_PLUGIN_PATH . 'public/class-em-public.php';

        // The class responsible for defining all post types.
        require_once EM_PLUGIN_PATH . 'includes/post-types/class-em-milestone.php';
        require_once EM_PLUGIN_PATH . 'includes/post-types/class-em-reward.php';
        require_once EM_PLUGIN_PATH . 'includes/post-types/class-em-user-milestone.php';

        // The class responsible for handling multisite functionality.
        require_once EM_PLUGIN_PATH . 'includes/class-em-multisite.php';

        // The class responsible for handling user check-ins.
        require_once EM_PLUGIN_PATH . 'includes/class-em-checkin.php';

        $this->loader = new EM_Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new EM_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new EM_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
        // Register shortcodes
        $this->loader->add_action('init', $plugin_public, 'register_shortcodes');
        
        // Register AJAX handlers for check-ins and nearby events
        $checkin = new EM_Checkin();
        $this->loader->add_action('wp_ajax_em_checkin', $checkin, 'ajax_checkin');
        $this->loader->add_action('wp_ajax_nopriv_em_checkin', $checkin, 'ajax_checkin');
        $this->loader->add_action('wp_ajax_em_get_nearby_events', $checkin, 'ajax_get_nearby_events');
        $this->loader->add_action('wp_ajax_nopriv_em_get_nearby_events', $checkin, 'ajax_get_nearby_events');
    }

    /**
     * Register all custom post types and taxonomies.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_post_types() {
        // Initialize milestone post type
        $milestone = new EM_Milestone();
        $this->loader->add_action('init', $milestone, 'register_post_type', 0);
        $this->loader->add_action('add_meta_boxes', $milestone, 'add_meta_boxes');
        $this->loader->add_action('save_post', $milestone, 'save_meta_boxes');

        // Initialize reward post type
        $reward = new EM_Reward();
        $this->loader->add_action('init', $reward, 'register_post_type', 0);
        $this->loader->add_action('add_meta_boxes', $reward, 'add_meta_boxes');
        $this->loader->add_action('save_post', $reward, 'save_meta_boxes');

        // Initialize user milestone tracking
        $user_milestone = new EM_User_Milestone();
        $this->loader->add_action('init', $user_milestone, 'register_post_type', 0);
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    EM_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}