<?php

namespace LinkNacional\Filebrowser\Includes;

use LinkNacional\Filebrowser\Includes\LinkNacionalFilebrowserLoader;
use LinkNacional\Filebrowser\Admin\LinkNacionalFilebrowserAdmin;
use LinkNacional\Filebrowser\Public\LinkNacionalFilebrowserPublic;

class LinkNacionalFilebrowser {

	protected $loader;
	protected $plugin_name;
	protected $version;

	public function __construct() {
		if ( defined( 'LKN_FILEBROWSER_VERSION' ) ) {
			$this->version = LKN_FILEBROWSER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'linknacional-file-browser';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {
		$this->loader = new LinkNacionalFilebrowserLoader();
	}

	private function define_admin_hooks() {
		$plugin_admin = new LinkNacionalFilebrowserAdmin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
		$this->loader->add_action( 'wp_ajax_lkn_create_folder', $plugin_admin, 'create_folder_ajax' );
		$this->loader->add_action( 'wp_ajax_lkn_upload_file', $plugin_admin, 'upload_file_ajax' );
		$this->loader->add_action( 'wp_ajax_lkn_delete_folder', $plugin_admin, 'delete_folder_ajax' );
		$this->loader->add_action( 'wp_ajax_lkn_delete_file', $plugin_admin, 'delete_file_ajax' );
		$this->loader->add_action( 'wp_ajax_lkn_update_folder_name', $plugin_admin, 'update_folder_name_ajax' );
		$this->loader->add_action( 'wp_ajax_lkn_update_file_name', $plugin_admin, 'update_file_name_ajax' );
		$this->loader->add_action( 'wp_ajax_lkn_get_folder_contents', $plugin_admin, 'get_folder_contents_ajax' );
		$this->loader->add_action( 'wp_ajax_lkn_get_all_folders', $plugin_admin, 'get_all_folders_ajax' );
		$this->loader->add_action( 'wp_ajax_lkn_get_folder_files', $plugin_admin, 'get_folder_files_ajax' );
		$this->loader->add_action( 'wp_ajax_lkn_get_all_folders_admin_frontend', $plugin_admin, 'get_all_folders_admin_frontend' );
		$this->loader->add_action( 'wp_ajax_nopriv_lkn_get_admin_nonce', $plugin_admin, 'lkn_get_admin_nonce');
		$this->loader->add_action( 'wp_ajax_lkn_get_admin_nonce', $plugin_admin, 'lkn_get_admin_nonce');
	}

	private function define_public_hooks() {
		$plugin_public = new LinkNacionalFilebrowserPublic( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init', $plugin_public, 'register_shortcode' );
		$this->loader->add_action( 'wp_ajax_lkn_frontend_get_contents', $plugin_public, 'get_folder_contents_frontend' );
		$this->loader->add_action( 'wp_ajax_nopriv_lkn_frontend_get_contents', $plugin_public, 'get_folder_contents_frontend' );
		$this->loader->add_action( 'wp_ajax_lkn_frontend_get_all_folders', $plugin_public, 'get_all_folders_frontend' );
		$this->loader->add_action( 'wp_ajax_nopriv_lkn_frontend_get_all_folders', $plugin_public, 'get_all_folders_frontend' );
		$this->loader->add_action( 'wp_ajax_lkn_frontend_search', $plugin_public, 'search_files_frontend' );
		$this->loader->add_action( 'wp_ajax_nopriv_lkn_frontend_search', $plugin_public, 'search_files_frontend' );
		$this->loader->add_action( 'wp_ajax_lkn_frontend_get_folder_files', $plugin_public, 'get_folder_files_frontend' );
		$this->loader->add_action( 'wp_ajax_nopriv_lkn_frontend_get_folder_files', $plugin_public, 'get_folder_files_frontend' );
		$this->loader->add_action( 'wp_ajax_nopriv_lkn_get_public_nonce', $plugin_public, 'lkn_get_public_nonce');
		$this->loader->add_action( 'wp_ajax_lkn_get_public_nonce', $plugin_public, 'lkn_get_public_nonce');
	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}
}
