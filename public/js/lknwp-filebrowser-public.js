(function ($) {
	'use strict';

	let currentFolderId = 0;
	let isSearchMode = false;
	let searchTimeout;
	let nonce = '';

	$(document).ready(function () {

		// Initialize filebrowser
		fetchAndSetNonce('lknwp_filebrowser_public_nonce', function () {
			$('.lknwp-filebrowser-public').each(function () {
				const $container = $(this);
				const initialFolderId = parseInt($container.data('folder-id')) || 0;
				const initialLayout = $container.data('layout') || 'grid';

				currentFolderId = initialFolderId;

				loadFolderTree($container);
				loadFolderContents(currentFolderId, $container);
			});
		});


		// Search functionality
		$('#lknwp-search-input').on('input', function () {
			const searchTerm = $(this).val().trim();

			clearTimeout(searchTimeout);

			if (searchTerm.length >= 2) {
				searchTimeout = setTimeout(() => {
					performSearch(searchTerm);
				}, 300);
			} else if (searchTerm.length === 0) {
				clearSearch();
			}
		});

		// Search button click
		$('#lknwp-search-btn').on('click', function () {
			const searchTerm = $('#lknwp-search-input').val().trim();
			if (searchTerm.length >= 2) {
				performSearch(searchTerm);
			}
		});

		// Clear search button
		$('#lknwp-clear-search').on('click', function () {
			clearSearch();
		});

		// Enter key search
		$('#lknwp-search-input').on('keypress', function (e) {
			if (e.which === 13) {
				e.preventDefault();
				const searchTerm = $(this).val().trim();
				if (searchTerm.length >= 2) {
					performSearch(searchTerm);
				}
			}
		});

		// Layout switcher
		$('.layout-btn').on('click', function () {
			const layout = $(this).data('layout');
			const $container = $('.lknwp-filebrowser-public');
			const $content = $('.lknwp-filebrowser-content');

			$('.layout-btn').removeClass('active');
			$(this).addClass('active');

			$content.removeClass('grid list').addClass(layout);
			$container.data('layout', layout);
		});

		// Folder navigation
		$(document).on('click', '.content-item.folder, .content-item.search-result-folder', function () {
			const folderId = $(this).data('folder-id');
			const folderName = $(this).data('folder-name');

			// Navigate directly without clearing search first (to avoid double navigation)
			navigateToFolder(folderId, folderName);
		});

		// File click (navigate to location) - navigates to file location and marks it green
		$(document).on('click', '.content-item.file, .content-item.search-result-file', function (e) {
			// Don't trigger if clicking on download button or locate button
			if ($(e.target).closest('.file-download-btn, .file-locate-btn').length) {
				return;
			}

			const fileName = $(this).find('.content-item-name').text().trim();
			const folderId = $(this).data('folder-id'); // Get folder ID from data attribute
			const $container = $(this).closest('.lknwp-filebrowser-public');
			const $treeContainer = $container.find('#lknwp-folder-tree-public');

			// Navigate to the folder containing the file
			currentFolderId = folderId;

			// Clear search UI without reloading content
			if (isSearchMode) {
				$('#lknwp-search-input').val('');
				$('#lknwp-clear-search').hide();
				isSearchMode = false;
			}

			// Update selected file in tree - remove from all first
			$treeContainer.find('.file-item-tree').removeClass('selected');
			// Also remove selected styling from main content files
			$container.find('.content-item.file').removeClass('selected-in-tree');

			// Update active folder in tree and load contents
			updateActiveFolderInTree(folderId, $container);

			// Load folder contents with callback to apply selection after loading
			loadFolderContents(folderId, $container, false, () => {
				// After content is loaded, apply selections immediately
				// Find and select the matching file in the tree
				$treeContainer.find('.file-item-tree').each(function () {
					const $fileItem = $(this);
					const treeFileName = $fileItem.data('file-name');
					const treeParentFolderId = $fileItem.data('parent-folder-id');

					if (treeFileName === fileName && treeParentFolderId == folderId) {
						$fileItem.addClass('selected');
						return false; // Break the each loop
					}
				});

				// Add selected styling to the corresponding file in main content
				$container.find('.content-item.file').each(function () {
					const $mainFile = $(this);
					const mainFileName = $mainFile.find('.content-item-name').text().trim();
					const mainFolderId = $mainFile.data('folder-id');

					if (mainFileName === fileName && mainFolderId == folderId) {
						$mainFile.addClass('selected-in-tree');
						// Scroll to the file smoothly
						$mainFile[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
					}
				});
			});

			// Expand the folder in tree and load its files
			setTimeout(() => {
				const $folderItem = $treeContainer.find(`[data-folder-id="${folderId}"]`);
				const $toggleBtn = $folderItem.find('.folder-toggle-btn-public');

				if ($toggleBtn.length > 0) {
					// Expand the folder to show files
					const $icon = $toggleBtn.find('i');
					const $subfolders = $treeContainer.find(`.folder-item-public.child-folder-public[data-parent-id="${folderId}"]`);
					const $subfiles = $treeContainer.find(`.file-item-tree.child-file-tree[data-parent-id="${folderId}"]`);

					$subfolders.addClass('show');
					$subfiles.addClass('show');
					$icon.removeClass('fa-caret-right').addClass('fa-caret-down');
					$toggleBtn.attr('title', 'Esconder Subpastas');
				}
			}, 300);
		});

		// File locate button click - opens file in new tab
		$(document).on('click', '.file-locate-btn', function (e) {
			e.stopPropagation();

			// Get file URL from the file item
			const $fileItem = $(this).closest('.content-item.file');
			const fileUrl = $fileItem.data('file-url');

			if (fileUrl) {
				window.open(fileUrl, '_blank');
			}
		});

		// Download button click
		$(document).on('click', '.file-download-btn', function (e) {
			e.stopPropagation();
			const fileUrl = $(this).data('file-url');
			const fileName = $(this).data('file-name');
			if (fileUrl) {
				// Create temporary link to force download
				const link = document.createElement('a');
				link.href = fileUrl;
				link.download = fileName || '';
				document.body.appendChild(link);
				link.click();
				document.body.removeChild(link);
			}
		});

		// Breadcrumb navigation
		$(document).on('click', '.breadcrumb-item', function () {
			const folderId = $(this).data('folder-id');
			const folderName = $(this).text();

			navigateToFolder(folderId, folderName);
		});
	});

	function fetchAndSetNonce(actionName, onReady) {
		$.ajax({
			url: lknwp_public_ajax.ajax_url,
			type: 'POST',
			data: { action: 'lknwp_get_public_nonce', action_name: actionName },
			success: function (response) {
				if (response.success && response.data.nonce) {
					nonce = response.data.nonce;
					if (typeof onReady === 'function') onReady();
				} else {
					console.error('Não foi possível obter o nonce.');
				}
			},
			error: function () {
				console.error('Erro ao buscar o nonce.');
			}
		});
	}

	function loadFolderContents(folderId, $container, skipTreeUpdate = false, callback = null) {
		const $content = $container.find('.lknwp-filebrowser-content');

		$content.html('<div class="loading"><i class="fas fa-spinner"></i> ' + (lknwp_public_ajax.loading_text || 'Loading...') + '</div>');

		$.ajax({
			url: lknwp_public_ajax.ajax_url,
			type: 'POST',
			data: {
				action: 'lknwp_frontend_get_contents',
				nonce: nonce,
				folder_id: folderId
			},
			success: function (response) {
				if (response.success) {
					renderFolderContents(response.data, $container);
					updateBreadcrumb(response.data.breadcrumb, $container);
					// Only update tree if not coming from tree click
					if (!skipTreeUpdate) {
						updateActiveFolderInTree(folderId, $container);
					}
					// Execute callback if provided
					if (typeof callback === 'function') {
						callback();
					}
				} else {
					$content.html('<div class="error">' + (lknwp_public_ajax.error_loading_text || 'Error loading contents') + '</div>');
				}
			},
			error: function () {
				$content.html('<div class="error">' + (lknwp_public_ajax.error_loading_text || 'Error loading contents') + '</div>');
			}
		});
	}

	function renderFolderContents(data, $container) {
		const $content = $container.find('.lknwp-filebrowser-content');
		const layout = $container.data('layout') || 'grid';

		$content.empty();

		const folders = data.folders || [];
		const files = data.files || [];

		if (folders.length === 0 && files.length === 0) {
			$content.html(`
				<div class="empty-folder">
					<i class="fas fa-folder-open"></i>
					<h3>${lknwp_public_ajax.empty_folder_text || 'This folder is empty'}</h3>
					<p>${lknwp_public_ajax.empty_folder_desc || 'No files or folders found in this location.'}</p>
				</div>
			`);
			return;
		}

		// Render folders
		folders.forEach(function (folder) {
			const folderElement = createFolderElement(folder, layout);
			$content.append(folderElement);
		});

		// Render files
		files.forEach(function (file) {
			const fileElement = createFileElement(file, layout);
			$content.append(fileElement);
		});
	}

	function createFolderElement(folder, layout) {
		const $element = $(`
			<div class="content-item folder" data-folder-id="${folder.id}" data-folder-name="${folder.name}">
				<i class="fas fa-folder"></i>
				<div class="content-item-name">${folder.name}</div>
				<div class="content-item-info">${lknwp_public_ajax.folder_text || 'Folder'}</div>
			</div>
		`);

		return $element;
	}

	function createFileElement(file, layout) {
		const fileIcon = getFileIcon(file.file_type);
		const fileSize = formatFileSize(file.file_size);

		if (layout === 'list') {
			const $element = $(`
				<div class="content-item file ${file.file_type}" data-file-id="${file.id}" data-file-url="${file.file_url}" data-file-type="${file.file_type}" data-folder-id="${file.folder_id}">
					<i class="${fileIcon}"></i>
					<button class="file-locate-btn" data-folder-id="${file.folder_id}" data-file-name="${file.original_name}" title="Abrir arquivo">
						<i class="fas fa-external-link-alt"></i>
					</button>
					<div class="content-item-name">${file.original_name}</div>
					<div class="content-item-info">${fileSize}</div>
					<button class="file-download-btn" data-file-url="${file.file_url}" data-file-name="${file.original_name}">
						${lknwp_public_ajax.download_text || 'DOWNLOAD'}
					</button>
				</div>
			`);
			return $element;
		} else {
			// Grid layout
			const $element = $(`
				<div class="content-item file ${file.file_type}" data-file-id="${file.id}" data-file-url="${file.file_url}" data-file-type="${file.file_type}" data-folder-id="${file.folder_id}">
					<i class="${fileIcon}"></i>
					<button class="file-locate-btn" data-folder-id="${file.folder_id}" data-file-name="${file.original_name}" title="Abrir arquivo">
						<i class="fas fa-external-link-alt"></i>
					</button>
					<div class="content-item-name">${file.original_name}</div>
					<div class="content-item-info">${fileSize}</div>
					<button class="file-download-btn" data-file-url="${file.file_url}" data-file-name="${file.original_name}">
						${lknwp_public_ajax.download_text || 'DOWNLOAD'}
					</button>
				</div>
			`);
			return $element;
		}
	}

	function performSearch(searchTerm) {
		const $container = $('.lknwp-filebrowser-public');
		const $content = $container.find('.lknwp-filebrowser-content');
		const $treeContainer = $container.find('#lknwp-folder-tree-public');

		isSearchMode = true;
		$('#lknwp-clear-search').show();

		// Clear file selections when performing search
		$treeContainer.find('.file-item-tree').removeClass('selected');
		$container.find('.content-item.file').removeClass('selected-in-tree');

		$content.html('<div class="loading"><i class="fas fa-spinner"></i> ' + (lknwp_public_ajax.searching_text || 'Searching...') + '</div>');

		$.ajax({
			url: lknwp_public_ajax.ajax_url,
			type: 'POST',
			data: {
				action: 'lknwp_frontend_search',
				nonce: nonce,
				search_term: searchTerm,
				folder_id: currentFolderId
			},
			success: function (response) {
				if (response.success) {
					renderSearchResults(response.data, $container);
				} else {
					$content.html('<div class="error">' + (lknwp_public_ajax.error_search_text || 'Error performing search') + '</div>');
				}
			},
			error: function () {
				$content.html('<div class="error">' + (lknwp_public_ajax.error_search_text || 'Error performing search') + '</div>');
			}
		});
	}

	function renderSearchResults(data, $container) {
		const $content = $container.find('.lknwp-filebrowser-content');
		const layout = $container.data('layout') || 'grid';

		$content.empty();

		// Add search results header
		const searchHeader = $(`
			<div class="search-results-header">
				<i class="fas fa-search"></i>
				${lknwp_public_ajax.search_results_text || 'Search results for'} "${data.search_term}" - ${lknwp_public_ajax.found_items_text || 'Found'} ${(data.folders.length + data.files.length)} ${lknwp_public_ajax.items_text || 'items'}
			</div>
		`);
		$content.append(searchHeader);

		const folders = data.folders || [];
		const files = data.files || [];

		if (folders.length === 0 && files.length === 0) {
			$content.append(`
				<div class="empty-folder">
					<i class="fas fa-search"></i>
					<h3>${lknwp_public_ajax.no_results_text || 'No results found'}</h3>
					<p>${lknwp_public_ajax.no_results_desc || 'Try adjusting your search terms.'}</p>
				</div>
			`);
			return;
		}

		// Create results container
		const $resultsContainer = $('<div class="search-results"></div>');

		// Apply the same layout class as the main content
		$resultsContainer.addClass(layout);

		// Render folders
		folders.forEach(function (folder) {
			const folderElement = createFolderElement(folder, layout);
			folderElement.addClass('search-result-folder');
			$resultsContainer.append(folderElement);
		});

		// Render files
		files.forEach(function (file) {
			const fileElement = createFileElement(file, layout);
			fileElement.addClass('search-result-file');
			$resultsContainer.append(fileElement);
		});

		$content.append($resultsContainer);
	}

	function clearSearch() {
		const $container = $('.lknwp-filebrowser-public');
		const $treeContainer = $container.find('#lknwp-folder-tree-public');

		$('#lknwp-search-input').val('');
		$('#lknwp-clear-search').hide();
		isSearchMode = false;

		// Clear file selections when clearing search
		$treeContainer.find('.file-item-tree').removeClass('selected');
		$container.find('.content-item.file').removeClass('selected-in-tree');

		// Reload current folder contents
		loadFolderContents(currentFolderId, $container);
	}

	function navigateToFolder(folderId, folderName) {
		const $container = $('.lknwp-filebrowser-public');
		const $treeContainer = $container.find('#lknwp-folder-tree-public');

		currentFolderId = folderId;

		// Clear search UI without reloading content
		if (isSearchMode) {
			$('#lknwp-search-input').val('');
			$('#lknwp-clear-search').hide();
			isSearchMode = false;
		}

		// Clear file selections when navigating to different folder
		$treeContainer.find('.file-item-tree').removeClass('selected');
		$container.find('.content-item.file').removeClass('selected-in-tree');

		// Update active folder in tree
		updateActiveFolderInTree(folderId, $container);

		loadFolderContents(folderId, $container);
	}

	function updateBreadcrumb(breadcrumb, $container) {
		const $breadcrumbContainer = $container.find('#lknwp-current-path');

		if (!breadcrumb || breadcrumb.length === 0) return;

		let breadcrumbHtml = '';

		breadcrumb.forEach(function (item, index) {
			if (index > 0) {
				breadcrumbHtml += '<span class="breadcrumb-separator">/</span>';
			}

			if (index === breadcrumb.length - 1) {
				// Current folder (not clickable)
				breadcrumbHtml += `<span class="breadcrumb-current">${item.name}</span>`;
			} else {
				// Clickable breadcrumb item
				breadcrumbHtml += `<a href="#" class="breadcrumb-item" data-folder-id="${item.id}">${item.name}</a>`;
			}
		});

		$breadcrumbContainer.html(breadcrumbHtml);
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

	function updateActiveFolderInTree(folderId, $container) {
		const $treeContainer = $container.find('#lknwp-folder-tree-public');
		if ($treeContainer.length === 0) return;

		// Remove active class from all folders
		$treeContainer.find('.folder-item-public').removeClass('active');

		// Find and activate the target folder
		const $targetFolder = $treeContainer.find(`.folder-item-public[data-folder-id="${folderId}"]`);
		if ($targetFolder.length === 0) return;

		$targetFolder.addClass('active');

		// Expand parent folders if necessary
		expandParentFolders($targetFolder, $treeContainer);
	}

	function expandParentFolders($targetFolder, $treeContainer) {
		const parentId = $targetFolder.data('parent-id');

		if (parentId && parentId !== 0) {
			// Find parent folder
			const $parentFolder = $treeContainer.find(`.folder-item-public[data-folder-id="${parentId}"]`);
			if ($parentFolder.length > 0) {
				// Expand parent folder if it has toggle button
				const $toggleBtn = $parentFolder.find('.folder-toggle-btn-public');
				if ($toggleBtn.length > 0) {
					const $icon = $toggleBtn.find('i');
					const $subfolders = $treeContainer.find(`.folder-item-public.child-folder-public[data-parent-id="${parentId}"]`);
					const $subfiles = $treeContainer.find(`.file-item-tree.child-file-tree[data-parent-id="${parentId}"]`);

					// Show subfolders and files if not already shown
					if (!$subfolders.hasClass('show')) {
						$subfolders.addClass('show');
						$subfiles.addClass('show');
						$icon.removeClass('fa-caret-right').addClass('fa-caret-down');
						$toggleBtn.attr('title', 'Esconder Subpastas');
					}
				}

				// Recursively expand parent's parents
				expandParentFolders($parentFolder, $treeContainer);
			}
		}
	}

	function hideSubfoldersRecursively(parentId, $treeContainer) {
		// Find direct children of this parent
		const $directChildren = $treeContainer.find(`.folder-item-public.child-folder-public[data-parent-id="${parentId}"]`);
		const $directFiles = $treeContainer.find(`.file-item-tree.child-file-tree[data-parent-id="${parentId}"]`);

		// Hide direct children
		$directChildren.removeClass('show');
		$directFiles.removeClass('show');

		// For each direct child, also hide their children recursively and reset their toggle buttons
		$directChildren.each(function () {
			const childId = $(this).data('folder-id');
			const $childToggleBtn = $(this).find('.folder-toggle-btn-public');

			// Reset toggle button state
			if ($childToggleBtn.length > 0) {
				const $childIcon = $childToggleBtn.find('i');
				$childIcon.removeClass('fa-caret-down').addClass('fa-caret-right');
				$childToggleBtn.attr('title', 'Mostrar Subpastas');
			}

			// Recursively hide children of this child
			hideSubfoldersRecursively(childId, $treeContainer);
		});
	}

	function loadFolderTree($container) {
		const $treeContainer = $container.find('#lknwp-folder-tree-public');
		if ($treeContainer.length === 0) return;

		$.ajax({
			url: lknwp_public_ajax.ajax_url,
			type: 'POST',
			data: {
				action: 'lknwp_frontend_get_all_folders',
				nonce: nonce
			},
			success: function (response) {
				if (response.success) {
					renderFolderTree(response.data.folders, response.data.files, $treeContainer, $container);
				} else {
					$treeContainer.html('<div class="error">' + (lknwp_public_ajax.error_folders_text || 'Error loading folders') + '</div>');
				}
			},
			error: function () {
				$treeContainer.html('<div class="error">' + (lknwp_public_ajax.error_folders_text || 'Error loading folders') + '</div>');
			}
		});
	}

	function renderFolderTree(folders, files, $treeContainer, $mainContainer) {
		$treeContainer.empty();

		// Add root folder (not active by default)
		const $rootItem = $('<div class="folder-item-public" data-folder-id="0" data-folder-name="Home">')
			.html('<div class="folder-item-content-public"><i class="fas fa-home"></i> Home</div>');
		$treeContainer.append($rootItem);

		if (folders && folders.length > 0) {
			renderFolderTreeRecursive(folders, files, $treeContainer, 0, $mainContainer);
		}

		// Set the initial active folder based on currentFolderId
		if (currentFolderId === 0) {
			$rootItem.addClass('active');
		} else {
			updateActiveFolderInTree(currentFolderId, $mainContainer);
		}

		// Add click handlers for folder navigation
		$treeContainer.on('click', '.folder-item-content-public', function (e) {
			e.preventDefault();
			const $folderItem = $(this).closest('.folder-item-public');
			const folderId = $folderItem.data('folder-id') || 0;
			const folderName = $folderItem.data('folder-name') || 'Home';

			// Update current folder ID
			currentFolderId = folderId;

			// Clear file selections when navigating to different folder
			$treeContainer.find('.file-item-tree').removeClass('selected');
			const $container = $treeContainer.closest('.lknwp-filebrowser-public');
			$container.find('.content-item.file').removeClass('selected-in-tree');

			// Update active folder
			$treeContainer.find('.folder-item-public').removeClass('active');
			$folderItem.addClass('active');

			// Load contents without updating tree (skip tree update since we already updated it)
			loadFolderContents(folderId, $mainContainer, true);
		});

		// Add toggle handlers for subfolders
		$treeContainer.on('click', '.folder-toggle-btn-public', function (e) {
			e.preventDefault();
			e.stopPropagation();

			const folderId = $(this).data('folder-id');
			const $btn = $(this);
			const $icon = $btn.find('i');
			const $subfolders = $treeContainer.find(`.folder-item-public.child-folder-public[data-parent-id="${folderId}"]`);
			const $subfiles = $treeContainer.find(`.file-item-tree.child-file-tree[data-parent-id="${folderId}"]`);

			// Check the icon state to determine if expanded or collapsed
			const isExpanded = $icon.hasClass('fa-caret-down');

			if (isExpanded) {
				// Hide subfolders and files recursively
				hideSubfoldersRecursively(folderId, $treeContainer);
				$subfiles.removeClass('show');
				$icon.removeClass('fa-caret-down').addClass('fa-caret-right');
				$btn.attr('title', 'Mostrar Subpastas');
			} else {
				// Show subfolders and files
				$subfolders.addClass('show');
				$subfiles.addClass('show');
				$icon.removeClass('fa-caret-right').addClass('fa-caret-down');
				$btn.attr('title', 'Esconder Subpastas');
			}
		});

		// Add click handlers for file navigation
		$treeContainer.on('click', '.file-item-content-tree', function (e) {
			e.preventDefault();
			const $fileItem = $(this).closest('.file-item-tree');
			const fileName = $fileItem.data('file-name');
			const parentFolderId = $fileItem.data('parent-folder-id');

			// Navigate to the folder containing the file
			currentFolderId = parentFolderId;

			// Update active folder
			$treeContainer.find('.folder-item-public').removeClass('active');
			$treeContainer.find(`[data-folder-id="${parentFolderId}"]`).addClass('active');

			// Update selected file - remove from all and add to current
			$treeContainer.find('.file-item-tree').removeClass('selected');
			$fileItem.addClass('selected');

			// Also remove and add selected styling from main content files
			const $container = $treeContainer.closest('.lknwp-filebrowser-public');
			$container.find('.content-item.file').removeClass('selected-in-tree');

			// Load folder contents with callback to apply selection after loading
			loadFolderContents(parentFolderId, $mainContainer, true, () => {
				// Add selected styling to the corresponding file in main content immediately after loading
				$mainContainer.find('.content-item.file').each(function () {
					const $mainFile = $(this);
					const mainFileName = $mainFile.find('.content-item-name').text().trim();
					const mainFolderId = $mainFile.data('folder-id');

					if (mainFileName === fileName && mainFolderId == parentFolderId) {
						$mainFile.addClass('selected-in-tree');
						// Scroll to the file smoothly
						$mainFile[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
					}
				});
			});
		});
	}

	function renderFolderTreeRecursive(allFolders, allFiles, $treeContainer, parentId, $mainContainer, level = 0) {
		const folders = allFolders.filter(f => f.parent_id == parentId);
		const files = allFiles.filter(f => f.folder_id == parentId);

		folders.forEach(function (folder) {
			// Check if folder has children (either subfolders or files)
			const hasSubfolders = allFolders.some(f => f.parent_id == folder.id);
			const hasFiles = allFiles.some(f => f.folder_id == folder.id);
			const hasChildren = hasSubfolders || hasFiles;

			// Create folder item HTML with toggle button if it has children
			let folderItemHtml;
			if (hasChildren) {
				folderItemHtml = `
					<div class="folder-item-content-public">
						<i class="fas fa-folder"></i> ${folder.name}
					</div>
					<button class="folder-toggle-btn-public" data-folder-id="${folder.id}" title="Mostrar Subpastas">
						<i class="fas fa-caret-right"></i>
					</button>
				`;
			} else {
				folderItemHtml = `
					<div class="folder-item-content-public">
						<i class="fas fa-folder"></i> ${folder.name}
					</div>
				`;
			}

			const $folderItem = $('<div class="folder-item-public' + (level > 0 ? ' child-folder-public' : '') + '" data-folder-id="' + folder.id + '" data-folder-name="' + folder.name + '"' + (level > 0 ? ' data-parent-id="' + parentId + '"' : '') + '>')
				.html(folderItemHtml)
				.css('padding-left', (20 * level + 20) + 'px');

			$treeContainer.append($folderItem);

			// Render files immediately after this folder (before processing subfolders)
			const folderFiles = allFiles.filter(f => f.folder_id == folder.id);
			folderFiles.forEach(function (file) {
				const fileExtension = file.original_name.split('.').pop().toLowerCase();
				const iconClass = getFileIcon(fileExtension);

				const $fileItem = $('<div class="file-item-tree child-file-tree" data-file-name="' + file.original_name + '" data-parent-folder-id="' + folder.id + '" data-parent-id="' + folder.id + '">')
					.html(`
						<div class="file-item-content-tree">
							<i class="${iconClass}"></i> ${file.original_name}
						</div>
					`)
					.css('padding-left', (20 * (level + 1) + 20) + 'px'); // Same level as child folders

				$treeContainer.append($fileItem);
			});

			// Then render subfolders recursively
			renderFolderTreeRecursive(allFolders, allFiles, $treeContainer, folder.id, $mainContainer, level + 1);
		});

		// Render root files (files in the root folder, parentId = 0)
		if (parentId === 0) {
			files.forEach(function (file) {
				const fileExtension = file.original_name.split('.').pop().toLowerCase();
				const iconClass = getFileIcon(fileExtension);

				const $fileItem = $('<div class="file-item-tree" data-file-name="' + file.original_name + '" data-parent-folder-id="' + parentId + '">')
					.html(`
						<div class="file-item-content-tree">
							<i class="${iconClass}"></i> ${file.original_name}
						</div>
					`)
					.css('padding-left', (20 * level + 40) + 'px'); // Extra padding for root files

				$treeContainer.append($fileItem);
			});
		}
	}	/**
	 * Get the appropriate icon class for a file extension
	 */
	function getFileIcon(extension) {
		switch (extension.toLowerCase()) {
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'gif':
			case 'webp':
				return 'fas fa-file-image';
			case 'pdf':
				return 'fas fa-file-pdf';
			case 'doc':
			case 'docx':
				return 'fas fa-file-word';
			case 'xls':
			case 'xlsx':
				return 'fas fa-file-excel';
			case 'ppt':
			case 'pptx':
				return 'fas fa-file-powerpoint';
			case 'txt':
				return 'fas fa-file-alt';
			case 'zip':
			case 'rar':
			case '7z':
				return 'fas fa-file-archive';
			case 'mp3':
			case 'wav':
			case 'ogg':
				return 'fas fa-file-audio';
			case 'mp4':
			case 'avi':
			case 'mov':
				return 'fas fa-file-video';
			default:
				return 'fas fa-file';
		}
	}

})(jQuery);
