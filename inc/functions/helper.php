<?php
/**
 * General helper functions for WP Ultimo Support Agents manager.
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo_Support_Agents/Helper
 * @version     1.0.0
 */

/**
 * Returns the WP Ultimo Support Agents version.
 *
 * @since 1.0.0
 * @return string
 */
function wusa_get_version() {

	return WP_Ultimo_Support_Agents()->version;

} // end wusa_get_version;
