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
		if ( 'toplevel_page_lkn-filebrowser' !== $hook_suffix ) {
			return;
		}
		wp_enqueue_script( 'lkn-filebrowser-fontawesome', LKN_FILEBROWSER_PLUGIN_URL . 'assets/js/compiled/fontawesome.compiled.js', array(), LKN_FILEBROWSER_VERSION, false );
		wp_enqueue_style( $this->plugin_name, LKN_FILEBROWSER_PLUGIN_URL . 'admin/css/linknacional-filebrowser-admin.css', array(), LKN_FILEBROWSER_VERSION, 'all' );
	}

	public function enqueue_scripts( $hook_suffix ) {
		if ( 'toplevel_page_lkn-filebrowser' !== $hook_suffix ) {
			return;
		}
		wp_enqueue_script( $this->plugin_name, LKN_FILEBROWSER_PLUGIN_URL . 'admin/js/linknacional-filebrowser-admin.js', array( 'jquery' ), LKN_FILEBROWSER_VERSION, false );
		wp_localize_script( $this->plugin_name, 'lkn_ajax', array(
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
		));
	}

	public function add_admin_menu() {
		add_menu_page(
			esc_html__('File Browser', 'linknacional-file-browser'),
			esc_html__('File Browser', 'linknacional-file-browser'),
			'manage_options',
			'lkn-filebrowser',
			array($this, 'admin_page'),
			'dashicons-portfolio',
			30
		);
	}

	public function admin_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e('File Browser Manager', 'linknacional-file-browser'); ?></h1>

			<div class="lkn-instructions-panel">
				<div class="lkn-instructions-header">
					<h2><i class="fas fa-info-circle"></i> <?php esc_html_e('How to Use', 'linknacional-file-browser'); ?></h2>
				</div>
				<div class="lkn-instructions-content">
					<p><?php esc_html_e('To display the file browser on your website, follow these simple steps:', 'linknacional-file-browser'); ?></p>
					<ol style="margin: 15px 0; padding-left: 20px;">
						<li><?php esc_html_e('Copy the shortcode below', 'linknacional-file-browser'); ?></li>
						<li><?php esc_html_e('Go to the page or post where you want to display the file browser', 'linknacional-file-browser'); ?></li>
						<li><?php esc_html_e('Add a shortcode component/element in your editor', 'linknacional-file-browser'); ?></li>
						<li><?php esc_html_e('Paste the shortcode into the component', 'linknacional-file-browser'); ?></li>
						<li><?php esc_html_e('Save and publish your page', 'linknacional-file-browser'); ?></li>
					</ol>
					<div class="lkn-shortcode-box">
						<code>[lkn_filebrowser]</code>
						<button type="button" class="button button-primary copy-shortcode" data-shortcode="[lkn_filebrowser]">
							<i class="fas fa-copy"></i> <?php esc_html_e('Copy Shortcode', 'linknacional-file-browser'); ?>
						</button>
					</div>
					<p style="margin-top: 15px; font-size: 13px; color: #666;">
						<?php esc_html_e('The file browser will display all folders and files you create here. Users can navigate through folders, search for files, and download them directly from the frontend.', 'linknacional-file-browser'); ?>
					</p>
				</div>
			</div>

			<div id="lkn-filebrowser-admin">
				<div class="lkn-toolbar">
					<button type="button" class="button button-primary" id="create-folder-btn">
						<i class="fas fa-folder-plus"></i> <?php esc_html_e('Create Folder', 'linknacional-file-browser'); ?>
					</button>
					<button type="button" class="button button-secondary" id="upload-file-btn">
						<i class="fas fa-upload"></i> <?php esc_html_e('Upload Files', 'linknacional-file-browser'); ?>
					</button>
					<input type="file" id="file-upload-input" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif" style="display: none;">
				</div>

				<div class="lkn-breadcrumb">
					<span id="current-path"><?php esc_html_e('Home', 'linknacional-file-browser'); ?></span>
				</div>

				<div class="lkn-file-manager">
					<div class="lkn-sidebar">
						<h3><?php esc_html_e('Folders', 'linknacional-file-browser'); ?></h3>
						<div id="folder-tree"></div>
					</div>

					<div class="lkn-content">
						<div id="folder-contents"></div>
					</div>
				</div>
			</div>
		</div>

		<div id="create-folder-modal" class="lkn-modal" style="display: none;">
			<div class="lkn-modal-content">
				<span class="lkn-close">&times;</span>
				<h2><?php esc_html_e('Create New Folder', 'linknacional-file-browser'); ?></h2>
				<form id="create-folder-form">
					<label for="folder-name"><?php esc_html_e('Folder Name:', 'linknacional-file-browser'); ?></label>
					<input type="text" id="folder-name" name="folder_name" required>
					<input type="hidden" id="parent-folder-id" name="parent_id" value="0">
					<div class="form-actions">
						<button type="submit" class="button button-primary"><?php esc_html_e('Create', 'linknacional-file-browser'); ?></button>
						<button type="button" class="button" onclick="closeModal('create-folder-modal')"><?php esc_html_e('Cancel', 'linknacional-file-browser'); ?></button>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	public function lkn_get_admin_nonce() {
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
		check_ajax_referer('lkn_filebrowser_nonce', 'nonce');
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
		check_ajax_referer('lkn_filebrowser_nonce', 'nonce');
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Insufficient permissions', 'linknacional-file-browser'));
		}
		$folder_id = isset( $_POST['folder_id'] ) ? intval( wp_unslash( $_POST['folder_id'] ) ) : 0;
		if (empty($_FILES['files'])) {
			wp_send_json_error(esc_html__('No files uploaded', 'linknacional-file-browser'));
		}
		$upload_dir = wp_upload_dir();
		$filebrowser_dir = $upload_dir['basedir'] . '/lkn-filebrowser';
		$filebrowser_url = $upload_dir['baseurl'] . '/lkn-filebrowser';
		$uploaded_files = array();
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$files = $_FILES['files'];
		for ($i = 0; $i < count($files['name']); $i++) {
			if ($files['error'][$i] === UPLOAD_ERR_OK) {
				$original_name = sanitize_file_name($files['name'][$i]);
				$file_size = $files['size'][$i];
				$file_type = wp_check_filetype($original_name);
				$allowed_types = array('pdf','doc','docx','xls','xlsx','ppt','pptx','txt','jpg','jpeg','png','gif');
				if ( empty( $file_type['ext'] ) || ! in_array( strtolower( $file_type['ext'] ), $allowed_types, true ) ) {
					continue;
				}
				$filename = wp_unique_filename($filebrowser_dir, $original_name);
				$file_path = $filebrowser_dir . '/' . $filename;
				$file_url = $filebrowser_url . '/' . $filename;
				// phpcs:ignore Generic.PHP.ForbiddenFunctions.Found
				if ( move_uploaded_file( $files['tmp_name'][$i], $file_path ) ) {
					global $wpdb;
					$result = $wpdb->insert(
						$this->table_files(),
						array('name' => $filename, 'original_name' => $original_name, 'folder_id' => $folder_id, 'file_type' => $file_type['ext'], 'file_size' => $file_size, 'file_path' => $file_path, 'file_url' => $file_url),
						array('%s', '%s', '%d', '%s', '%d', '%s', '%s')
					);
					if ($result !== false) {
						$uploaded_files[] = array('id' => $wpdb->insert_id, 'name' => $original_name, 'size' => $file_size, 'type' => $file_type['ext']);
					}
				}
			}
		}
		if (empty($uploaded_files)) {
			wp_send_json_error(esc_html__('Failed to upload files', 'linknacional-file-browser'));
		}
		wp_send_json_success(array('message' => esc_html__('Files uploaded successfully', 'linknacional-file-browser'), 'files' => $uploaded_files));
	}

	public function delete_folder_ajax() {
		check_ajax_referer('lkn_filebrowser_nonce', 'nonce');
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Insufficient permissions', 'linknacional-file-browser'));
		}
		$folder_id = isset( $_POST['folder_id'] ) ? intval( wp_unslash( $_POST['folder_id'] ) ) : 0;
		global $wpdb;
		$this->delete_folder_recursive($folder_id);
		wp_send_json_success(esc_html__('Folder deleted successfully', 'linknacional-file-browser'));
	}

	public function delete_file_ajax() {
		check_ajax_referer('lkn_filebrowser_nonce', 'nonce');
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
		check_ajax_referer('lkn_filebrowser_nonce', 'nonce');
		$folder_id = isset( $_POST['folder_id'] ) ? intval( wp_unslash( $_POST['folder_id'] ) ) : 0;
		$contents = $this->get_folder_contents($folder_id);
		wp_send_json_success($contents);
	}

	public function get_all_folders_ajax() {
		check_ajax_referer('lkn_filebrowser_nonce', 'nonce');
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
		check_ajax_referer('lkn_filebrowser_nonce', 'nonce');
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
		check_ajax_referer('lkn_filebrowser_nonce', 'nonce');
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
		check_ajax_referer( 'lkn_filebrowser_admin_nonce', 'nonce' );
		$folder_id = isset( $_POST['folder_id'] ) ? intval( wp_unslash( $_POST['folder_id'] ) ) : 0;
		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$files = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$this->table_files()} WHERE folder_id = %d ORDER BY original_name ASC", $folder_id));
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		wp_send_json_success( $files );
	}

	public function get_all_folders_admin_frontend() {
		check_ajax_referer( 'lkn_filebrowser_nonce', 'nonce' );
		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$folders = $wpdb->get_results("SELECT * FROM {$this->table_folders()} ORDER BY parent_id ASC, name ASC");
		$files = $wpdb->get_results("SELECT * FROM {$this->table_files()} ORDER BY folder_id ASC, original_name ASC");
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		wp_send_json_success(array('folders' => $folders, 'files' => $files));
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
