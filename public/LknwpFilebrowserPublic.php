<?php

namespace Lkn\WPFilebrowser\Public;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Lkn\WPFilebrowser\Public
 * @author     Link Nacional <contato@linknacional.com>
 */
class LknwpFilebrowserPublic {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, LKNWP_FILEBROWSER_PLUGIN_URL . 'public/css/lknwp-filebrowser-public.css', array(), LKNWP_FILEBROWSER_VERSION, 'all' );
		wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, LKNWP_FILEBROWSER_PLUGIN_URL . 'public/js/lknwp-filebrowser-public.js', array( 'jquery' ), LKNWP_FILEBROWSER_VERSION, false );
		wp_localize_script( $this->plugin_name, 'lknwp_public_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'loading_text' => __( 'Loading...', 'lknwp-filebrowser' ),
			'searching_text' => __( 'Searching...', 'lknwp-filebrowser' ),
			'error_loading_text' => __( 'Error loading contents', 'lknwp-filebrowser' ),
			'error_search_text' => __( 'Error performing search', 'lknwp-filebrowser' ),
			'error_folders_text' => __( 'Error loading folders', 'lknwp-filebrowser' ),
			'empty_folder_text' => __( 'This folder is empty', 'lknwp-filebrowser' ),
			'empty_folder_desc' => __( 'No files or folders found in this location.', 'lknwp-filebrowser' ),
			'no_results_text' => __( 'No results found', 'lknwp-filebrowser' ),
			'no_results_desc' => __( 'Try adjusting your search terms.', 'lknwp-filebrowser' ),
			'folder_text' => __( 'Folder', 'lknwp-filebrowser' ),
			'download_text' => __( 'DOWNLOAD', 'lknwp-filebrowser' ),
			'search_results_text' => __( 'Search results for', 'lknwp-filebrowser' ),
			'found_items_text' => __( 'Found', 'lknwp-filebrowser' ),
			'items_text' => __( 'items', 'lknwp-filebrowser' )
		));
	}

	/**
	 * Create a nonce
	 */
	public function lknwp_get_public_nonce() {
		$action_name = isset($_POST['action_name']) ? sanitize_text_field(wp_unslash($_POST['action_name'])) : '';
		if (!$action_name) {
			wp_send_json_error('Action name required');
		}
		$nonce = wp_create_nonce($action_name);
		wp_send_json_success(array('nonce' => $nonce));
	}

	/**
	 * Register shortcode
	 */
	public function register_shortcode() {
		add_shortcode( 'lknwp_filebrowser', array( $this, 'render_filebrowser_shortcode' ) );
	}

	/**
	 * Render filebrowser shortcode
	 */
	public function render_filebrowser_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'folder_id' => 0,
			'show_search' => 'true',
			'show_breadcrumb' => 'true',
			'show_folder_tree' => 'true',
			'layout' => 'grid' // grid or list
		), $atts );

		$folder_id = intval( $atts['folder_id'] );
		$show_search = $atts['show_search'] === 'true';
		$show_breadcrumb = $atts['show_breadcrumb'] === 'true';
		$show_folder_tree = $atts['show_folder_tree'] === 'true';
		$layout = in_array( $atts['layout'], array( 'grid', 'list' ) ) ? $atts['layout'] : 'grid';

		ob_start();
		?>
		<div class="lknwp-filebrowser-public" data-folder-id="<?php echo esc_attr( $folder_id ); ?>" data-layout="<?php echo esc_attr( $layout ); ?>">
			
			<?php if ( $show_search ): ?>
			<div class="lknwp-search-container">
				<div class="lknwp-search-box">
					<input type="text" id="lknwp-search-input" placeholder="<?php \_e( 'Search files and folders...', 'lknwp-filebrowser' ); ?>">
					<button type="button" id="lknwp-search-btn">
						<i class="fas fa-search"></i>
					</button>
					<button type="button" id="lknwp-clear-search" style="display: none;">
						<i class="fas fa-times"></i>
					</button>
				</div>
			</div>
			<?php endif; ?>

			<div class="lknwp-file-manager-public">
				<?php if ( $show_folder_tree ): ?>
				<div class="lknwp-sidebar-public">
					<h4><?php \_e( 'Folders', 'lknwp-filebrowser' ); ?></h4>
					<div id="lknwp-folder-tree-public">
						<div class="loading"><i class="fas fa-spinner"></i> <?php \_e( 'Loading folders...', 'lknwp-filebrowser' ); ?></div>
					</div>
				</div>
				<?php endif; ?>

				<div class="lknwp-content-public <?php echo !$show_folder_tree ? 'full-width' : ''; ?>">
					<?php if ( $show_breadcrumb ): ?>
					<div class="lknwp-breadcrumb-public">
						<span id="lknwp-current-path"><?php \_e( 'Home', 'lknwp-filebrowser' ); ?></span>
					</div>
					<?php endif; ?>

					<div class="lknwp-layout-controls">
						<button type="button" class="layout-btn <?php echo $layout === 'grid' ? 'active' : ''; ?>" data-layout="grid">
							<i class="fas fa-th"></i>
						</button>
						<button type="button" class="layout-btn <?php echo $layout === 'list' ? 'active' : ''; ?>" data-layout="list">
							<i class="fas fa-list"></i>
						</button>
					</div>

					<div class="lknwp-filebrowser-content <?php echo esc_attr( $layout ); ?>">
						<div id="lknwp-loading" class="loading">
							<i class="fas fa-spinner"></i> <?php \_e( 'Loading...', 'lknwp-filebrowser' ); ?>
						</div>
					</div>
				</div>
			</div>

		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * AJAX handler for getting all folders (frontend tree)
	 */
	public function get_all_folders_frontend() {
		check_ajax_referer( 'lknwp_filebrowser_public_nonce', 'nonce' );
		
		global $wpdb;
		$folders_table = $wpdb->prefix . 'lknwp_filebrowser_folders';
		$files_table = $wpdb->prefix . 'lknwp_filebrowser_files';
		
		// Get all folders
		$folders = $wpdb->get_results(
			"SELECT * FROM $folders_table ORDER BY parent_id ASC, name ASC"
		);
		
		// Get all files
		$files = $wpdb->get_results(
			"SELECT * FROM $files_table ORDER BY folder_id ASC, original_name ASC"
		);
		
		wp_send_json_success( array(
			'folders' => $folders,
			'files' => $files
		) );
	}

	/**
	 * AJAX handler for getting folder contents (frontend)
	 */
	public function get_folder_contents_frontend() {
		check_ajax_referer( 'lknwp_filebrowser_public_nonce', 'nonce' );
		
		$folder_id = intval( $_POST['folder_id'] );
		
		$contents = $this->get_folder_contents( $folder_id );
		
		wp_send_json_success( $contents );
	}

	/**
	 * AJAX handler for searching files (frontend)
	 */
	public function search_files_frontend() {
		check_ajax_referer( 'lknwp_filebrowser_public_nonce', 'nonce' );
		
		$search_term = sanitize_text_field( $_POST['search_term'] );
		$folder_id = intval( $_POST['folder_id'] );
		
		if ( empty( $search_term ) ) {
			wp_send_json_error( __( 'Search term is required', 'lknwp-filebrowser' ) );
		}

		global $wpdb;
		$folders_table = $wpdb->prefix . 'lknwp_filebrowser_folders';
		$files_table = $wpdb->prefix . 'lknwp_filebrowser_files';

		// Search folders with parent info for path building
		$folders = $wpdb->get_results( $wpdb->prepare(
			"SELECT f.*, p.name as parent_name, p.path as parent_path 
			 FROM $folders_table f 
			 LEFT JOIN $folders_table p ON f.parent_id = p.id 
			 WHERE f.name LIKE %s 
			 ORDER BY f.name ASC",
			'%' . $wpdb->esc_like( $search_term ) . '%'
		));

		// Enhance folders with full path information
		foreach ($folders as $folder) {
			$folder->full_path = $this->build_folder_path( $folder->id );
		}

		// Search files with folder info
		$files = $wpdb->get_results( $wpdb->prepare(
			"SELECT f.*, folder.name as folder_name, folder.path as folder_path 
			 FROM $files_table f 
			 LEFT JOIN $folders_table folder ON f.folder_id = folder.id 
			 WHERE f.original_name LIKE %s 
			 ORDER BY f.original_name ASC",
			'%' . $wpdb->esc_like( $search_term ) . '%'
		));

		$results = array(
			'folders' => $folders,
			'files' => $files,
			'search_term' => $search_term
		);
		
		wp_send_json_success( $results );
	}

	/**
	 * Get folder contents (folders and files) - shared method
	 */
	private function get_folder_contents( $folder_id ) {
		global $wpdb;
		$folders_table = $wpdb->prefix . 'lknwp_filebrowser_folders';
		$files_table = $wpdb->prefix . 'lknwp_filebrowser_files';

		// Get subfolders
		$folders = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $folders_table WHERE parent_id = %d ORDER BY name ASC",
			$folder_id
		));

		// Get files
		$files = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $files_table WHERE folder_id = %d ORDER BY original_name ASC",
			$folder_id
		));

		// Get current folder info
		$current_folder = null;
		if ( $folder_id > 0 ) {
			$current_folder = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM $folders_table WHERE id = %d",
				$folder_id
			));
		}

		return array(
			'folders' => $folders,
			'files' => $files,
			'current_folder' => $current_folder,
			'breadcrumb' => $this->build_breadcrumb( $folder_id )
		);
	}

	/**
	 * Build breadcrumb navigation
	 */
	private function build_breadcrumb( $folder_id ) {
		if ( $folder_id == 0 ) {
			return array(
				array(
					'id' => 0,
					'name' => __( 'Home', 'lknwp-filebrowser' ),
					'path' => ''
				)
			);
		}

		global $wpdb;
		$folders_table = $wpdb->prefix . 'lknwp_filebrowser_folders';
		
		$breadcrumb = array();
		$current_id = $folder_id;
		$path_parts = array();

		// First, collect all folder names for the full path
		while ( $current_id > 0 ) {
			$folder = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM $folders_table WHERE id = %d",
				$current_id
			));

			if ( $folder ) {
				array_unshift( $path_parts, $folder->name );
				$current_id = $folder->parent_id;
			} else {
				break;
			}
		}

		// Reset and build breadcrumb with consistent paths
		$current_id = $folder_id;
		$partial_path = '';

		while ( $current_id > 0 ) {
			$folder = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM $folders_table WHERE id = %d",
				$current_id
			));

			if ( $folder ) {
				// Build the path consistently (without leading slash)
				$folder_index = array_search( $folder->name, $path_parts );
				if ( $folder_index !== false ) {
					$partial_path = implode( '/', array_slice( $path_parts, 0, $folder_index + 1 ) );
				}

				array_unshift( $breadcrumb, array(
					'id' => $folder->id,
					'name' => $folder->name,
					'path' => $partial_path
				));
				$current_id = $folder->parent_id;
			} else {
				break;
			}
		}

		// Add root
		array_unshift( $breadcrumb, array(
			'id' => 0,
			'name' => __( 'Home', 'lknwp-filebrowser' ),
			'path' => ''
		));

		return $breadcrumb;
	}

	/**
	 * Build full path for a folder
	 */
	private function build_folder_path( $folder_id ) {
		if ( $folder_id == 0 ) {
			return 'Home';
		}

		global $wpdb;
		$folders_table = $wpdb->prefix . 'lknwp_filebrowser_folders';
		
		$path_parts = array();
		$current_id = $folder_id;

		while ( $current_id != 0 ) {
			$folder = $wpdb->get_row( $wpdb->prepare(
				"SELECT id, name, parent_id FROM $folders_table WHERE id = %d",
				$current_id
			));

			if ( $folder ) {
				array_unshift( $path_parts, $folder->name );
				$current_id = $folder->parent_id;
			} else {
				break;
			}
		}

		return 'Home' . ( !empty( $path_parts ) ? ' / ' . implode( ' / ', $path_parts ) : '' );
	}

	/**
	 * AJAX handler for getting folder files (frontend tree)
	 */
	public function get_folder_files_frontend() {
		check_ajax_referer( 'lknwp_filebrowser_public_nonce', 'nonce' );
		
		$folder_id = intval( $_POST['folder_id'] );
		
		global $wpdb;
		$files_table = $wpdb->prefix . 'lknwp_filebrowser_files';
		
		// Get files from folder
		$files = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $files_table WHERE folder_id = %d ORDER BY original_name ASC",
			$folder_id
		));
		
		wp_send_json_success( $files );
	}
}
