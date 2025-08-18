<?php

namespace Lkn\WPFilebrowser;

use Lkn\WPFilebrowser\LknwpFilebrowserLoader;
use Lkn\WPFilebrowser\LknwpFilebrowserI18n;
use Lkn\WPFilebrowser\Admin\LknwpFilebrowserAdmin;
use Lkn\WPFilebrowser\Public\LknwpFilebrowserPublic;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Lkn\WPFilebrowser
 * @author     Link Nacional <contato@linknacional.com>
 */
class LknwpFilebrowser {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      LknwpFilebrowserLoader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'LKNWP_FILEBROWSER_VERSION' ) ) {
			$this->version = LKNWP_FILEBROWSER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'lknwp-filebrowser';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		$this->loader = new LknwpFilebrowserLoader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the LknwpFilebrowserI18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new LknwpFilebrowserI18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new LknwpFilebrowserAdmin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
		$this->loader->add_action( 'wp_ajax_lknwp_create_folder', $plugin_admin, 'create_folder_ajax' );
		$this->loader->add_action( 'wp_ajax_lknwp_upload_file', $plugin_admin, 'upload_file_ajax' );
		$this->loader->add_action( 'wp_ajax_lknwp_delete_folder', $plugin_admin, 'delete_folder_ajax' );
		$this->loader->add_action( 'wp_ajax_lknwp_delete_file', $plugin_admin, 'delete_file_ajax' );
		$this->loader->add_action( 'wp_ajax_lknwp_update_folder_name', $plugin_admin, 'update_folder_name_ajax' );
		$this->loader->add_action( 'wp_ajax_lknwp_update_file_name', $plugin_admin, 'update_file_name_ajax' );
		$this->loader->add_action( 'wp_ajax_lknwp_get_folder_contents', $plugin_admin, 'get_folder_contents_ajax' );
		$this->loader->add_action( 'wp_ajax_lknwp_get_all_folders', $plugin_admin, 'get_all_folders_ajax' );
		$this->loader->add_action( 'wp_ajax_lknwp_get_folder_files', $plugin_admin, 'get_folder_files_ajax' );
		$this->loader->add_action( 'wp_ajax_lknwp_get_all_folders_admin_frontend', $plugin_admin, 'get_all_folders_admin_frontend' );
		$this->loader->add_action( 'wp_ajax_nopriv_lknwp_get_admin_nonce', $plugin_admin, 'lknwp_get_admin_nonce');
		$this->loader->add_action( 'wp_ajax_lknwp_get_admin_nonce', $plugin_admin, 'lknwp_get_admin_nonce');
		$this->loader->add_action( 'init', $this, 'updater_init' );
	}

	public function updater_init()
	{
		require_once __DIR__ . '/plugin-updater/plugin-update-checker.php';

		return new \Lkn_Puc_Plugin_UpdateChecker(
			'https://api.linknacional.com/v3/u/?slug=lknwp-filebrowser',
			PLUGIN_FILE,
			'lknwp-filebrowser'
		);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new LknwpFilebrowserPublic( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'register_shortcode' );
		$this->loader->add_action( 'wp_ajax_lknwp_frontend_get_contents', $plugin_public, 'get_folder_contents_frontend' );
		$this->loader->add_action( 'wp_ajax_nopriv_lknwp_frontend_get_contents', $plugin_public, 'get_folder_contents_frontend' );
		$this->loader->add_action( 'wp_ajax_lknwp_frontend_get_all_folders', $plugin_public, 'get_all_folders_frontend' );
		$this->loader->add_action( 'wp_ajax_nopriv_lknwp_frontend_get_all_folders', $plugin_public, 'get_all_folders_frontend' );
		$this->loader->add_action( 'wp_ajax_lknwp_frontend_search', $plugin_public, 'search_files_frontend' );
		$this->loader->add_action( 'wp_ajax_nopriv_lknwp_frontend_search', $plugin_public, 'search_files_frontend' );
		$this->loader->add_action( 'wp_ajax_lknwp_frontend_get_folder_files', $plugin_public, 'get_folder_files_frontend' );
		$this->loader->add_action( 'wp_ajax_nopriv_lknwp_frontend_get_folder_files', $plugin_public, 'get_folder_files_frontend' );
		$this->loader->add_action( 'wp_ajax_nopriv_lknwp_get_public_nonce', $plugin_public, 'lknwp_get_public_nonce');
		$this->loader->add_action( 'wp_ajax_lknwp_get_public_nonce', $plugin_public, 'lknwp_get_public_nonce');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    LknwpFilebrowserLoader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
