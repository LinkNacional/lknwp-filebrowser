<?php

/**
 * Class aliases for backward compatibility
 * Maps old class names to new PSR-4 namespaced classes
 */

use Lkn\WPFilebrowser\LknwpFilebrowser;
use Lkn\WPFilebrowser\LknwpFilebrowserLoader;
use Lkn\WPFilebrowser\LknwpFilebrowserI18n;
use Lkn\WPFilebrowser\LknwpFilebrowserActivator;
use Lkn\WPFilebrowser\LknwpFilebrowserDeactivator;
use Lkn\WPFilebrowser\Admin\LknwpFilebrowserAdmin;
use Lkn\WPFilebrowser\Public\LknwpFilebrowserPublic;

// Backward compatibility aliases
if (!class_exists('Lknwp_Filebrowser')) {
    class_alias(LknwpFilebrowser::class, 'Lknwp_Filebrowser');
}

if (!class_exists('Lknwp_Filebrowser_Loader')) {
    class_alias(LknwpFilebrowserLoader::class, 'Lknwp_Filebrowser_Loader');
}

if (!class_exists('Lknwp_Filebrowser_i18n')) {
    class_alias(LknwpFilebrowserI18n::class, 'Lknwp_Filebrowser_i18n');
}

if (!class_exists('Lknwp_Filebrowser_Activator')) {
    class_alias(LknwpFilebrowserActivator::class, 'Lknwp_Filebrowser_Activator');
}

if (!class_exists('Lknwp_Filebrowser_Deactivator')) {
    class_alias(LknwpFilebrowserDeactivator::class, 'Lknwp_Filebrowser_Deactivator');
}

if (!class_exists('Lknwp_Filebrowser_Admin')) {
    class_alias(LknwpFilebrowserAdmin::class, 'Lknwp_Filebrowser_Admin');
}

if (!class_exists('Lknwp_Filebrowser_Public')) {
    class_alias(LknwpFilebrowserPublic::class, 'Lknwp_Filebrowser_Public');
}
