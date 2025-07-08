<?php
/**
 * Support Agent Manager
 *
 * Handles processes related to Support Agents.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Support_Agent_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo_Support_Agents\Managers;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to webhooks.
 *
 * @since 2.0.0
 */
class Support_Agent_Manager extends \WP_Ultimo\Managers\Base_Manager {

	use \WP_Ultimo\Apis\Rest_Api, \WP_Ultimo\Apis\WP_CLI, \WP_Ultimo_Support_Agents\Traits\Singleton;

	/**
	 * The manager slug.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $slug = 'customer';

	/**
	 * The model class associated to this manager.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $model_class = '\\WP_Ultimo_Support_Agents\\Models\\Support_Agent';

	/**
	 * Overrides the default constructor to set the rest base value.
	 *
	 * @since 1.0.3
	 */
	public function __construct() {

		$this->rest_base = 'support_agent';

	} // end __construct;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		$this->enable_rest_api();

		$this->enable_wp_cli();

		add_action('wp_network_dashboard_setup', array($this, 'clean_up_widgets'), 999);

		add_filter('wu_delete_form_get_object_support_agent', array($this, 'get_correct_object_for_deletion'), 10, 2);

	} // end init;

	/**
	 * Get the correct object for deletion.
	 *
	 * @since 2.0.0
	 *
	 * @param null|\WP_Ultimo\Models\Base_Model $object The object.
	 * @param integer                           $id The id of the object.
	 * @return \WP_Ultimo\Models\Base_Model
	 */
	public function get_correct_object_for_deletion($object, $id) {

		if (!$object) {

			$object = wusa_get_support_agent($id);

		} // end if;

		return $object;

	} // end get_correct_object_for_deletion;

	/**
	 * Cleans the widgets that are not supported.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function clean_up_widgets() {

		global $wp_meta_boxes;

		$support_agent = wusa_get_current_support_agent();

		if ($support_agent) {

			$widgets = $support_agent->get_network_dashboard_widgets();

			foreach ($widgets as $key => $value) {

				if ($value || empty($key)) {

					continue;

				} // end if;

				$place_priority_id = explode(':', $key);

				if (isset($wp_meta_boxes['dashboard-network'][$place_priority_id[0]][$place_priority_id[1]][$place_priority_id[2]])) {

					unset($wp_meta_boxes['dashboard-network'][$place_priority_id[0]][$place_priority_id[1]][$place_priority_id[2]]);

				} // end if;

				if (isset($wp_meta_boxes['dashboard-network']['side'][$place_priority_id[1]][$place_priority_id[2]])) {

					unset($wp_meta_boxes['dashboard-network']['side'][$place_priority_id[1]][$place_priority_id[2]]);

				} // end if;

			} // end foreach;

		} // end if;

	} // end clean_up_widgets;

} // end class Support_Agent_Manager;
