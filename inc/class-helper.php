<?php
/**
 * WP Ultimo Support Agents helper methods for including and rendering files, assets, etc
 *
 * @package WP_Ultimo_Support_Agents
 * @subpackage Helper
 * @since 1.0.0
 */

namespace WP_Ultimo_Support_Agents; // phpcs:ignore

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WU Support Agents helper methods for including and rendering files, assets, etc
 *
 * @since 1.0.0
 */
class Helper {

	use \WP_Ultimo_Support_Agents\Traits\Singleton;

	/**
	 * List of view types that are subject to view overriding
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $replaceable_views = array(
		'signup',
		'emails',
		'forms',
	);

	/**
	 * Adds hooks to be added at the original instatiation.
	 *
	 * @since 1.9.0
	 */
	public function init() {

		// Overwrite
		add_filter('wp_ultimo_support_agents_view_override', array($this, 'view_override'), 10, 3);

	} // end init;

	/**
	 * Returns the full path to the plugin folder
	 *
	 * @since 0.0.1
	 * @param string $dir Path relative to the plugin root you want to access.
	 * @return string
	 */
	public function path($dir) {

		return WP_ULTIMO_SUPPORT_AGENTS_PLUGIN_DIR . $dir;

	} // end path;

	/**
	 * Returns the URL to the plugin folder.
	 *
	 * @since 0.0.1
	 * @param string $dir Path relative to the plugin root you want to access.
	 * @return string
	 */
	public function url($dir) {

		return apply_filters('wu_support_agents_url', WP_ULTIMO_SUPPORT_AGENTS_PLUGIN_URL . $dir); // phpcs:disable

	} // end url;

	/**
	 * Shorthand for url('assets/img'). Returns the URL for assets inside the assets folder.
	 *
	 * @since 0.0.1
	 * @param string $asset Asset file name with the extention.
	 * @param string $assets_dir Assets sub-directory. Defaults to 'img'.
	 * @return string
	 */
	public function get_asset($asset, $assets_dir = 'img') {

		if (!defined('SCRIPT_DEBUG') || !SCRIPT_DEBUG) {

			$asset = preg_replace('/(?<!\.min)(\.js|\.css)/', '.min$1', $asset);

		} // end if;

		return $this->url("assets/$assets_dir/$asset");

	} // end get_asset;

	/**
	 * Renders a view file from the view folder.
	 *
	 * @since 0.0.1
	 * @param string  $view View file to render. Do not include the .php extension.
	 * @param boolean $vars Key => Value pairs to be made available as local variables inside the view scope.
	 * @return void
	 */
	public function render($view, $vars = false) {

		$template = $this->path("views/$view.php");

		// Make passed variables available
		if (is_array($vars)) {

			extract($vars); // phpcs:ignore

		} // end if;

		/**
		 * Only allow templating for emails and signup for now
		 */
		if (preg_match('/(' . implode('|', $this->replaceable_views) . ')\w+/', $view)) {

			$template = apply_filters('wp_ultimo_support_agents_view_override', $template, $view);

		} // end if;

		// Load our view
		include $template;

	} // end render;

	/**
	 * Allows us to search templates when we are not in the main site environmentå
	 *
	 * @todo Can this be improved? Do we need to re-check the Template Path in here? Not sure...
	 *
	 * @since 1.9.0
	 * @param string|array $template_names Template file(s) to search for, in order.
	 * @param bool         $load           If true the template file will be loaded if it is found.
	 * @param bool         $require_once   Whether to require_once or require. Default true. Has no effect if $load is false.
	 * @return string The template filename if one is located.
	 */
	public function custom_locate_template($template_names, $load = false, $require_once = true) {

		$stylesheet_path = get_stylesheet_directory();

		$located = '';

		foreach ((array) $template_names as $template_name) {

			if (!$template_name) {

				continue;

			} // end if;

			if (file_exists( $stylesheet_path . '/' . $template_name)) {

				$located = $stylesheet_path . '/' . $template_name;

				break;

			} elseif (file_exists(TEMPLATEPATH . '/' . $template_name)) {

				$located = TEMPLATEPATH . '/' . $template_name;

				break;

			} elseif (file_exists(ABSPATH . WPINC . '/theme-compat/' . $template_name)) {

				$located = ABSPATH . WPINC . '/theme-compat/' . $template_name;

				break;

			} // end if;

		} // end foreach;

		if ($load && '' !== $located) {

			load_template($located, $require_once);

		} // end if;

		return $located;

	} // end custom_locate_template;

	/**
	 * Check if an alternative view exists and override
	 *
	 * @param  string $original_path The original path of the view.
	 * @param  string $view          View path.
	 * @return string  The new path.
	 */
	public function view_override($original_path, $view) {

		if (is_main_site()) {

			$found = locate_template("wu-support-agents/$view.php");

		} else {

			$found = $this->custom_locate_template("wu-support-agents/$view.php");

		} // end if;

		return $found ? $found : $original_path;

	} // end view_override;

	/**
	 * This function return 'slugfied' options terms to be used as options ids.
	 *
	 * @since 0.0.1
	 * @param string $term Returns a string based on the term and this plugin slug.
	 * @return string
	 */
	public function slugfy($term) {

		return "wp_ultimo_support_agents_$term";

	} // end slugfy;

	/**
	 * Get the value of a slugfied network option
	 *
	 * @since 1.9.6
	 * @param string $option_name Option name.
	 * @param mixed  $default The default value.
	 * @return mixed
	 */
	public function get_option($option_name = 'settings', $default = array()) {

		$option_value = get_option(null, $this->slugfy($option_name), $default);

		return apply_filters('wp_ultimo_support_agents_get_option', $option_value, $option_name, $default);

	} // end get_option;

	/**
	 * Save slugfied network option
	 *
	 * @since 1.9.6
	 *
	 * @param string $option_name The option name to save.
	 * @param mixed  $value       The new value of the option.
	 * @return boolean
	 */
	public function save_option($option_name = 'settings', $value = false) {

		return update_option(null, $this->slugfy($option_name), $value);

	} // end save_option;

} // end class Helper;
