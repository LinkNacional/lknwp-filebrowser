<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.linknacional.com.br
 * @since             1.0.0
 * @package           Lknwp_Filebrowser
 *
 * @wordpress-plugin
 * Plugin Name:       Link Nacional File Browser
 * Plugin URI:        https://www.linknacional.com.br
 * Description:       Create your folder structure and display it on the frontend.
 * Version:           1.0.1
 * Author:            Link Nacional
 * Author URI:        https://www.linknacional.com.br/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       lknwp-filebrowser
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'LKNWP_FILEBROWSER_VERSION', '1.0.1' );
define( 'PLUGIN_FILE', __FILE__ );
define( 'LINK_PLUGIN_NAME', 'lknwp-filebrowser' );

/**
 * Plugin constants
 */
define( 'LKNWP_FILEBROWSER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LKNWP_FILEBROWSER_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Autoloader using Composer
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
} else {
	// Manual autoloader fallback for PSR-4
	spl_autoload_register( function ( $class ) {
		$namespaces = [
			'Lkn\\WPFilebrowser\\Admin\\' => plugin_dir_path( __FILE__ ) . 'admin/',
			'Lkn\\WPFilebrowser\\Public\\' => plugin_dir_path( __FILE__ ) . 'public/',
			'Lkn\\WPFilebrowser\\Includes\\' => plugin_dir_path( __FILE__ ) . 'includes/'
		];

		foreach ($namespaces as $prefix => $base_dir) {
			$len = strlen( $prefix );
			if ( strncmp( $prefix, $class, $len ) !== 0 ) {
				continue;
			}

			$relative_class = substr( $class, $len );
			$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

			if ( file_exists( $file ) ) {
				require $file;
				return;
			}
		}
	});
}

use Lkn\WPFilebrowser\Includes\LknwpFilebrowser;
use Lkn\WPFilebrowser\Includes\LknwpFilebrowserActivator;
use Lkn\WPFilebrowser\Includes\LknwpFilebrowserDeactivator;

// Include backward compatibility aliases
require_once plugin_dir_path( __FILE__ ) . 'includes/Aliases.php';

/**
 * The code that runs during plugin activation.
 */
function activate_lknwp_filebrowser() {
	LknwpFilebrowserActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_lknwp_filebrowser() {
	LknwpFilebrowserDeactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_lknwp_filebrowser' );
register_deactivation_hook( __FILE__, 'deactivate_lknwp_filebrowser' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_lknwp_filebrowser() {
	$plugin = new LknwpFilebrowser();
	$plugin->run();
}
run_lknwp_filebrowser();
