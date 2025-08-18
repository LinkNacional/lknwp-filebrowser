(function ($) {
	'use strict';

	let currentFolderId = 0;
	let currentPath = 'Home';
	let adminNonce = '';

	$(document).ready(function () {

		// Copy shortcode button handler
		$('.copy-shortcode').on('click', function (e) {
			e.preventDefault();
			const shortcode = $(this).data('shortcode');

			// Create temporary textarea for copying
			const tempTextarea = $('<textarea>');
			$('body').append(tempTextarea);
			tempTextarea.val(shortcode).select();
			document.execCommand('copy');
			tempTextarea.remove();

			// Show feedback
			const $btn = $(this);
			const originalText = $btn.html();
			$btn.html('<i class="fas fa-check"></i> ' + (lknwp_ajax.copied_text || 'Copied!'));
			$btn.addClass('copied');

			setTimeout(function () {
				$btn.html(originalText);
				$btn.removeClass('copied');
			}, 2000);
		});

		// Create folder button
		$('#create-folder-btn').on('click', function () {
			$('#parent-folder-id').val(currentFolderId);
			$('#create-folder-modal').show();
		});

		// Upload file button
		$('#upload-file-btn').on('click', function () {
			$('#file-upload-input').click();
		});

		// File upload handler
		$('#file-upload-input').on('change', function () {
			const files = this.files;
			if (files.length > 0) {
				uploadFiles(files, currentFolderId);
			}
		});

		// Create folder form submit
		$('#create-folder-form').on('submit', function (e) {
			e.preventDefault();
			createFolder();
		});

		// Close modal
		$('.lknwp-close').on('click', function () {
			$(this).closest('.lknwp-modal').hide();
		});

		// Close modal on outside click
		$('.lknwp-modal').on('click', function (e) {
			if (e.target === this) {
				$(this).hide();
			}
		});

		// Folder tree click handler
		$(document).on('click', '.folder-item-content', function () {
			const folderId = $(this).closest('.folder-item').data('folder-id') || 0;
			const folderName = $(this).closest('.folder-item').data('folder-name') || 'Home';
			navigateToFolder(folderId, folderName);
		});

		// Content item click handler for folders
		$(document).on('click', '.content-item.folder', function () {
			const folderId = $(this).data('folder-id');
			const folderName = $(this).data('folder-name');
			navigateToFolder(folderId, folderName);
		});

		// File content item click handler (for navigating to file location) - expanded to full container
		$(document).on('click', '.content-item.file', function (e) {
			// Make sure the click is not on a button or edit container
			if ($(e.target).closest('.content-item-actions, .edit-container, .file-locate-btn').length === 0) {
				const $fileItem = $(this);
				const fileName = $fileItem.find('.content-item-name').text().trim();
				const folderId = $fileItem.data('folder-id');

				// Clear any previous highlighting in main content
				$('.content-item.file').removeClass('selected-in-tree');

				// Add green highlighting to this file in main content
				$fileItem.addClass('selected-in-tree');

				// Highlight the file in tree and select it (preserve main selection)
				highlightFileInTree(fileName, folderId, true);
			}
		});

		// File item in tree click handler
		$(document).on('click', '.file-item-tree', function (e) {
			e.stopPropagation();

			const fileName = $(this).data('file-name');
			const folderId = $(this).data('parent-folder-id');
			const $clickedFile = $(this);

			// Add selection styling in tree
			$('.file-item-tree').removeClass('selected');
			$clickedFile.addClass('selected');

			// Clear any previous highlighting in main content
			$('.content-item.file').removeClass('selected-in-tree');

			// Navigate to folder if not already there
			if (currentFolderId !== folderId) {
				navigateToFolder(folderId, null, function () {
					// Callback executed after folder contents are loaded
					selectFileInMainContent(fileName);
					// Re-select the file in tree (in case it got cleared)
					$('.file-item-tree').removeClass('selected');
					$clickedFile.addClass('selected');
				}, true); // Preserve file selection
			} else {
				// Already in the correct folder, just highlight the file
				selectFileInMainContent(fileName);
			}
		});		// File locate button click handler (only for main content grid)
		$(document).on('click', '.file-locate-btn', function (e) {
			e.stopPropagation();

			// Only handle files in main content grid
			const $fileInContent = $(this).closest('.content-item.file');
			if ($fileInContent.length > 0) {
				const fileUrl = $fileInContent.find('.file-content-area').data('file-url');
				if (fileUrl) {
					window.open(fileUrl, '_blank');
				}
			}
		});

		// Toggle file visibility in tree
		$(document).on('click', '.folder-toggle-btn', function (e) {
			e.stopPropagation();
			e.preventDefault();

			const folderId = $(this).data('folder-id');
			const $icon = $(this).find('i');
			const $treeContainer = $('#folder-tree');
			const $subfolders = $treeContainer.find(`.folder-item.child-folder[data-parent-id="${folderId}"]`);
			const $files = $treeContainer.find(`.file-item-tree.child-file-tree[data-parent-folder-id="${folderId}"]`);

			if ($icon.hasClass('fa-caret-right')) {
				// Expand - show subfolders and files
				$subfolders.addClass('show');
				$files.addClass('show');
				$icon.removeClass('fa-caret-right').addClass('fa-caret-down');
				$(this).attr('title', 'Recolher');
			} else {
				// Collapse - hide subfolders and files recursively
				hideSubfoldersRecursively(folderId);
				$subfolders.removeClass('show');
				$files.removeClass('show');
				$icon.removeClass('fa-caret-down').addClass('fa-caret-right');
				$(this).attr('title', 'Expandir');
			}
		});

		// Delete handlers
		$(document).on('click', '.delete-btn', function (e) {
			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();

			const itemType = $(this).data('type');
			const itemId = $(this).data('id');
			const itemName = $(this).data('name');

			if (confirm(`Deseja excluir este ${itemType}? "${itemName}"`)) {
				if (itemType === 'pasta') {
					deleteFolder(itemId);
				} else {
					deleteFile(itemId);
				}
			}

			return false;
		});

		// Edit handlers
		$(document).on('click', '.edit-btn', function (e) {
			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();
			const itemType = $(this).data('type');
			const itemId = $(this).data('id');
			const currentName = $(this).data('name');
			const $contentItem = $(this).closest('.content-item');
			const $nameElement = $contentItem.find('.content-item-name');

			// Prevent multiple edits
			if ($contentItem.find('.edit-container').length > 0) {
				return;
			}

			// Create edit container with input and buttons
			let inputValue = currentName;
			let extension = '';

			if (itemType === 'arquivo') {
				// For files, separate name and extension
				const lastDot = currentName.lastIndexOf('.');
				if (lastDot > 0) {
					inputValue = currentName.substring(0, lastDot);
					extension = currentName.substring(lastDot);
				}
			}

			const $editContainer = $(`
				<div class="edit-container">
					<input type="text" class="edit-name-input" value="${inputValue}" data-extension="${extension}">
					<div class="edit-controls">
						<button class="edit-confirm-btn" title="Confirmar">
							<i class="fas fa-check"></i>
						</button>
						<button class="edit-cancel-btn" title="Cancelar">
							<i class="fas fa-times"></i>
						</button>
					</div>
				</div>
			`);

			$nameElement.hide().after($editContainer);
			const $input = $editContainer.find('.edit-name-input');
			$input.focus().select();

			// Add editing class to prevent container clicks (fallback)
			$contentItem.addClass('editing');

			// Prevent container click when clicking on edit elements
			$editContainer.on('click', function (e) {
				e.stopPropagation();
			});

			// Handle confirm button
			$editContainer.find('.edit-confirm-btn').on('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				e.stopImmediatePropagation();
				confirmEdit($input, itemType, itemId, currentName, $contentItem, $nameElement, $editContainer);
				return false;
			});

			// Handle cancel button
			$editContainer.find('.edit-cancel-btn').on('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				e.stopImmediatePropagation();
				cancelEdit($editContainer, $nameElement);
				return false;
			});

			// Handle Enter key
			$input.on('keydown', function (e) {
				if (e.keyCode === 13) { // Enter key
					e.preventDefault();
					confirmEdit($input, itemType, itemId, currentName, $contentItem, $nameElement, $editContainer);
				} else if (e.keyCode === 27) { // Escape key
					e.preventDefault();
					cancelEdit($editContainer, $nameElement);
				}
			});

			return false;
		});

		// Helper functions for edit functionality
		function confirmEdit($input, itemType, itemId, currentName, $contentItem, $nameElement, $editContainer) {
			const newName = $input.val().trim();
			const extension = $input.data('extension') || '';

			if (!newName) {
				alert(lknwp_ajax.name_empty_error || 'Nome não pode estar vazio');
				return;
			}

			const originalNamePart = itemType === 'arquivo' ?
				currentName.substring(0, currentName.lastIndexOf('.') > 0 ? currentName.lastIndexOf('.') : currentName.length) :
				currentName;

			if (newName !== originalNamePart) {
				const finalName = itemType === 'arquivo' ? newName + extension : newName;
				updateItemName(itemType, itemId, finalName, $contentItem, $editContainer, $nameElement);
			} else {
				cancelEdit($editContainer, $nameElement);
			}
		}

		function cancelEdit($editContainer, $nameElement) {
			const $contentItem = $editContainer.closest('.content-item');
			$contentItem.removeClass('editing');
			$editContainer.remove();
			$nameElement.show();
		}

		// Load initial content somente após buscar o nonce
		fetchAndSetAdminNonce('lknwp_filebrowser_nonce', function () {
			loadFolderTree();
			loadFolderContents(0);
		});
	});

	function fetchAndSetAdminNonce(actionName, onReady) {
		$.ajax({
			url: lknwp_ajax.ajax_url,
			type: 'POST',
			data: { action: 'lknwp_get_admin_nonce', action_name: actionName },
			success: function (response) {
				if (response.success && response.data.nonce) {
					adminNonce = response.data.nonce;
				} else {
					console.error('Não foi possível obter o nonce admin.');
				}
				if (typeof onReady === 'function') onReady();
			},
			error: function () {
				console.error('Erro ao buscar o nonce admin.');
				if (typeof onReady === 'function') onReady();
			}
		});
	}

	function loadFolderTree() {
		// Get all folders and files to build tree
		$.ajax({
			url: lknwp_ajax.ajax_url,
			type: 'POST',
			data: {
				action: 'lknwp_get_all_folders_admin_frontend',
				nonce: adminNonce
			},
			success: function (response) {
				if (response.success) {
					const folders = response.data.folders || [];
					const files = response.data.files || [];
					renderFolderTreeRecursive(folders, files, $('#folder-tree'));
				} else {
					console.error('Error loading folder tree:', response);
				}
			},
			error: function (xhr, status, error) {
				console.error('AJAX Error loading folder tree:', status, error);
				console.error('Response:', xhr.responseText);
			}
		});
	}

	function renderFolderTreeRecursive(allFolders, allFiles, container, parentId = 0, level = 0) {
		if (level === 0) {
			container.empty();
			// Add root folder with proper structure for click handling
			const rootItem = $('<div class="folder-item active" data-folder-id="0" data-folder-name="Home">')
				.html('<div class="folder-item-content"><i class="fas fa-home"></i> Home</div>');
			container.append(rootItem);

			// Render files for root folder (folder_id = 0)
			renderFilesInFolderTree(allFiles, container, 0, 1);
		}

		// Render folders
		const folders = allFolders.filter(f => f.parent_id == parentId);
		folders.forEach(function (folder) {
			const hasChildren = allFolders.some(f => f.parent_id == folder.id);
			const hasFiles = allFiles.some(f => f.folder_id == folder.id);
			let folderItemHtml;

			if (hasChildren || hasFiles) {
				folderItemHtml = `
					<div class="folder-item-content">
						<i class="fas fa-folder"></i> ${folder.name}
					</div>
					<button class="folder-toggle-btn" data-folder-id="${folder.id}" title="Expandir/Recolher">
						<i class="fas fa-caret-right"></i>
					</button>
				`;
			} else {
				folderItemHtml = `
					<div class="folder-item-content">
						<i class="fas fa-folder"></i> ${folder.name}
					</div>
				`;
			}

			const folderItem = $('<div class="folder-item' + (level > 0 ? ' child-folder' : '') + '" data-folder-id="' + folder.id + '" data-folder-name="' + folder.name + '"' + (level > 0 ? ' data-parent-id="' + parentId + '"' : '') + '>')
				.html(folderItemHtml)
				.css('padding-left', (20 * level + 10) + 'px');

			container.append(folderItem);

			// Render files for this folder
			renderFilesInFolderTree(allFiles, container, folder.id, level + 1);

			// Render children folders
			renderFolderTreeRecursive(allFolders, allFiles, container, folder.id, level + 1);
		});
	} function renderFilesInFolderTree(allFiles, container, folderId, level) {
		const files = allFiles.filter(f => f.folder_id == folderId);

		files.forEach(function (file) {
			const fileExtension = file.original_name.split('.').pop().toLowerCase();
			let fileIcon = 'fas fa-file';

			// Set file icon based on extension
			if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExtension)) {
				fileIcon = 'fas fa-file-image';
			} else if (['pdf'].includes(fileExtension)) {
				fileIcon = 'fas fa-file-pdf';
			} else if (['doc', 'docx'].includes(fileExtension)) {
				fileIcon = 'fas fa-file-word';
			} else if (['xls', 'xlsx'].includes(fileExtension)) {
				fileIcon = 'fas fa-file-excel';
			} else if (['ppt', 'pptx'].includes(fileExtension)) {
				fileIcon = 'fas fa-file-powerpoint';
			} else if (['txt'].includes(fileExtension)) {
				fileIcon = 'fas fa-file-alt';
			}

			const fileItemHtml = `
				<div class="file-item-content-tree">
					<i class="${fileIcon}"></i> ${file.original_name}
				</div>
			`;

			const $fileItem = $('<div class="file-item-tree child-file-tree" data-file-id="' + file.id + '" data-parent-folder-id="' + folderId + '" data-file-name="' + file.original_name + '">')
				.html(fileItemHtml)
				.css('padding-left', (20 * level + 10) + 'px');

			container.append($fileItem);
		});
	}

	function loadFolderContents(folderId, callback) {
		$('#folder-contents').html('<div class="loading"><i class="fas fa-spinner"></i> ' + (lknwp_ajax.loading_text || 'Loading...') + '</div>');

		$.ajax({
			url: lknwp_ajax.ajax_url,
			type: 'POST',
			data: {
				action: 'lknwp_get_folder_contents',
				nonce: adminNonce,
				folder_id: folderId
			},
			success: function (response) {
				if (response.success) {
					renderFolderContents(response.data);
					if (typeof callback === 'function') {
						callback();
					}
				} else {
					$('#folder-contents').html('<div class="error">' + (lknwp_ajax.error_loading_text || 'Error loading contents') + '</div>');
				}
			},
			error: function () {
				$('#folder-contents').html('<div class="error">' + (lknwp_ajax.error_loading_text || 'Error loading contents') + '</div>');
			}
		});
	}

	function renderFolderContents(data) {
		const container = $('#folder-contents');
		container.empty();

		const folders = data.folders || [];
		const files = data.files || [];

		if (folders.length === 0 && files.length === 0) {
			container.html('<div class="empty-folder"><i class="fas fa-folder-open"></i><p>' + (lknwp_ajax.empty_folder_text || 'This folder is empty') + '</p></div>');
			return;
		}

		// Render folders
		folders.forEach(function (folder) {
			const folderElement = $(`
				<div class="content-item folder" data-folder-id="${folder.id}" data-folder-name="${folder.name}">
					<div class="content-item-actions">
						<button class="edit-btn" data-type="pasta" data-id="${folder.id}" data-name="${folder.name}" title="Editar nome">
							<i class="fas fa-edit"></i>
						</button>
						<button class="delete-btn" data-type="pasta" data-id="${folder.id}" data-name="${folder.name}" title="Excluir pasta">
							<i class="fas fa-trash"></i>
						</button>
					</div>
					<i class="fas fa-folder"></i>
					<div class="content-item-name">${folder.name}</div>
					<div class="content-item-info">${lknwp_ajax.folder_text || 'Folder'}</div>
				</div>
			`);
			container.append(folderElement);
		});

		// Render files
		files.forEach(function (file) {
			const fileIcon = getFileIcon(file.file_type);
			const fileSize = formatFileSize(file.file_size);

			const fileElement = $(`
				<div class="content-item file ${file.file_type}" data-file-id="${file.id}" data-folder-id="${file.folder_id}">
					<div class="content-item-actions">
						<button class="edit-btn" data-type="arquivo" data-id="${file.id}" data-name="${file.original_name}" title="Editar nome">
							<i class="fas fa-edit"></i>
						</button>
						<button class="delete-btn" data-type="arquivo" data-id="${file.id}" data-name="${file.original_name}" title="Excluir arquivo">
							<i class="fas fa-trash"></i>
						</button>
					</div>
					<div class="file-content-area" data-file-url="${file.file_url}">
						<i class="${fileIcon}"></i>
						<div class="content-item-name">${file.original_name}</div>
						<div class="content-item-info">${fileSize}</div>
					</div>
					<button class="file-locate-btn" data-folder-id="${file.folder_id}" data-file-name="${file.original_name}" title="Abrir arquivo">
						<i class="fas fa-external-link-alt"></i>
					</button>
				</div>
			`);

			container.append(fileElement);
		});
	}

	function navigateToFolder(folderId, folderName, callback, preserveFileSelection) {
		// If folderName is not provided, try to find it
		if (!folderName) {
			if (folderId === 0) {
				folderName = 'Home';
			} else {
				// Try to find folder name from the tree
				const $folderItem = $('#folder-tree').find(`[data-folder-id="${folderId}"]`);
				if ($folderItem.length > 0) {
					folderName = $folderItem.data('folder-name') || 'Folder';
				} else {
					folderName = 'Folder';
				}
			}
		}

		currentFolderId = folderId;
		currentPath = folderName;

		// Clear previous file selections when navigating to a different folder (unless preserving)
		if (!preserveFileSelection) {
			$('.file-item-tree').removeClass('selected');
			$('.content-item.file').removeClass('selected-in-tree');
		}

		// Update breadcrumb
		$('#current-path').text(folderName);

		// Update active folder in tree with expansion
		updateActiveFolderInTree(folderId);

		// Load folder contents with callback
		loadFolderContents(folderId, callback);
	}

	function createFolder() {
		const formData = new FormData($('#create-folder-form')[0]);
		formData.append('action', 'lknwp_create_folder');
		formData.append('nonce', adminNonce);

		$.ajax({
			url: lknwp_ajax.ajax_url,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function (response) {
				if (response.success) {
					$('#create-folder-modal').hide();
					$('#folder-name').val('');
					loadFolderTree();
					loadFolderContents(currentFolderId);
					alert(response.data.message);
				} else {
					alert('Error: ' + response.data);
				}
			},
			error: function () {
				alert(lknwp_ajax.create_folder_error || 'Error creating folder');
			}
		});
	}

	function uploadFiles(files, folderId) {
		const formData = new FormData();
		formData.append('action', 'lknwp_upload_file');
		formData.append('nonce', adminNonce);
		formData.append('folder_id', folderId);

		for (let i = 0; i < files.length; i++) {
			formData.append('files[]', files[i]);
		}

		// Show loading
		$('#folder-contents').html('<div class="loading"><i class="fas fa-spinner"></i> ' + (lknwp_ajax.uploading_text || 'Uploading files...') + '</div>');

		$.ajax({
			url: lknwp_ajax.ajax_url,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function (response) {
				if (response.success) {
					loadFolderTree(); // Update sidebar tree
					loadFolderContents(currentFolderId);
					alert(response.data.message);
				} else {
					alert('Error: ' + response.data);
					loadFolderContents(currentFolderId);
				}
			},
			error: function () {
				alert(lknwp_ajax.upload_error || 'Error uploading files');
				loadFolderContents(currentFolderId);
			}
		});

		// Clear file input
		$('#file-upload-input').val('');
	}

	function deleteFolder(folderId) {
		$.ajax({
			url: lknwp_ajax.ajax_url,
			type: 'POST',
			data: {
				action: 'lknwp_delete_folder',
				nonce: adminNonce,
				folder_id: folderId
			},
			success: function (response) {
				if (response.success) {
					loadFolderTree();
					if (currentFolderId == folderId) {
						navigateToFolder(0, 'Home');
					} else {
						loadFolderContents(currentFolderId);
					}
					alert(response.data);
				} else {
					alert('Error: ' + response.data);
				}
			}
		});
	}

	function deleteFile(fileId) {
		$.ajax({
			url: lknwp_ajax.ajax_url,
			type: 'POST',
			data: {
				action: 'lknwp_delete_file',
				nonce: adminNonce,
				file_id: fileId
			},
			success: function (response) {
				if (response.success) {
					loadFolderTree(); // Update sidebar tree
					loadFolderContents(currentFolderId);
					alert(response.data);
				} else {
					alert('Error: ' + response.data);
				}
			}
		});
	}

	function getFileIcon(fileType) {
		const icons = {
			pdf: 'fas fa-file-pdf',
			doc: 'fas fa-file-word',
			docx: 'fas fa-file-word',
			xls: 'fas fa-file-excel',
			xlsx: 'fas fa-file-excel',
			ppt: 'fas fa-file-powerpoint',
			pptx: 'fas fa-file-powerpoint',
			txt: 'fas fa-file-alt',
			jpg: 'fas fa-file-image',
			jpeg: 'fas fa-file-image',
			png: 'fas fa-file-image',
			gif: 'fas fa-file-image'
		};

		return icons[fileType] || 'fas fa-file';
	}

	function formatFileSize(bytes) {
		if (bytes === 0) return '0 Bytes';

		const k = 1024;
		const sizes = ['Bytes', 'KB', 'MB', 'GB'];
		const i = Math.floor(Math.log(bytes) / Math.log(k));

		return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
	}

	function hideSubfoldersRecursively(parentId) {
		// Find direct children of this parent
		const $directChildren = $(`.folder-item.child-folder[data-parent-id="${parentId}"]`);
		const $directFiles = $(`.file-item-tree.child-file-tree[data-parent-folder-id="${parentId}"]`);

		// Hide direct children and files
		$directChildren.removeClass('show');
		$directFiles.removeClass('show');

		// For each direct child, also hide their children recursively and reset their toggle buttons
		$directChildren.each(function () {
			const childId = $(this).data('folder-id');
			const $childToggleBtn = $(this).find('.folder-toggle-btn');

			// Reset toggle button state
			if ($childToggleBtn.length > 0) {
				const $childIcon = $childToggleBtn.find('i');
				$childIcon.removeClass('fa-caret-down').addClass('fa-caret-right');
				$childToggleBtn.attr('title', 'Expandir');
			}

			// Recursively hide children of this child
			hideSubfoldersRecursively(childId);
		});
	}

	function updateActiveFolderInTree(folderId) {
		// Remove active class from all folders
		$('.folder-item').removeClass('active');

		// Find and activate the target folder
		const $targetFolder = $(`.folder-item[data-folder-id="${folderId}"]`);
		if ($targetFolder.length === 0) return;

		$targetFolder.addClass('active');

		// Expand parent folders if necessary
		expandParentFolders($targetFolder);
	}

	function expandParentFolders($targetFolder) {
		const parentId = $targetFolder.data('parent-id');

		if (parentId && parentId !== 0) {
			// Find parent folder
			const $parentFolder = $(`.folder-item[data-folder-id="${parentId}"]`);
			if ($parentFolder.length > 0) {
				// Expand parent folder if it has toggle button
				const $toggleBtn = $parentFolder.find('.folder-toggle-btn');
				if ($toggleBtn.length > 0) {
					const $icon = $toggleBtn.find('i');
					const $subfolders = $(`.folder-item.child-folder[data-parent-id="${parentId}"]`);

					// Show subfolders if not already shown
					if (!$subfolders.hasClass('show')) {
						$subfolders.addClass('show');
						$icon.removeClass('fa-caret-right').addClass('fa-caret-down');
						$toggleBtn.attr('title', 'Esconder Subpastas');
					}
				}

				// Recursively expand parent's parents
				expandParentFolders($parentFolder);
			}
		}
	} function updateItemName(itemType, itemId, newName, $contentItem, $editContainer, $nameElement) {
		const action = itemType === 'pasta' ? 'lknwp_update_folder_name' : 'lknwp_update_file_name';

		// Show loading state
		$editContainer.find('.edit-controls').html('<i class="fas fa-spinner fa-spin"></i>');

		$.ajax({
			url: lknwp_ajax.ajax_url,
			type: 'POST',
			data: {
				action: action,
				nonce: adminNonce,
				id: itemId,
				new_name: newName
			},
			success: function (response) {
				if (response.success) {
					// Update the displayed name
					$nameElement.text(newName);

					// Update data attributes
					$contentItem.data(itemType === 'pasta' ? 'folder-name' : 'file-name', newName);
					$contentItem.find('.edit-btn, .delete-btn').data('name', newName);

					// Remove editing class and edit container
					$contentItem.removeClass('editing');
					$editContainer.remove();
					$nameElement.show();

					// Reload folder tree to update names in sidebar
					if (itemType === 'pasta') {
						loadFolderTree();
					} else {
						// Also reload tree for files to update file names in sidebar
						loadFolderTree();
					}

					// Show success message briefly
					const $success = $('<span style="color: #0073aa; font-size: 10px;">✓ ' + (lknwp_ajax.saved_text || 'Salvo') + '</span>');
					$nameElement.after($success);
					setTimeout(() => $success.remove(), 2000);
				} else {
					alert((lknwp_ajax.update_error || 'Erro ao atualizar nome') + ': ' + (response.data || (lknwp_ajax.unknown_error || 'Erro desconhecido')));
					$contentItem.removeClass('editing');
					$editContainer.remove();
					$nameElement.show();
				}
			},
			error: function () {
				alert(lknwp_ajax.update_error || 'Erro ao atualizar nome');
				$contentItem.removeClass('editing');
				$editContainer.remove();
				$nameElement.show();
			}
		});
	}

	// Global functions for modal
	window.closeModal = function (modalId) {
		$('#' + modalId).hide();
	};

	/**
	 * Highlight a specific file in the main content area
	 */
	function highlightFileInContent(fileName) {
		const $contentArea = $('#folder-contents');
		const $fileItems = $contentArea.find('.content-item');

		// Remove any existing highlights
		$fileItems.removeClass('highlighted-file');

		// Find and highlight the target file
		$fileItems.each(function () {
			const itemName = $(this).find('.content-item-name').text().trim();
			if (itemName === fileName) {
				$(this).addClass('highlighted-file');
				// Scroll into view
				$(this)[0].scrollIntoView({ behavior: 'smooth', block: 'center' });

				// Remove highlight after 3 seconds
				setTimeout(() => {
					$(this).removeClass('highlighted-file');
				}, 3000);
				return false; // Break the loop
			}
		});
	}

	/**
	 * Select file in main content (green highlighting)
	 */
	function selectFileInMainContent(fileName) {
		// Clear previous selections
		$('.content-item.file').removeClass('selected-in-tree');

		// Find and select the file
		const $fileItems = $('.content-item.file');
		let found = false;

		$fileItems.each(function () {
			const itemName = $(this).find('.content-item-name').text().trim();
			if (itemName === fileName) {
				$(this).addClass('selected-in-tree');
				// Scroll into view
				$(this)[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
				found = true;
				return false; // Break the loop
			}
		});

		// If not found, try a more flexible search
		if (!found) {
			$fileItems.each(function () {
				const itemName = $(this).find('.content-item-name').text().trim();
				if (itemName.includes(fileName) || fileName.includes(itemName)) {
					$(this).addClass('selected-in-tree');
					$(this)[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
					return false;
				}
			});
		}
	}

	/**
	 * Highlight file in tree sidebar
	 */
	function highlightFileInTree(fileName, folderId, preserveMainSelection) {
		// Clear previous selections in tree
		$('.file-item-tree').removeClass('selected');

		// Clear main content selections only if not preserving
		if (!preserveMainSelection) {
			$('.content-item.file').removeClass('selected-in-tree');
		}

		// First, expand all necessary parent folders AND the folder containing the file
		expandFoldersToShowFile(folderId, function () {
			// After expanding parents, ensure the specific folder is also expanded to show its files
			const $folderItem = $(`.folder-item[data-folder-id="${folderId}"]`);
			if ($folderItem.length > 0) {
				const $toggleBtn = $folderItem.find('.folder-toggle-btn');
				if ($toggleBtn.length > 0) {
					const $icon = $toggleBtn.find('i');
					if ($icon.hasClass('fa-caret-right')) {
						// Expand the folder to show files
						const $files = $(`.file-item-tree.child-file-tree[data-parent-folder-id="${folderId}"]`);
						const $subfolders = $(`.folder-item.child-folder[data-parent-id="${folderId}"]`);

						$files.addClass('show');
						$subfolders.addClass('show');
						$icon.removeClass('fa-caret-right').addClass('fa-caret-down');
						$toggleBtn.attr('title', 'Recolher');
					}
				}
			}

			// Now find and highlight the file
			setTimeout(() => {
				const $treeFiles = $('.file-item-tree');
				let found = false;

				$treeFiles.each(function () {
					const treeFileName = $(this).data('file-name');
					const treeParentFolderId = $(this).data('parent-folder-id');

					if (treeFileName === fileName && treeParentFolderId == folderId) {
						$(this).addClass('selected');
						$(this)[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
						found = true;
						return false;
					}
				});

				// Debug log if file not found - removed for production
			}, 150);
		});
	}

	/**
	 * Expand all parent folders to make a specific folder visible
	 */
	function expandFoldersToShowFile(folderId, callback) {
		if (folderId === 0) {
			// Root folder is always visible
			if (typeof callback === 'function') {
				callback();
			}
			return;
		}

		// Find the folder item
		const $folderItem = $(`.folder-item[data-folder-id="${folderId}"]`);

		if ($folderItem.length === 0) {
			// Folder not found, callback anyway
			if (typeof callback === 'function') {
				callback();
			}
			return;
		}

		// Check if folder is visible (not hidden by parent)
		if ($folderItem.hasClass('child-folder') && !$folderItem.hasClass('show')) {
			// Folder is hidden, need to expand its parent
			const parentId = $folderItem.data('parent-id');

			// Recursively expand parents first
			expandFoldersToShowFile(parentId, function () {
				// After parent is expanded, expand this folder's parent
				const $parentFolder = $(`.folder-item[data-folder-id="${parentId}"]`);
				if ($parentFolder.length > 0) {
					const $toggleBtn = $parentFolder.find('.folder-toggle-btn');
					if ($toggleBtn.length > 0) {
						const $icon = $toggleBtn.find('i');
						if ($icon.hasClass('fa-caret-right')) {
							// Expand parent folder
							const $subfolders = $(`.folder-item.child-folder[data-parent-id="${parentId}"]`);
							const $files = $(`.file-item-tree.child-file-tree[data-parent-folder-id="${parentId}"]`);

							$subfolders.addClass('show');
							$files.addClass('show');
							$icon.removeClass('fa-caret-right').addClass('fa-caret-down');
							$toggleBtn.attr('title', 'Recolher');
						}
					}
				}

				// Now callback to continue
				if (typeof callback === 'function') {
					callback();
				}
			});
		} else {
			// Folder is already visible or is root level
			if (typeof callback === 'function') {
				callback();
			}
		}
	}

})(jQuery);
