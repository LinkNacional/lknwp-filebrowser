<?php

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
// Custom tables — no WP core API exists. $wpdb is the only correct approach.

namespace LinkNacional\Filebrowser\Public;

class LinkNacionalFilebrowserPublic {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function lkn_get_public_nonce() {
		if ( ! wp_doing_ajax() ) {
			wp_die( esc_html__( 'Invalid request method.', 'linknacional-file-browser' ) );
		}
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$action_name = isset( $_POST['action_name'] ) ? sanitize_text_field( wp_unslash( $_POST['action_name'] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		if ( ! $action_name ) {
			wp_send_json_error( esc_html__( 'Action name required', 'linknacional-file-browser' ) );
		}
		$nonce = wp_create_nonce( $action_name );
		wp_send_json_success( array( 'nonce' => $nonce ) );
	}

	public function register_shortcode() {
		add_shortcode( 'lkn_filebrowser', array( $this, 'render_filebrowser_shortcode' ) );
	}

	public function render_filebrowser_shortcode( $atts ) {
		wp_enqueue_script( 'lkn-filebrowser-fontawesome', LKN_FILEBROWSER_PLUGIN_URL . 'assets/js/compiled/fontawesome.compiled.js', array(), LKN_FILEBROWSER_VERSION, false );
		wp_enqueue_style( $this->plugin_name, LKN_FILEBROWSER_PLUGIN_URL . 'public/css/linknacional-filebrowser-public.css', array(), LKN_FILEBROWSER_VERSION, 'all' );
		wp_enqueue_script( $this->plugin_name, LKN_FILEBROWSER_PLUGIN_URL . 'public/js/linknacional-filebrowser-public.js', array( 'jquery' ), LKN_FILEBROWSER_VERSION, false );

		wp_localize_script( $this->plugin_name, 'lkn_public_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'loading_text' => __( 'Loading...', 'linknacional-file-browser' ),
			'searching_text' => __( 'Searching...', 'linknacional-file-browser' ),
			'error_loading_text' => __( 'Error loading contents', 'linknacional-file-browser' ),
			'error_search_text' => __( 'Error performing search', 'linknacional-file-browser' ),
			'error_folders_text' => __( 'Error loading folders', 'linknacional-file-browser' ),
			'empty_folder_text' => __( 'This folder is empty', 'linknacional-file-browser' ),
			'empty_folder_desc' => __( 'No files or folders found in this location.', 'linknacional-file-browser' ),
			'no_results_text' => __( 'No results found', 'linknacional-file-browser' ),
			'no_results_desc' => __( 'Try adjusting your search terms.', 'linknacional-file-browser' ),
			'folder_text' => __( 'Folder', 'linknacional-file-browser' ),
			'download_text' => __( 'DOWNLOAD', 'linknacional-file-browser' ),
			'search_results_text' => __( 'Search results for', 'linknacional-file-browser' ),
			'found_items_text' => __( 'Found', 'linknacional-file-browser' ),
			'items_text' => __( 'items', 'linknacional-file-browser' ),
			'hide_subfolders' => __( 'Hide Subfolders', 'linknacional-file-browser' ),
			'show_subfolders' => __( 'Show Subfolders', 'linknacional-file-browser' ),
			'open_file' => __( 'Open file', 'linknacional-file-browser' ),
		));

		$atts = shortcode_atts( array(
			'folder_id' => 0,
			'show_search' => 'true',
			'show_breadcrumb' => 'true',
			'show_folder_tree' => 'true',
			'layout' => 'grid',
		), $atts );

		$folder_id = intval( $atts['folder_id'] );
		$show_search = $atts['show_search'] === 'true';
		$show_breadcrumb = $atts['show_breadcrumb'] === 'true';
		$show_folder_tree = $atts['show_folder_tree'] === 'true';
		$layout = in_array( $atts['layout'], array( 'grid', 'list' ) ) ? $atts['layout'] : 'grid';

		ob_start();
		?>
		<div class="lkn-filebrowser-public" data-folder-id="<?php echo esc_attr( $folder_id ); ?>" data-layout="<?php echo esc_attr( $layout ); ?>">

			<?php if ( $show_search ): ?>
			<div class="lkn-search-container">
				<div class="lkn-search-box">
					<input type="text" id="lkn-search-input" placeholder="<?php esc_attr_e( 'Search files and folders...', 'linknacional-file-browser' ); ?>">
					<button type="button" id="lkn-search-btn"><i class="fas fa-search"></i></button>
					<button type="button" id="lkn-clear-search" style="display: none;"><i class="fas fa-times"></i></button>
				</div>
			</div>
			<?php endif; ?>

			<div class="lkn-file-manager-public">
				<?php if ( $show_folder_tree ): ?>
				<div class="lkn-sidebar-public">
					<h4><?php esc_html_e( 'Folders', 'linknacional-file-browser' ); ?></h4>
					<div id="lkn-folder-tree-public">
						<div class="loading"><i class="fas fa-spinner"></i> <?php esc_html_e( 'Loading folders...', 'linknacional-file-browser' ); ?></div>
					</div>
				</div>
				<?php endif; ?>

				<div class="lkn-content-public <?php echo !$show_folder_tree ? 'full-width' : ''; ?>">
					<?php if ( $show_breadcrumb ): ?>
					<div class="lkn-breadcrumb-public">
						<span id="lkn-current-path"><?php esc_html_e( 'Home', 'linknacional-file-browser' ); ?></span>
					</div>
					<?php endif; ?>

					<div class="lkn-layout-controls">
						<button type="button" class="layout-btn <?php echo $layout === 'grid' ? 'active' : ''; ?>" data-layout="grid"><i class="fas fa-th"></i></button>
						<button type="button" class="layout-btn <?php echo $layout === 'list' ? 'active' : ''; ?>" data-layout="list"><i class="fas fa-list"></i></button>
					</div>

					<div class="lkn-filebrowser-content <?php echo esc_attr( $layout ); ?>">
						<div id="lkn-loading" class="loading">
							<i class="fas fa-spinner"></i> <?php esc_html_e( 'Loading...', 'linknacional-file-browser' ); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public function get_all_folders_frontend() {
		check_ajax_referer( 'lkn_filebrowser_public_nonce', 'nonce' );
		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$folders = $wpdb->get_results("SELECT * FROM {$this->table_folders()} ORDER BY parent_id ASC, name ASC");
		$files = $wpdb->get_results("SELECT * FROM {$this->table_files()} ORDER BY folder_id ASC, original_name ASC");
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		wp_send_json_success( array( 'folders' => $folders, 'files' => $files ) );
	}

	public function get_folder_contents_frontend() {
		check_ajax_referer( 'lkn_filebrowser_public_nonce', 'nonce' );
		$folder_id = isset( $_POST['folder_id'] ) ? intval( wp_unslash( $_POST['folder_id'] ) ) : 0;
		$contents = $this->get_folder_contents( $folder_id );
		wp_send_json_success( $contents );
	}

	public function search_files_frontend() {
		check_ajax_referer( 'lkn_filebrowser_public_nonce', 'nonce' );
		$search_term = isset( $_POST['search_term'] ) ? sanitize_text_field( wp_unslash( $_POST['search_term'] ) ) : '';
		$folder_id = isset( $_POST['folder_id'] ) ? intval( wp_unslash( $_POST['folder_id'] ) ) : 0;
		if ( empty( $search_term ) ) {
			wp_send_json_error( __( 'Search term is required', 'linknacional-file-browser' ) );
		}
		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$folders = $wpdb->get_results( $wpdb->prepare(
			"SELECT f.*, p.name as parent_name, p.path as parent_path FROM {$this->table_folders()} f LEFT JOIN {$this->table_folders()} p ON f.parent_id = p.id WHERE f.name LIKE %s ORDER BY f.name ASC",
			'%' . $wpdb->esc_like( $search_term ) . '%'
		));
		foreach ($folders as $folder) {
			$folder->full_path = $this->build_folder_path( $folder->id );
		}
		$files = $wpdb->get_results( $wpdb->prepare(
			"SELECT f.*, folder.name as folder_name, folder.path as folder_path FROM {$this->table_files()} f LEFT JOIN {$this->table_folders()} folder ON f.folder_id = folder.id WHERE f.original_name LIKE %s ORDER BY f.original_name ASC",
			'%' . $wpdb->esc_like( $search_term ) . '%'
		));
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		wp_send_json_success( array( 'folders' => $folders, 'files' => $files, 'search_term' => $search_term ) );
	}

	private function get_folder_contents( $folder_id ) {
		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$folders = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$this->table_folders()} WHERE parent_id = %d ORDER BY name ASC", $folder_id));
		$files = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$this->table_files()} WHERE folder_id = %d ORDER BY original_name ASC", $folder_id));
		$current_folder = null;
		if ( $folder_id > 0 ) {
			$current_folder = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$this->table_folders()} WHERE id = %d", $folder_id));
		}
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return array('folders' => $folders, 'files' => $files, 'current_folder' => $current_folder, 'breadcrumb' => $this->build_breadcrumb( $folder_id ));
	}

	private function build_breadcrumb( $folder_id ) {
		if ( $folder_id == 0 ) {
			return array( array( 'id' => 0, 'name' => __( 'Home', 'linknacional-file-browser' ), 'path' => '' ) );
		}
		global $wpdb;
		$breadcrumb = array();
		$current_id = $folder_id;
		$path_parts = array();
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		while ( $current_id > 0 ) {
			$folder = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$this->table_folders()} WHERE id = %d", $current_id));
			if ( $folder ) { array_unshift( $path_parts, $folder->name ); $current_id = $folder->parent_id; } else { break; }
		}
		$current_id = $folder_id;
		$partial_path = '';
		while ( $current_id > 0 ) {
			$folder = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$this->table_folders()} WHERE id = %d", $current_id));
			if ( $folder ) {
				$folder_index = array_search( $folder->name, $path_parts );
				if ( $folder_index !== false ) { $partial_path = implode( '/', array_slice( $path_parts, 0, $folder_index + 1 ) ); }
				array_unshift( $breadcrumb, array( 'id' => $folder->id, 'name' => $folder->name, 'path' => $partial_path ));
				$current_id = $folder->parent_id;
			} else { break; }
		}
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		array_unshift( $breadcrumb, array( 'id' => 0, 'name' => __( 'Home', 'linknacional-file-browser' ), 'path' => '' ) );
		return $breadcrumb;
	}

	private function build_folder_path( $folder_id ) {
		if ( $folder_id == 0 ) return 'Home';
		global $wpdb;
		$path_parts = array();
		$current_id = $folder_id;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		while ( $current_id != 0 ) {
			$folder = $wpdb->get_row( $wpdb->prepare("SELECT id, name, parent_id FROM {$this->table_folders()} WHERE id = %d", $current_id));
			if ( $folder ) { array_unshift( $path_parts, $folder->name ); $current_id = $folder->parent_id; } else { break; }
		}
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return 'Home' . ( !empty( $path_parts ) ? ' / ' . implode( ' / ', $path_parts ) : '' );
	}

	public function get_folder_files_frontend() {
		check_ajax_referer( 'lkn_filebrowser_public_nonce', 'nonce' );
		$folder_id = isset( $_POST['folder_id'] ) ? intval( wp_unslash( $_POST['folder_id'] ) ) : 0;
		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$files = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$this->table_files()} WHERE folder_id = %d ORDER BY original_name ASC", $folder_id));
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		wp_send_json_success( $files );
	}

	private function table_folders() {
		global $wpdb;
		return $wpdb->prefix . 'lkn_filebrowser_folders';
	}

	private function table_files() {
		global $wpdb;
		return $wpdb->prefix . 'lkn_filebrowser_files';
	}
}
