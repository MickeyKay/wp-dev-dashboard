<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://wordpress.org/plugins/wp-dev-dashboard
 * @since             1.0.0
 * @package           WP_Dev_Dashboard
 *
 * @wordpress-plugin
 * Plugin Name:       WP Dev Dashboard
 * Plugin URI:        http://wordpress.org/plugins/wp-dev-dashboard
 * Description:       Easily see all of your unresolved plugin & theme support requests.
 * Version:           1.0.0
 * Author:            Mickey Kay Creative
 * Author URI:        http://mickeykaycreative.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-dev-dashboard
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-dev-dashboard-activator.php
 */
function activate_wp_dev_dashboard() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-dev-dashboard-activator.php';
	WP_Dev_Dashboard_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-dev-dashboard-deactivator.php
 */
function deactivate_wp_dev_dashboard() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-dev-dashboard-deactivator.php';
	WP_Dev_Dashboard_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_dev_dashboard' );
register_deactivation_hook( __FILE__, 'deactivate_wp_dev_dashboard' );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-dev-dashboard.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_dev_dashboard() {

	// Pass main plugin file through to plugin class for later use.
	$args = array(
		'plugin_file' => __FILE__,
	);

	$plugin = WP_Dev_Dashboard::get_instance( $args );
	$plugin->run();

}
run_wp_dev_dashboard();
