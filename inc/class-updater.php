<?php
/**
 * Updates add-ons based on the main plugin license.
 *
 * @package WP_Ultimo_Support_Agents
 * @subpackage Updater
 * @since 1.0.0
 */

namespace WP_Ultimo_Support_Agents;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Updates add-ons based on the main plugin license.
 *
 * @since 1.0.0
 */
class Updater {

	use \WP_Ultimo_Support_Agents\Traits\Singleton;

	/**
	 * Holds the URL for serving build files.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	public $updates_url = 'https://versions.nextpress.co/updates/';

	/**
	 * Add the main hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

    // Enable our auto updates library
		add_action('init', array($this, 'enable_auto_updates'));

	} // end init;

	/**
	 * Adds the auto-update hooks, if a license is present.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function enable_auto_updates() {

		$license_key = \WP_Ultimo\License::get_instance()->get_license_key();

		if (!$license_key) {

			return;

		} // end if;

		$url = add_query_arg(array(
			'license_key' => rawurlencode($license_key),
			'slug'        => 'wp-ultimo-support-agents',
			'action'      => 'get_metadata',
		), $this->updates_url);

    // Instantiating it
		$update_checker = \Puc_v4_Factory::buildUpdateChecker(
		$url,                           // Metadata URL.
		WP_ULTIMO_SUPPORT_AGENTS_PLUGIN_FILE,        // Full path to the main plugin file.
		'wp-ultimo-support-agents'                   // Plugin slug. Usually it's the same as the name of the directory.
		);

	} // end enable_auto_updates;

} // end class Updater;
