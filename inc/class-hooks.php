<?php
/**
 * WP Ultimo Support Agents activation and deactivation hooks
 *
 * @package WP_Ultimo_Support_Agents
 * @subpackage Hooks
 * @since 1.0.0
 */

namespace WP_Ultimo_Support_Agents;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Support Agents activation and deactivation hooks
 *
 * @since 1.0.0
 */
class Hooks {

	/**
	 * Static-only class.
	 */
	private function __construct() {} // end __construct;

	/**
	 * Register the activation and deactivation hooks
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function init() {

		/**
		 * Runs on WP Ultimo Support Agents activation
		 */
		register_activation_hook(WP_ULTIMO_SUPPORT_AGENTS_PLUGIN_FILE, array('WP_Ultimo_Support_Agents\Hooks', 'on_activation'));

		/**
		 * Runs on WP Ultimo Support Agents deactivation
		 */
		register_deactivation_hook(WP_ULTIMO_SUPPORT_AGENTS_PLUGIN_FILE, array('WP_Ultimo_Support_Agents\Hooks', 'on_deactivation'));

		/**
		 * Runs the activation hook.
		 */
		add_action('plugins_loaded', array('WP_Ultimo_Support_Agents\Hooks', 'on_activation_do'), 1);

	} // end init;

	/**
	 *  Runs when WP Ultimo Support Agents is activated
	 *
	 * @since 1.9.6 It now uses hook-based approach, it is up to each sub-class to attach their own routines.
	 * @since 1.2.0
	 */
	public static function on_activation() {
		/*
		 * Set the activation flag
		 */
		update_network_option(null, 'wp_ultimo_support_agents_activation', 'yes');

	} // end on_activation;

	/**
	 * Runs whenever the activation flag is set.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function on_activation_do() {

		if (get_network_option(null, 'wp_ultimo_support_agents_activation') === 'yes' && isset($_GET['activate'])) {

			// Removes the flag
			delete_network_option(null, 'wp_ultimo_support_agents_activation');

			/**
			 * Let other parts of the plugin attach their routines for activation
			 *
			 * @since 1.9.6
			 * @return void
			 */
			do_action('wp_ultimo_support_agents_activation');

		} // end if;

	} // end on_activation_do;

	/**
	 * Runs when WP Ultimo Support Agents is deactivated
	 *
	 * @since 1.9.6 It now uses hook-based approach, it is up to each sub-class to attach their own routines.
	 * @since 1.2.0
	 */
	public static function on_deactivation() {

		/**
		 * Let other parts of the plugin attach their routines for deactivation
		 *
		 * @since 1.9.6
		 * @return void
		 */
		do_action('wp_ultimo_support_agents_deactivation');

	} // end on_deactivation;

} // end class Hooks;
