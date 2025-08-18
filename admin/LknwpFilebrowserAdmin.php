<?php

namespace Lkn\WPFilebrowser\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Lkn\WPFilebrowser\Admin
 * @author     Link Nacional <contato@linknacional.com>
 */
class LknwpFilebrowserAdmin {

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
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, LKNWP_FILEBROWSER_PLUGIN_URL . 'admin/css/lknwp-filebrowser-admin.css', array(), LKNWP_FILEBROWSER_VERSION, 'all' );
		wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, LKNWP_FILEBROWSER_PLUGIN_URL . 'admin/js/lknwp-filebrowser-admin.js', array( 'jquery' ), LKNWP_FILEBROWSER_VERSION, false );
		wp_localize_script( $this->plugin_name, 'lknwp_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'copied_text' => __( 'Copied!', 'lknwp-filebrowser' ),
			'loading_text' => __( 'Loading...', 'lknwp-filebrowser' ),
			'uploading_text' => __( 'Uploading files...', 'lknwp-filebrowser' ),
			'error_loading_text' => __( 'Error loading contents', 'lknwp-filebrowser' ),
			'empty_folder_text' => __( 'This folder is empty', 'lknwp-filebrowser' ),
			'folder_text' => __( 'Folder', 'lknwp-filebrowser' ),
			'saved_text' => __( 'Saved', 'lknwp-filebrowser' ),
			'name_empty_error' => __( 'Name cannot be empty', 'lknwp-filebrowser' ),
			'update_error' => __( 'Error updating name', 'lknwp-filebrowser' ),
			'unknown_error' => __( 'Unknown error', 'lknwp-filebrowser' ),
			'create_folder_error' => __( 'Error creating folder', 'lknwp-filebrowser' ),
			'upload_error' => __( 'Error uploading files', 'lknwp-filebrowser' )
		));
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_menu_page(
			__('File Browser', 'lknwp-filebrowser'),
			__('File Browser', 'lknwp-filebrowser'),
			'manage_options',
			'lknwp-filebrowser',
			array($this, 'admin_page'),
			'dashicons-portfolio',
			30
		);
	}

	/**
	 * Admin page content
	 */
	public function admin_page() {
		?>
		<div class="wrap">
			<h1><?php _e('File Browser Manager', 'lknwp-filebrowser'); ?></h1>
			
			<!-- Shortcode Instructions -->
			<div class="lknwp-instructions-panel">
				<div class="lknwp-instructions-header">
					<h2><i class="fas fa-info-circle"></i> <?php _e('How to Use', 'lknwp-filebrowser'); ?></h2>
				</div>
				<div class="lknwp-instructions-content">
					<p><?php _e('To display the file browser on your website, follow these simple steps:', 'lknwp-filebrowser'); ?></p>
					<ol style="margin: 15px 0; padding-left: 20px;">
						<li><?php _e('Copy the shortcode below', 'lknwp-filebrowser'); ?></li>
						<li><?php _e('Go to the page or post where you want to display the file browser', 'lknwp-filebrowser'); ?></li>
						<li><?php _e('Add a shortcode component/element in your editor', 'lknwp-filebrowser'); ?></li>
						<li><?php _e('Paste the shortcode into the component', 'lknwp-filebrowser'); ?></li>
						<li><?php _e('Save and publish your page', 'lknwp-filebrowser'); ?></li>
					</ol>
					<div class="lknwp-shortcode-box">
						<code>[lknwp_filebrowser]</code>
						<button type="button" class="button button-primary copy-shortcode" data-shortcode="[lknwp_filebrowser]">
							<i class="fas fa-copy"></i> <?php _e('Copy Shortcode', 'lknwp-filebrowser'); ?>
						</button>
					</div>
					<p style="margin-top: 15px; font-size: 13px; color: #666;">
						<?php _e('The file browser will display all folders and files you create here. Users can navigate through folders, search for files, and download them directly from the frontend.', 'lknwp-filebrowser'); ?>
					</p>
				</div>
			</div>
			
			<div id="lknwp-filebrowser-admin">
				<div class="lknwp-toolbar">
					<button type="button" class="button button-primary" id="create-folder-btn">
						<i class="fas fa-folder-plus"></i> <?php _e('Create Folder', 'lknwp-filebrowser'); ?>
					</button>
					<button type="button" class="button button-secondary" id="upload-file-btn">
						<i class="fas fa-upload"></i> <?php _e('Upload Files', 'lknwp-filebrowser'); ?>
					</button>
					<input type="file" id="file-upload-input" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif" style="display: none;">
				</div>

				<div class="lknwp-breadcrumb">
					<span id="current-path"><?php _e('Home', 'lknwp-filebrowser'); ?></span>
				</div>

				<div class="lknwp-file-manager">
					<div class="lknwp-sidebar">
						<h3><?php _e('Folders', 'lknwp-filebrowser'); ?></h3>
						<div id="folder-tree">
							<!-- Folder tree will be loaded here -->
						</div>
					</div>

					<div class="lknwp-content">
						<div id="folder-contents">
							<!-- Folder contents will be loaded here -->
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Create Folder Modal -->
		<div id="create-folder-modal" class="lknwp-modal" style="display: none;">
			<div class="lknwp-modal-content">
				<span class="lknwp-close">&times;</span>
				<h2><?php \_e('Create New Folder', 'lknwp-filebrowser'); ?></h2>
				<form id="create-folder-form">
					<label for="folder-name"><?php \_e('Folder Name:', 'lknwp-filebrowser'); ?></label>
					<input type="text" id="folder-name" name="folder_name" required>
					<input type="hidden" id="parent-folder-id" name="parent_id" value="0">
					<div class="form-actions">
						<button type="submit" class="button button-primary"><?php \_e('Create', 'lknwp-filebrowser'); ?></button>
						<button type="button" class="button" onclick="closeModal('create-folder-modal')"><?php \_e('Cancel', 'lknwp-filebrowser'); ?></button>
					</div>
				</form>
			</div>
		</div>

		<?php
	}

	/**
	 * Create a nonce
	 */
	public function lknwp_get_admin_nonce() {
		$action_name = isset($_POST['action_name']) ? sanitize_text_field(wp_unslash($_POST['action_name'])) : '';
		if (!$action_name) {
			wp_send_json_error('Action name required');
		}
		$nonce = wp_create_nonce($action_name);
		wp_send_json_success(array('nonce' => $nonce));
	}

	/**
	 * AJAX handler for creating folders
	 */
	public function create_folder_ajax() {
		check_ajax_referer('lknwp_filebrowser_nonce', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_die(__('Insufficient permissions', 'lknwp-filebrowser'));
		}

		$folder_name = sanitize_text_field($_POST['folder_name']);
		$parent_id = intval($_POST['parent_id']);

		if (empty($folder_name)) {
			wp_send_json_error(__('Folder name is required', 'lknwp-filebrowser'));
		}

		global $wpdb;
		$folders_table = $wpdb->prefix . 'lknwp_filebrowser_folders';

		// Build path
		$path = $this->build_folder_path($parent_id) . '/' . $folder_name;

		$result = $wpdb->insert(
			$folders_table,
			array(
				'name' => $folder_name,
				'parent_id' => $parent_id,
				'path' => $path
			),
			array('%s', '%d', '%s')
		);

		if ($result === false) {
			wp_send_json_error(__('Failed to create folder', 'lknwp-filebrowser'));
		}

		wp_send_json_success(array(
			'message' => __('Folder created successfully', 'lknwp-filebrowser'),
			'folder_id' => $wpdb->insert_id
		));
	}

	/**
	 * AJAX handler for uploading files
	 */
	public function upload_file_ajax() {
		check_ajax_referer('lknwp_filebrowser_nonce', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_die(__('Insufficient permissions', 'lknwp-filebrowser'));
		}

		$folder_id = intval($_POST['folder_id']);

		if (empty($_FILES['files'])) {
			wp_send_json_error(__('No files uploaded', 'lknwp-filebrowser'));
		}

		$upload_dir = wp_upload_dir();
		$filebrowser_dir = $upload_dir['basedir'] . '/lknwp-filebrowser';
		$filebrowser_url = $upload_dir['baseurl'] . '/lknwp-filebrowser';

		$uploaded_files = array();
		$files = $_FILES['files'];

		for ($i = 0; $i < count($files['name']); $i++) {
			if ($files['error'][$i] === UPLOAD_ERR_OK) {
				$original_name = sanitize_file_name($files['name'][$i]);
				$file_size = $files['size'][$i];
				$file_type = wp_check_filetype($original_name);
				
				// Generate unique filename
				$filename = wp_unique_filename($filebrowser_dir, $original_name);
				$file_path = $filebrowser_dir . '/' . $filename;
				$file_url = $filebrowser_url . '/' . $filename;

				if (\move_uploaded_file($files['tmp_name'][$i], $file_path)) {
					// Save to database
					global $wpdb;
					$files_table = $wpdb->prefix . 'lknwp_filebrowser_files';

					$result = $wpdb->insert(
						$files_table,
						array(
							'name' => $filename,
							'original_name' => $original_name,
							'folder_id' => $folder_id,
							'file_type' => $file_type['ext'],
							'file_size' => $file_size,
							'file_path' => $file_path,
							'file_url' => $file_url
						),
						array('%s', '%s', '%d', '%s', '%d', '%s', '%s')
					);

					if ($result !== false) {
						$uploaded_files[] = array(
							'id' => $wpdb->insert_id,
							'name' => $original_name,
							'size' => $file_size,
							'type' => $file_type['ext']
						);
					}
				}
			}
		}

		if (empty($uploaded_files)) {
			wp_send_json_error(__('Failed to upload files', 'lknwp-filebrowser'));
		}

		wp_send_json_success(array(
			'message' => __('Files uploaded successfully', 'lknwp-filebrowser'),
			'files' => $uploaded_files
		));
	}

	/**
	 * AJAX handler for deleting folders
	 */
	public function delete_folder_ajax() {
		check_ajax_referer('lknwp_filebrowser_nonce', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_die(__('Insufficient permissions', 'lknwp-filebrowser'));
		}

		$folder_id = intval($_POST['folder_id']);
		
		global $wpdb;
		$folders_table = $wpdb->prefix . 'lknwp_filebrowser_folders';
		$files_table = $wpdb->prefix . 'lknwp_filebrowser_files';

		// Delete all files in this folder and subfolders
		$this->delete_folder_recursive($folder_id);

		wp_send_json_success(__('Folder deleted successfully', 'lknwp-filebrowser'));
	}

	/**
	 * AJAX handler for deleting files
	 */
	public function delete_file_ajax() {
		check_ajax_referer('lknwp_filebrowser_nonce', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_die(__('Insufficient permissions', 'lknwp-filebrowser'));
		}

		$file_id = intval($_POST['file_id']);
		
		global $wpdb;
		$files_table = $wpdb->prefix . 'lknwp_filebrowser_files';

		// Get file info
		$file = $wpdb->get_row($wpdb->prepare("SELECT * FROM $files_table WHERE id = %d", $file_id));
		
		if ($file) {
			// Delete physical file
			if (file_exists($file->file_path)) {
				unlink($file->file_path);
			}

			// Delete from database
			$wpdb->delete($files_table, array('id' => $file_id), array('%d'));
		}

		wp_send_json_success(__('File deleted successfully', 'lknwp-filebrowser'));
	}

	/**
	 * AJAX handler for getting folder contents
	 */
	public function get_folder_contents_ajax() {
		check_ajax_referer('lknwp_filebrowser_nonce', 'nonce');
		
		$folder_id = intval($_POST['folder_id']);
		
		$contents = $this->get_folder_contents($folder_id);
		
		wp_send_json_success($contents);
	}

	/**
	 * AJAX handler for getting all folders (for tree view)
	 */
	public function get_all_folders_ajax() {
		check_ajax_referer('lknwp_filebrowser_nonce', 'nonce');
		
		global $wpdb;
		$folders_table = $wpdb->prefix . 'lknwp_filebrowser_folders';

		$folders = $wpdb->get_results("SELECT * FROM $folders_table ORDER BY parent_id ASC, name ASC");
		
		wp_send_json_success($folders);
	}

	/**
	 * Get folder contents (folders and files)
	 */
	private function get_folder_contents($folder_id) {
		global $wpdb;
		$folders_table = $wpdb->prefix . 'lknwp_filebrowser_folders';
		$files_table = $wpdb->prefix . 'lknwp_filebrowser_files';

		// Get subfolders
		$folders = $wpdb->get_results($wpdb->prepare(
			"SELECT * FROM $folders_table WHERE parent_id = %d ORDER BY name ASC",
			$folder_id
		));

		// Get files
		$files = $wpdb->get_results($wpdb->prepare(
			"SELECT * FROM $files_table WHERE folder_id = %d ORDER BY original_name ASC",
			$folder_id
		));

		return array(
			'folders' => $folders,
			'files' => $files
		);
	}

	/**
	 * Build folder path
	 */
	private function build_folder_path($folder_id) {
		if ($folder_id == 0) {
			return '';
		}

		global $wpdb;
		$folders_table = $wpdb->prefix . 'lknwp_filebrowser_folders';
		
		$folder = $wpdb->get_row($wpdb->prepare("SELECT * FROM $folders_table WHERE id = %d", $folder_id));
		
		if ($folder && $folder->parent_id > 0) {
			return $this->build_folder_path($folder->parent_id) . '/' . $folder->name;
		} elseif ($folder) {
			return $folder->name;
		}
		
		return '';
	}

	/**
	 * Recursively delete folder and its contents
	 */
	private function delete_folder_recursive($folder_id) {
		global $wpdb;
		$folders_table = $wpdb->prefix . 'lknwp_filebrowser_folders';
		$files_table = $wpdb->prefix . 'lknwp_filebrowser_files';

		// Get all subfolders
		$subfolders = $wpdb->get_results($wpdb->prepare(
			"SELECT id FROM $folders_table WHERE parent_id = %d",
			$folder_id
		));

		// Recursively delete subfolders
		foreach ($subfolders as $subfolder) {
			$this->delete_folder_recursive($subfolder->id);
		}

		// Delete all files in this folder
		$files = $wpdb->get_results($wpdb->prepare(
			"SELECT * FROM $files_table WHERE folder_id = %d",
			$folder_id
		));

		foreach ($files as $file) {
			if (file_exists($file->file_path)) {
				unlink($file->file_path);
			}
		}

		// Delete files from database
		$wpdb->delete($files_table, array('folder_id' => $folder_id), array('%d'));

		// Delete the folder itself
		$wpdb->delete($folders_table, array('id' => $folder_id), array('%d'));
	}

	/**
	 * AJAX handler for updating folder name
	 */
	public function update_folder_name_ajax() {
		check_ajax_referer('lknwp_filebrowser_nonce', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_die(__('Insufficient permissions', 'lknwp-filebrowser'));
		}

		$folder_id = intval($_POST['id']);
		$new_name = sanitize_text_field($_POST['new_name']);

		if (empty($new_name)) {
			wp_send_json_error(__('Nome da pasta não pode estar vazio', 'lknwp-filebrowser'));
		}

		global $wpdb;
		$folders_table = $wpdb->prefix . 'lknwp_filebrowser_folders';

		// Check if folder exists
		$folder = $wpdb->get_row($wpdb->prepare("SELECT * FROM $folders_table WHERE id = %d", $folder_id));
		if (!$folder) {
			wp_send_json_error(__('Pasta não encontrada', 'lknwp-filebrowser'));
		}

		// Check if name already exists in the same parent folder
		$existing = $wpdb->get_var($wpdb->prepare(
			"SELECT id FROM $folders_table WHERE name = %s AND parent_id = %d AND id != %d",
			$new_name,
			$folder->parent_id,
			$folder_id
		));

		if ($existing) {
			wp_send_json_error(__('Já existe uma pasta com este nome', 'lknwp-filebrowser'));
		}

		// Update folder name
		$result = $wpdb->update(
			$folders_table,
			array('name' => $new_name),
			array('id' => $folder_id),
			array('%s'),
			array('%d')
		);

		if ($result !== false) {
			wp_send_json_success(__('Nome da pasta atualizado com sucesso', 'lknwp-filebrowser'));
		} else {
			wp_send_json_error(__('Erro ao atualizar nome da pasta', 'lknwp-filebrowser'));
		}
	}

	/**
	 * AJAX handler for updating file name
	 */
	public function update_file_name_ajax() {
		check_ajax_referer('lknwp_filebrowser_nonce', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_die(__('Insufficient permissions', 'lknwp-filebrowser'));
		}

		$file_id = intval($_POST['id']);
		$new_name = sanitize_file_name($_POST['new_name']);

		if (empty($new_name)) {
			wp_send_json_error(__('Nome do arquivo não pode estar vazio', 'lknwp-filebrowser'));
		}

		global $wpdb;
		$files_table = $wpdb->prefix . 'lknwp_filebrowser_files';

		// Check if file exists
		$file = $wpdb->get_row($wpdb->prepare("SELECT * FROM $files_table WHERE id = %d", $file_id));
		if (!$file) {
			wp_send_json_error(__('Arquivo não encontrado', 'lknwp-filebrowser'));
		}

		// Check if name already exists in the same folder
		$existing = $wpdb->get_var($wpdb->prepare(
			"SELECT id FROM $files_table WHERE original_name = %s AND folder_id = %d AND id != %d",
			$new_name,
			$file->folder_id,
			$file_id
		));

		if ($existing) {
			wp_send_json_error(__('Já existe um arquivo com este nome', 'lknwp-filebrowser'));
		}

		// Get file extension to validate
		$file_info = \pathinfo($new_name);
		$old_file_info = \pathinfo($file->original_name);
		
		if (isset($file_info['extension']) && isset($old_file_info['extension'])) {
			if (\strtolower($file_info['extension']) !== \strtolower($old_file_info['extension'])) {
				wp_send_json_error(__('Não é possível alterar a extensão do arquivo', 'lknwp-filebrowser'));
			}
		}

		// Update file name
		$result = $wpdb->update(
			$files_table,
			array('original_name' => $new_name),
			array('id' => $file_id),
			array('%s'),
			array('%d')
		);

		if ($result !== false) {
			wp_send_json_success(__('Nome do arquivo atualizado com sucesso', 'lknwp-filebrowser'));
		} else {
			wp_send_json_error(__('Erro ao atualizar nome do arquivo', 'lknwp-filebrowser'));
		}
	}

	/**
	 * AJAX handler for getting folder files (admin tree)
	 */
	public function get_folder_files_ajax() {
		check_ajax_referer( 'lknwp_filebrowser_admin_nonce', 'nonce' );
		
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

	/**
	 * Get all folders and files for admin frontend tree navigation
	 */
	public function get_all_folders_admin_frontend() {
		check_ajax_referer( 'lknwp_filebrowser_nonce', 'nonce' );
		
		global $wpdb;
		$folders_table = $wpdb->prefix . 'lknwp_filebrowser_folders';
		$files_table = $wpdb->prefix . 'lknwp_filebrowser_files';

		// Get all folders
		$folders = $wpdb->get_results("SELECT * FROM $folders_table ORDER BY parent_id ASC, name ASC");
		
		// Get all files
		$files = $wpdb->get_results("SELECT * FROM $files_table ORDER BY folder_id ASC, original_name ASC");

		wp_send_json_success(array(
			'folders' => $folders,
			'files' => $files
		));
	}

}
