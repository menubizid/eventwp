<?php
/**
 * Plugin Name:       EventWP — Sport Event Active Nation
 * Plugin URI:        https://activenation.id/eventwp
 * Description:       Full-stack CMS Sport Event (Zumba Step, 3x3 Basket, Padel, Yoga, Run) untuk WordPress. Custom Post Types + Gutenberg Blocks + Widget + Shortcode + REST API + App Mobile Web. Data tersimpan di database yang sama dengan WordPress (phpMyAdmin — MySQL/MariaDB).
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Active Nation
 * Author URI:        https://activenation.id
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       eventwp
 * Domain Path:       /languages
 *
 * @package EventWP
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'EVENTWP_VERSION' ) ) {
	define( 'EVENTWP_VERSION', '1.0.0' );
}

if ( ! defined( 'EVENTWP_DB_VERSION' ) ) {
	define( 'EVENTWP_DB_VERSION', '1.0.0' );
}

if ( ! defined( 'EVENTWP_FILE' ) ) {
	define( 'EVENTWP_FILE', __FILE__ );
}

if ( ! defined( 'EVENTWP_DIR' ) ) {
	define( 'EVENTWP_DIR', __DIR__ );
}

if ( ! defined( 'EVENTWP_URL' ) ) {
	define( 'EVENTWP_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
}

if ( ! defined( 'EVENTWP_REST_NAMESPACE' ) ) {
	define( 'EVENTWP_REST_NAMESPACE', 'eventwp/v1' );
}

/**
 * Autoload — map EventWP_* class names to their files.
 *
 * @param string $class Class name.
 * @return void
 */
function eventwp_autoload( $class ) {
	if ( 0 !== strpos( $class, 'EventWP_' ) ) {
		return;
	}
	$slug = strtolower( str_replace( 'EventWP_', '', $class ) );
	$slug = str_replace( '_', '-', $slug );
	$dir  = EVENTWP_DIR . '/includes';
	$file = $dir . '/class-eventwp-' . $slug . '.php';
	if ( is_readable( $file ) ) {
		require_once $file;
	}
}
spl_autoload_register( 'eventwp_autoload' );

require_once EVENTWP_DIR . '/includes/class-eventwp.php';

/**
 * Boot on plugins_loaded so dependant APIs (wpdb, roles, rest) are ready.
 */
function eventwp_boot() {
	EventWP::instance();
}
add_action( 'plugins_loaded', 'eventwp_boot', 5 );

/**
 * Plugin activation.
 */
register_activation_hook( __FILE__, array( 'EventWP', 'activate' ) );

/**
 * Plugin deactivation.
 */
register_deactivation_hook( __FILE__, array( 'EventWP', 'deactivate' ) );
