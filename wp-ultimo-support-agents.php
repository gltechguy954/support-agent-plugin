<?php
/**
 * Plugin Name: UC Spaces: Support Agents
 * Description: Create and manage a special kind of UC user that has access to the Network Admin without being a super-admin (Support Agents), complete with granular permission controls.
 * Plugin URI: https://ucspaces.com/addons
 * Text Domain: wp-ultimo-support-agents
 * Version: 1.0.8
 * Author: UC Dev Team
 * Network: true
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /lang
 *
 * WP Ultimo Support Agents is distributed under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WP Ultimo Support Agents is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WP Ultimo Support Agents. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author   Arindo Duque and NextPress
 * @category Core
 * @package  WP_Ultimo_Support_Agents
 * @version  1.0.8
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

if (!defined('WP_ULTIMO_SUPPORT_AGENTS_PLUGIN_FILE')) {

	define('WP_ULTIMO_SUPPORT_AGENTS_PLUGIN_FILE', __FILE__);

} // end if;

/**
 * Require core file dependencies
 */
require_once __DIR__ . '/constants.php';

require_once __DIR__ . '/dependencies/autoload.php';

require_once __DIR__ . '/inc/class-autoloader.php';

require_once __DIR__ . '/inc/traits/trait-singleton.php';

/**
 * Setup autoloader
 */
WP_Ultimo_Support_Agents\Autoloader::init();

/**
 * Setup activation/deactivation hooks
 */
WP_Ultimo_Support_Agents\Hooks::init();

/**
 * Initializes the WP Ultimo Support Agents class
 *
 * This function returns the WP_Ultimo_Support_Agents class singleton, and
 * should be used to avoid declaring globals.
 *
 * @since 1.0.0
 * @return WP_Ultimo_Support_Agents
 */
function WP_Ultimo_Support_Agents() { // phpcs:ignore

	return WP_Ultimo_Support_Agents::get_instance();

} // end WP_Ultimo_Support_Agents;

// Initialize and set to global for back-compat
add_action('plugins_loaded', 'wp_ultimo_support_agents_init', 5);

/**
 * Wait before we have WP Ultimo available before hooking into it.
 *
 * @since 1.0.0
 * @return void
 */
function wp_ultimo_support_agents_init() {

	$GLOBALS['WP_Ultimo_Support_Agents'] = WP_Ultimo_Support_Agents();

} // end wp_ultimo_support_agents_init;
