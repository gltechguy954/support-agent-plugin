<?php
/**
 * A trait that defines the singleton.
 *
 * @package WP_Ultimo_Support_Agents
 * @since 1.0.0
 */

namespace WP_Ultimo_Support_Agents\Traits;

/**
 * Singleton trait.
 */
trait Singleton {

	/**
	 * Makes sure we are only using one instance of the class
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Returns the instance of WP_Ultimo_Support_Agents
	 *
	 * @return object
	 */
	public static function get_instance() {

		if (!static::$instance instanceof static) {

			static::$instance = new static();

			static::$instance->init();

		} // end if;

		return static::$instance;

	} // end get_instance;

	/**
	 * Runs only once, at the first instantiation of the Singleton.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {} // end init;

} // end trait Singleton;
