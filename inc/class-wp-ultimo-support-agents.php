<?php
/**
 * WP Ultimo Support Agents main class.
 *
 * @package WP_Ultimo_Support_Agents
 * @since 1.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Support Agents main class
 *
 * This class instantiates our dependencies and load the things
 * our plugin needs to run.
 *
 * @package WP_Ultimo_Support_Agents
 * @since 1.0.0
 */
final class WP_Ultimo_Support_Agents {

    use \WP_Ultimo_Support_Agents\Traits\Singleton;

    /**
     * Checks if WP Ultimo Support Agents was loaded or not.
     *
     * This is set to true when all the WP Ultimo Support Agents requirements are met.
     *
     * @since 1.0.0
     * @var boolean
     */
    protected $loaded = false;

    /**
     * Version of the Plugin
     *
     * @var string
     */
    public $version = '1.0.8';

    /**
     * Helper instance
     *
     * @since 1.0.0
     * @var WP_Ultimo_Support_Agents\Helper
     */
    protected $helper;

    /**
     * Loads the necessary components into the main class
     *
     * @since 1.0.0
     * @return void
     */
    public function init() {
        /*
         * Loads the WP_Ultimo\Helper class.
         */
        $this->helper = WP_Ultimo_Support_Agents\Helper::get_instance();

        /*
         * Helper Functions
         */
        require_once $this->helper->path('inc/functions/helper.php');

        /*
         * Support Agent Functions
         */
        require_once $this->helper->path('inc/functions/support-agent.php');

        /*
         * Set up the text-domain for translations
         */
        $this->setup_textdomain();

        /*
         * Check if the WP Ultimo Support Agents requirements are present.
         *
         * Everything we need to run our setup install needs to be loaded before this
         * and have no dependencies outside of the classes loaded so far.
         */
        if (WP_Ultimo_Support_Agents\Requirements::met() === false) {

            return;

        } // end if;

        $this->loaded = true;

        /**
         * Run the updater.
         */
        WP_Ultimo_Support_Agents\Updater::get_instance();

        /*
         * Loads Permission Control
         */
        \WP_Ultimo_Support_Agents\Permission_Control::get_instance();

        /*
         * Loads admin pages
         */
        $this->load_admin_pages();

        /*
         * Loads Managers
         */
        $this->load_managers();

        /**
         * Triggers when all the dependencies were loaded
         *
         * Allows plugin developers to add new functionality. For example, support to new
         * Hosting providers, etc.
         *
         * @since 1.0.0
         */
        do_action('wp_ultimo_support_agents_load');

    } // end init;

    /**
     * Returns true if all the requirements are met.
     *
     * @since 1.0.0
     * @return boolean
     */
    public function is_loaded() {

        return $this->loaded;

    } // end is_loaded;

    /**
     * Setup the plugin text domain to be used in translations.
     *
     * @since 0.0.1
     * @return void
     */
    public function setup_textdomain() {
        /*
         * Loads the translation files.
         */
        load_plugin_textdomain('wp-ultimo-support-agents', false, dirname(WP_ULTIMO_SUPPORT_AGENTS_PLUGIN_BASENAME) . '/lang');

    } // end setup_textdomain;

    /**
     * Load the WU_Support_Agents Addon Admin Pages.
     *
     * @since 1.0.0
     * @return void
     */
    protected function load_admin_pages() {
        /*
         * Loads the Support Agents Pages
         */
        new WP_Ultimo_Support_Agents\Admin_Pages\Support_Agent_List_Admin_Page();

        new WP_Ultimo_Support_Agents\Admin_Pages\Support_Agent_Edit_Admin_Page();

    } // end load_admin_pages;

    /**
     * Load extra the WU PTM managers.
     *
     * @since 1.0.0
     * @return void
     */
    protected function load_managers() {
        /*
         * Loads the support Agent manager.
         */
        WP_Ultimo_Support_Agents\Managers\Support_Agent_Manager::get_instance();

    } // end load_managers;

} // end class WP_Ultimo_Support_Agents;
