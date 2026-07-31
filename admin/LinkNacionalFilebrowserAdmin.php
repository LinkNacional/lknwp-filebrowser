<?php

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
// Custom tables — no WP core API exists. $wpdb is the only correct approach.

namespace LinkNacional\Filebrowser\Admin;

class LinkNacionalFilebrowserAdmin {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function enqueue_styles( $hook_suffix ) {
		if ( 'toplevel_page_linknacional-filebrowser' !== $hook_suffix ) {
			return;
		}
		wp_enqueue_script( 'linknacional-filebrowser-fontawesome', LINKNACIONAL_FILEBROWSER_PLUGIN_URL . 'assets/js/compiled/fontawesome.compiled.js', array(), LINKNACIONAL_FILEBROWSER_VERSION, false );
		wp_enqueue_style( $this->plugin_name, LINKNACIONAL_FILEBROWSER_PLUGIN_URL . 'admin/css/linknacional-filebrowser-admin.css', array(), LINKNACIONAL_FILEBROWSER_VERSION, 'all' );
	}

	public function enqueue_scripts( $hook_suffix ) {
		if ( 'toplevel_page_linknacional-filebrowser' !== $hook_suffix ) {
			return;
		}
		wp_enqueue_script( $this->plugin_name, LINKNACIONAL_FILEBROWSER_PLUGIN_URL . 'admin/js/linknacional-filebrowser-admin.js', array( 'jquery' ), LINKNACIONAL_FILEBROWSER_VERSION, false );
		wp_localize_script( $this->plugin_name, 'linknacional_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'copied_text' => esc_html__( 'Copied!', 'linknacional-file-browser' ),
			'loading_text' => esc_html__( 'Loading...', 'linknacional-file-browser' ),
			'uploading_text' => esc_html__( 'Uploading files...', 'linknacional-file-browser' ),
			'error_loading_text' => esc_html__( 'Error loading contents', 'linknacional-file-browser' ),
			'empty_folder_text' => esc_html__( 'This folder is empty', 'linknacional-file-browser' ),
			'folder_text' => esc_html__( 'Folder', 'linknacional-file-browser' ),
			'saved_text' => esc_html__( 'Saved', 'linknacional-file-browser' ),
			'name_empty_error' => esc_html__( 'Name cannot be empty', 'linknacional-file-browser' ),
			'update_error' => esc_html__( 'Error updating name', 'linknacional-file-browser' ),
			'unknown_error' => esc_html__( 'Unknown error', 'linknacional-file-browser' ),
			'create_folder_error' => esc_html__( 'Error creating folder', 'linknacional-file-browser' ),
			'upload_error' => esc_html__( 'Error uploading files', 'linknacional-file-browser' ),
			'hide_subfolders' => esc_html__( 'Hide Subfolders', 'linknacional-file-browser' ),
			'show_subfolders' => esc_html__( 'Show Subfolders', 'linknacional-file-browser' ),
			'expand_collapse' => esc_html__( 'Expand/Collapse', 'linknacional-file-browser' ),
			'collapse' => esc_html__( 'Collapse', 'linknacional-file-browser' ),
			'confirm_edit' => esc_html__( 'Confirm', 'linknacional-file-browser' ),
			'cancel' => esc_html__( 'Cancel', 'linknacional-file-browser' ),
			'edit_name' => esc_html__( 'Edit name', 'linknacional-file-browser' ),
			'delete_folder' => esc_html__( 'Delete folder', 'linknacional-file-browser' ),
			'delete_file' => esc_html__( 'Delete file', 'linknacional-file-browser' ),
			'open_file' => esc_html__( 'Open file', 'linknacional-file-browser' ),
			'migrating_text' => esc_html__( 'Migrating...', 'linknacional-file-browser' ),
			'migrate_error' => esc_html__( 'Migration failed. Please try again.', 'linknacional-file-browser' ),
		));
	}

	public function add_admin_menu() {
		add_menu_page(
			esc_html__('File Browser', 'linknacional-file-browser'),
			esc_html__('File Browser', 'linknacional-file-browser'),
			'manage_options',
			'linknacional-filebrowser',
			array($this, 'admin_page'),
			'dashicons-portfolio',
			30
		);
	}

	public function admin_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e('File Browser Manager', 'linknacional-file-browser'); ?></h1>

			<div class="linknacional-instructions-panel">
				<div class="linknacional-instructions-header">
					<h2><i class="fas fa-info-circle"></i> <?php esc_html_e('How to Use', 'linknacional-file-browser'); ?></h2>
				</div>
				<div class="linknacional-instructions-content">
					<p><?php esc_html_e('To display the file browser on your website, follow these simple steps:', 'linknacional-file-browser'); ?></p>
					<ol style="margin: 15px 0; padding-left: 20px;">
						<li><?php esc_html_e('Copy the shortcode below', 'linknacional-file-browser'); ?></li>
						<li><?php esc_html_e('Go to the page or post where you want to display the file browser', 'linknacional-file-browser'); ?></li>
						<li><?php esc_html_e('Add a shortcode component/element in your editor', 'linknacional-file-browser'); ?></li>
						<li><?php esc_html_e('Paste the shortcode into the component', 'linknacional-file-browser'); ?></li>
						<li><?php esc_html_e('Save and publish your page', 'linknacional-file-browser'); ?></li>
					</ol>
					<div class="linknacional-shortcode-box">
						<code>[linkn_filebrowser]</code>
						<button type="button" class="button button-primary copy-shortcode" data-shortcode="[linkn_filebrowser]">
							<i class="fas fa-copy"></i> <?php esc_html_e('Copy Shortcode', 'linknacional-file-browser'); ?>
						</button>
					</div>
					<p style="margin-top: 15px; font-size: 13px; color: #666;">
						<?php esc_html_e('The file browser will display all folders and files you create here. Users can navigate through folders, search for files, and download them directly from the frontend.', 'linknacional-file-browser'); ?>
					</p>
				</div>
			</div>

			<?php if ( $this->old_tables_exist() ) : ?>
			<div id="linknacional-migration-banner" style="background: #fff; border-left: 4px solid #2271b1; padding: 16px 20px; margin: 20px 0; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: space-between;">
				<div>
					<strong><?php esc_html_e( 'Data migration required', 'linknacional-file-browser' ); ?></strong>
					<p style="margin: 4px 0 0; color: #555;">
						<?php esc_html_e( 'We detected data from a previous version. Migrate your folders, files, and uploads to the new format.', 'linknacional-file-browser' ); ?>
					</p>
				</div>
				<div style="display: flex; gap: 8px; align-items: center;">
					<span id="linknacional-migration-status" style="display: none; color: #2271b1; font-weight: 500;"></span>
					<button type="button" id="linknacional-migrate-btn" class="button button-primary">
						<i class="fas fa-sync-alt"></i> <?php esc_html_e( 'Migrate Now', 'linknacional-file-browser' ); ?>
					</button>
				</div>
			</div>
			<?php endif; ?>

			<div id="linknacional-filebrowser-admin">
				<div class="linknacional-toolbar">
					<button type="button" class="button button-primary" id="create-folder-btn">
						<i class="fas fa-folder-plus"></i> <?php esc_html_e('Create Folder', 'linknacional-file-browser'); ?>
					</button>
					<button type="button" class="button button-secondary" id="upload-file-btn">
						<i class="fas fa-upload"></i> <?php esc_html_e('Upload Files', 'linknacional-file-browser'); ?>
					</button>
					<input type="file" id="file-upload-input" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif" style="display: none;">
				</div>

				<div class="linknacional-breadcrumb">
					<span id="current-path"><?php esc_html_e('Home', 'linknacional-file-browser'); ?></span>
				</div>

				<div class="linknacional-file-manager">
					<div class="linknacional-sidebar">
						<h3><?php esc_html_e('Folders', 'linknacional-file-browser'); ?></h3>
						<div id="folder-tree"></div>
					</div>

					<div class="linknacional-content">
						<div id="folder-contents"></div>
					</div>
				</div>
			</div>
		</div>

		<div id="create-folder-modal" class="linknacional-modal" style="display: none;">
			<div class="linknacional-modal-content">
				<span class="linknacional-close">&times;</span>
				<h2><?php esc_html_e('Create New Folder', 'linknacional-file-browser'); ?></h2>
				<form id="create-folder-form">
					<label for="folder-name"><?php esc_html_e('Folder Name:', 'linknacional-file-browser'); ?></label>
					<input type="text" id="folder-name" name="folder_name" required>
					<input type="hidden" id="parent-folder-id" name="parent_id" value="0">
					<div class="form-actions">
						<button type="submit" class="button button-primary"><?php esc_html_e('Create', 'linknacional-file-browser'); ?></button>
						<button type="button" class="button" onclick="linknacionalCloseModal('create-folder-modal')"><?php esc_html_e('Cancel', 'linknacional-file-browser'); ?></button>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	public function linknacional_get_admin_nonce() {
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

	public function create_folder_ajax() {
		check_ajax_referer('linknacional_filebrowser_nonce', 'nonce');
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Insufficient permissions', 'linknacional-file-browser'));
		}
		$folder_name = isset( $_POST['folder_name'] ) ? sanitize_text_field( wp_unslash( $_POST['folder_name'] ) ) : '';
		$parent_id = isset( $_POST['parent_id'] ) ? intval( wp_unslash( $_POST['parent_id'] ) ) : 0;
		if (empty($folder_name)) {
			wp_send_json_error(esc_html__('Folder name is required', 'linknacional-file-browser'));
		}
		global $wpdb;
		$path = $this->build_folder_path($parent_id) . '/' . $folder_name;
		$result = $wpdb->insert(
			$this->table_folders(),
			array('name' => $folder_name, 'parent_id' => $parent_id, 'path' => $path),
			array('%s', '%d', '%s')
		);
		if ($result === false) {
			wp_send_json_error(esc_html__('Failed to create folder', 'linknacional-file-browser'));
		}
		wp_send_json_success(array(
			'message' => esc_html__('Folder created successfully', 'linknacional-file-browser'),
			'folder_id' => $wpdb->insert_id
		));
	}

	public function upload_file_ajax() {
		check_ajax_referer('linknacional_filebrowser_nonce', 'nonce');
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Insufficient permissions', 'linknacional-file-browser'));
		}
		$folder_id = isset( $_POST['folder_id'] ) ? intval( wp_unslash( $_POST['folder_id'] ) ) : 0;
		if (empty($_FILES['files'])) {
			wp_send_json_error(esc_html__('No files uploaded', 'linknacional-file-browser'));
		}
		$upload_dir = wp_upload_dir();
		$filebrowser_dir = $upload_dir['basedir'] . '/linknacional-filebrowser';
		$filebrowser_url = $upload_dir['baseurl'] . '/linknacional-filebrowser';

		if ( ! file_exists( $filebrowser_dir ) ) {
			wp_mkdir_p( $filebrowser_dir );
		}

		$filter_upload_dir = function ( $dirs ) use ( $filebrowser_dir, $filebrowser_url ) {
			return array(
				'path'    => $filebrowser_dir,
				'url'     => $filebrowser_url,
				'subdir'  => '',
				'basedir' => $filebrowser_dir,
				'baseurl' => $filebrowser_url,
				'error'   => false,
			);
		};
		add_filter( 'upload_dir', $filter_upload_dir );

		$overrides = array(
			'test_form' => false,
			'mimes'     => array(
				'pdf'                       => 'application/pdf',
				'doc'                       => 'application/msword',
				'docx'                      => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'xls'                       => 'application/vnd.ms-excel',
				'xlsx'                      => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'ppt'                       => 'application/vnd.ms-powerpoint',
				'pptx'                      => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
				'txt'                       => 'text/plain',
				'jpg|jpeg|jpe'              => 'image/jpeg',
				'png'                       => 'image/png',
				'gif'                       => 'image/gif',
			),
		);

		$uploaded_files = array();
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$files = $_FILES['files'];
		for ( $i = 0; $i < count( $files['name'] ); $i++ ) {
			if ( UPLOAD_ERR_OK !== $files['error'][$i] ) {
				continue;
			}

			$original_name = sanitize_file_name( $files['name'][$i] );
			$file_size     = $files['size'][$i];

			$single_file = array(
				'name'     => $files['name'][$i],
				'type'     => $files['type'][$i],
				'tmp_name' => $files['tmp_name'][$i],
				'error'    => $files['error'][$i],
				'size'     => $files['size'][$i],
			);

			$movefile = wp_handle_upload( $single_file, $overrides );

			if ( isset( $movefile['error'] ) ) {
				continue;
			}

			$filename  = basename( $movefile['file'] );
			$file_path = $movefile['file'];
			$file_url  = $movefile['url'];
			$file_ext  = pathinfo( $filename, PATHINFO_EXTENSION );

			global $wpdb;
			$result = $wpdb->insert(
				$this->table_files(),
				array(
					'name'          => $filename,
					'original_name' => $original_name,
					'folder_id'     => $folder_id,
					'file_type'     => $file_ext,
					'file_size'     => $file_size,
					'file_path'     => $file_path,
					'file_url'      => $file_url,
				),
				array( '%s', '%s', '%d', '%s', '%d', '%s', '%s' )
			);

			if ( false !== $result ) {
				$uploaded_files[] = array(
					'id'   => $wpdb->insert_id,
					'name' => $original_name,
					'size' => $file_size,
					'type' => $file_ext,
				);
			}
		}

		remove_filter( 'upload_dir', $filter_upload_dir );
		if (empty($uploaded_files)) {
			wp_send_json_error(esc_html__('Failed to upload files', 'linknacional-file-browser'));
		}
		wp_send_json_success(array('message' => esc_html__('Files uploaded successfully', 'linknacional-file-browser'), 'files' => $uploaded_files));
	}

	public function delete_folder_ajax() {
		check_ajax_referer('linknacional_filebrowser_nonce', 'nonce');
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Insufficient permissions', 'linknacional-file-browser'));
		}
		$folder_id = isset( $_POST['folder_id'] ) ? intval( wp_unslash( $_POST['folder_id'] ) ) : 0;
		global $wpdb;
		$this->delete_folder_recursive($folder_id);
		wp_send_json_success(esc_html__('Folder deleted successfully', 'linknacional-file-browser'));
	}

	public function delete_file_ajax() {
		check_ajax_referer('linknacional_filebrowser_nonce', 'nonce');
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Insufficient permissions', 'linknacional-file-browser'));
		}
		$file_id = isset( $_POST['file_id'] ) ? intval( wp_unslash( $_POST['file_id'] ) ) : 0;
		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$file = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_files()} WHERE id = %d", $file_id));
		if ($file) {
			if ( file_exists( $file->file_path ) ) {
				wp_delete_file( $file->file_path );
			}
			$wpdb->delete($this->table_files(), array('id' => $file_id), array('%d'));
		}
		wp_send_json_success(esc_html__('File deleted successfully', 'linknacional-file-browser'));
	}

	public function get_folder_contents_ajax() {
		check_ajax_referer('linknacional_filebrowser_nonce', 'nonce');
		$folder_id = isset( $_POST['folder_id'] ) ? intval( wp_unslash( $_POST['folder_id'] ) ) : 0;
		$contents = $this->get_folder_contents($folder_id);
		wp_send_json_success($contents);
	}

	public function get_all_folders_ajax() {
		check_ajax_referer('linknacional_filebrowser_nonce', 'nonce');
		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$folders = $wpdb->get_results("SELECT * FROM {$this->table_folders()} ORDER BY parent_id ASC, name ASC");
		wp_send_json_success($folders);
	}

	private function get_folder_contents($folder_id) {
		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$folders = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->table_folders()} WHERE parent_id = %d ORDER BY name ASC", $folder_id));
		$files = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->table_files()} WHERE folder_id = %d ORDER BY original_name ASC", $folder_id));
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return array('folders' => $folders, 'files' => $files);
	}

	private function build_folder_path($folder_id) {
		if ($folder_id == 0) return '';
		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$folder = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_folders()} WHERE id = %d", $folder_id));
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ($folder && $folder->parent_id > 0) {
			return $this->build_folder_path($folder->parent_id) . '/' . $folder->name;
		} elseif ($folder) {
			return $folder->name;
		}
		return '';
	}

	private function delete_folder_recursive($folder_id) {
		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$subfolders = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$this->table_folders()} WHERE parent_id = %d", $folder_id));
		foreach ($subfolders as $subfolder) {
			$this->delete_folder_recursive($subfolder->id);
		}
		$files = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->table_files()} WHERE folder_id = %d", $folder_id));
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		foreach ($files as $file) {
			if ( file_exists( $file->file_path ) ) {
				wp_delete_file( $file->file_path );
			}
		}
		$wpdb->delete($this->table_files(), array('folder_id' => $folder_id), array('%d'));
		$wpdb->delete($this->table_folders(), array('id' => $folder_id), array('%d'));
	}

	public function update_folder_name_ajax() {
		check_ajax_referer('linknacional_filebrowser_nonce', 'nonce');
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Insufficient permissions', 'linknacional-file-browser'));
		}
		$folder_id = isset( $_POST['id'] ) ? intval( wp_unslash( $_POST['id'] ) ) : 0;
		$new_name = isset( $_POST['new_name'] ) ? sanitize_text_field( wp_unslash( $_POST['new_name'] ) ) : '';
		if (empty($new_name)) {
			wp_send_json_error(esc_html__('Folder name cannot be empty', 'linknacional-file-browser'));
		}
		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$folder = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_folders()} WHERE id = %d", $folder_id));
		if (!$folder) {
			wp_send_json_error(esc_html__('Folder not found', 'linknacional-file-browser'));
		}
		$existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->table_folders()} WHERE name = %s AND parent_id = %d AND id != %d", $new_name, $folder->parent_id, $folder_id));
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ($existing) {
			wp_send_json_error(esc_html__('A folder with this name already exists', 'linknacional-file-browser'));
		}
		$result = $wpdb->update($this->table_folders(), array('name' => $new_name), array('id' => $folder_id), array('%s'), array('%d'));
		if ($result !== false) {
			wp_send_json_success(esc_html__('Folder name updated successfully', 'linknacional-file-browser'));
		} else {
			wp_send_json_error(esc_html__('Error updating folder name', 'linknacional-file-browser'));
		}
	}

	public function update_file_name_ajax() {
		check_ajax_referer('linknacional_filebrowser_nonce', 'nonce');
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Insufficient permissions', 'linknacional-file-browser'));
		}
		$file_id = isset( $_POST['id'] ) ? intval( wp_unslash( $_POST['id'] ) ) : 0;
		$new_name = isset( $_POST['new_name'] ) ? sanitize_file_name( wp_unslash( $_POST['new_name'] ) ) : '';
		if (empty($new_name)) {
			wp_send_json_error(esc_html__('File name cannot be empty', 'linknacional-file-browser'));
		}
		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$file = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_files()} WHERE id = %d", $file_id));
		if (!$file) {
			wp_send_json_error(esc_html__('File not found', 'linknacional-file-browser'));
		}
		$existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->table_files()} WHERE original_name = %s AND folder_id = %d AND id != %d", $new_name, $file->folder_id, $file_id));
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ($existing) {
			wp_send_json_error(esc_html__('A file with this name already exists', 'linknacional-file-browser'));
		}
		$file_info = \pathinfo($new_name);
		$old_file_info = \pathinfo($file->original_name);
		if (isset($file_info['extension']) && isset($old_file_info['extension'])) {
			if (\strtolower($file_info['extension']) !== \strtolower($old_file_info['extension'])) {
				wp_send_json_error(esc_html__('Cannot change the file extension', 'linknacional-file-browser'));
			}
		}
		$result = $wpdb->update($this->table_files(), array('original_name' => $new_name), array('id' => $file_id), array('%s'), array('%d'));
		if ($result !== false) {
			wp_send_json_success(esc_html__('File name updated successfully', 'linknacional-file-browser'));
		} else {
			wp_send_json_error(esc_html__('Error updating file name', 'linknacional-file-browser'));
		}
	}

	public function get_folder_files_ajax() {
		check_ajax_referer( 'linknacional_filebrowser_nonce', 'nonce' );
		$folder_id = isset( $_POST['folder_id'] ) ? intval( wp_unslash( $_POST['folder_id'] ) ) : 0;
		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$files = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$this->table_files()} WHERE folder_id = %d ORDER BY original_name ASC", $folder_id));
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		wp_send_json_success( $files );
	}

	public function get_all_folders_admin_frontend() {
		check_ajax_referer( 'linknacional_filebrowser_nonce', 'nonce' );
		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$folders = $wpdb->get_results("SELECT * FROM {$this->table_folders()} ORDER BY parent_id ASC, name ASC");
		$files = $wpdb->get_results("SELECT * FROM {$this->table_files()} ORDER BY folder_id ASC, original_name ASC");
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		wp_send_json_success(array('folders' => $folders, 'files' => $files));
	}

	private function table_folders() {
		global $wpdb;
		return $wpdb->prefix . 'linknacional_filebrowser_folders';
	}

	private function table_files() {
		global $wpdb;
		return $wpdb->prefix . 'linknacional_filebrowser_files';
	}

	private function old_table_folders() {
		global $wpdb;
		return $wpdb->prefix . 'lknwp_filebrowser_folders';
	}

	private function old_table_files() {
		global $wpdb;
		return $wpdb->prefix . 'lknwp_filebrowser_files';
	}

	public function old_tables_exist() {
		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$folders_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $this->old_table_folders() ) ) === $this->old_table_folders();
		$files_exists   = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $this->old_table_files() ) ) === $this->old_table_files();
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $folders_exists || $files_exists;
	}

	public function migrate_ajax() {
		check_ajax_referer( 'linknacional_filebrowser_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Insufficient permissions', 'linknacional-file-browser' ) );
		}

		global $wpdb;

		$upload_dir       = wp_upload_dir();
		$old_dir          = $upload_dir['basedir'] . '/lknwp-filebrowser';
		$new_dir          = $upload_dir['basedir'] . '/linknacional-filebrowser';
		$old_url          = $upload_dir['baseurl'] . '/lknwp-filebrowser';
		$new_url          = $upload_dir['baseurl'] . '/linknacional-filebrowser';

		if ( ! file_exists( $new_dir ) ) {
			wp_mkdir_p( $new_dir );
		}

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,PluginCheck.Security.DirectDB.UnescapedDBParameter

		// --- Migrate folders ---
		$old_folders = $wpdb->get_results( "SELECT * FROM {$this->old_table_folders()} ORDER BY id ASC" );
		$id_map      = array(); // old_id → new_id
		$migrated_folders = 0;

		if ( is_array( $old_folders ) ) {
			foreach ( $old_folders as $folder ) {
				$new_parent_id = isset( $id_map[ $folder->parent_id ] ) ? $id_map[ $folder->parent_id ] : 0;
				$folder_name   = $folder->name;

				// Resolve name conflicts
				$unique_name = $folder_name;
				$suffix_num  = 1;
				while ( $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(*) FROM {$this->table_folders()} WHERE name = %s AND parent_id = %d",
					$unique_name, $new_parent_id
				) ) > 0 ) {
					$unique_name = $folder_name . ' (copy)';
					if ( $suffix_num > 1 ) {
						$unique_name = $folder_name . ' (copy)' . $suffix_num;
					}
					++$suffix_num;
				}

				$new_path = '/' . $unique_name;
				if ( $new_parent_id > 0 ) {
					$parent_path = $wpdb->get_var( $wpdb->prepare(
						"SELECT path FROM {$this->table_folders()} WHERE id = %d", $new_parent_id
					) );
					$new_path = $parent_path . '/' . $unique_name;
				}

				$inserted = $wpdb->insert(
					$this->table_folders(),
					array(
						'name'      => $unique_name,
						'parent_id' => $new_parent_id,
						'path'      => $new_path,
					),
					array( '%s', '%d', '%s' )
				);

				if ( false !== $inserted ) {
					$id_map[ $folder->id ] = $wpdb->insert_id;
					++$migrated_folders;
				}
			}
		}

		// --- Migrate files ---
		$old_files       = $wpdb->get_results( "SELECT * FROM {$this->old_table_files()} ORDER BY id ASC" );
		$migrated_files  = 0;

		if ( is_array( $old_files ) ) {
			foreach ( $old_files as $file ) {
				$new_folder_id = isset( $id_map[ $file->folder_id ] ) ? $id_map[ $file->folder_id ] : 0;
				$original_name = $file->original_name;

				// Resolve name conflicts
				$unique_name = $original_name;
				$suffix_num  = 1;
				while ( $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(*) FROM {$this->table_files()} WHERE original_name = %s AND folder_id = %d",
					$unique_name, $new_folder_id
				) ) > 0 ) {
					$ext         = pathinfo( $original_name, PATHINFO_EXTENSION );
					$base        = $ext ? substr( $original_name, 0, - ( strlen( $ext ) + 1 ) ) : $original_name;
					$unique_name = $base . ' (copy).' . $ext;
					if ( $suffix_num > 1 ) {
						$unique_name = $base . ' (copy)' . $suffix_num . '.' . $ext;
					}
					++$suffix_num;
				}

				$new_file_path = $new_dir . '/' . $unique_name;
				$new_file_url  = $new_url . '/' . $unique_name;

				// Copy physical file
				$old_file_path = $old_dir . '/' . $file->name;
				if ( file_exists( $old_file_path ) && ! file_exists( $new_file_path ) ) {
					copy( $old_file_path, $new_file_path );
				}

				$inserted = $wpdb->insert(
					$this->table_files(),
					array(
						'name'          => $unique_name,
						'original_name' => $unique_name,
						'folder_id'     => $new_folder_id,
						'file_type'     => $file->file_type,
						'file_size'     => $file->file_size,
						'file_path'     => $new_file_path,
						'file_url'      => $new_file_url,
					),
					array( '%s', '%s', '%d', '%s', '%d', '%s', '%s' )
				);

				if ( false !== $inserted ) {
					++$migrated_files;
				}
			}
		}

		// --- Drop old tables ---
		$wpdb->query( "DROP TABLE IF EXISTS {$this->old_table_folders()}" );
		$wpdb->query( "DROP TABLE IF EXISTS {$this->old_table_files()}" );

		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,PluginCheck.Security.DirectDB.UnescapedDBParameter

		wp_send_json_success( array(
			'message'          => sprintf(
				/* translators: 1: folders count, 2: files count */
				esc_html__( 'Migration complete: %1$d folders and %2$d files migrated.', 'linknacional-file-browser' ),
				$migrated_folders,
				$migrated_files
			),
			'folders_migrated' => $migrated_folders,
			'files_migrated'   => $migrated_files,
		) );
	}
}
