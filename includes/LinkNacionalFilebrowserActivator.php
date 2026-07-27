<?php

namespace LinkNacional\Filebrowser\Includes;

class LinkNacionalFilebrowserActivator {

	public static function activate() {
		global $wpdb;

		$folders_table = $wpdb->prefix . 'lkn_filebrowser_folders';
		$files_table   = $wpdb->prefix . 'lkn_filebrowser_files';

		$charset_collate = $wpdb->get_charset_collate();

		$sql_folders = "CREATE TABLE $folders_table (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			parent_id mediumint(9) DEFAULT 0,
			path text NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY parent_id (parent_id)
		) $charset_collate;";

		$sql_files = "CREATE TABLE $files_table (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			original_name varchar(255) NOT NULL,
			folder_id mediumint(9) NOT NULL,
			file_type varchar(50) NOT NULL,
			file_size bigint(20) NOT NULL,
			file_path text NOT NULL,
			file_url text NOT NULL,
			description text,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY folder_id (folder_id)
		) $charset_collate;";

		require_once \ABSPATH . 'wp-admin/includes/upgrade.php';
		\dbDelta( $sql_folders );
		\dbDelta( $sql_files );

		$upload_dir      = wp_upload_dir();
		$filebrowser_dir = $upload_dir['basedir'] . '/lkn-filebrowser';

		if ( ! \file_exists( $filebrowser_dir ) ) {
			wp_mkdir_p( $filebrowser_dir );
		}

		\add_option( 'lkn_filebrowser_db_version', '1.0.0' );
	}
}
