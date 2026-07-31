<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class aliases for backward compatibility.
 * Maps old class names to new PSR-4 namespaced classes.
 */

use LinkNacional\Filebrowser\Includes\LinkNacionalFilebrowser;
use LinkNacional\Filebrowser\Includes\LinkNacionalFilebrowserLoader;
use LinkNacional\Filebrowser\Includes\LinkNacionalFilebrowserActivator;
use LinkNacional\Filebrowser\Includes\LinkNacionalFilebrowserDeactivator;
use LinkNacional\Filebrowser\Admin\LinkNacionalFilebrowserAdmin;
use LinkNacional\Filebrowser\Public\LinkNacionalFilebrowserPublic;

if ( ! class_exists( 'Linknacional_Filebrowser' ) ) {
	class_alias( LinkNacionalFilebrowser::class, 'Linknacional_Filebrowser' );
}

if ( ! class_exists( 'Linknacional_Filebrowser_Loader' ) ) {
	class_alias( LinkNacionalFilebrowserLoader::class, 'Linknacional_Filebrowser_Loader' );
}

if ( ! class_exists( 'Linknacional_Filebrowser_Activator' ) ) {
	class_alias( LinkNacionalFilebrowserActivator::class, 'Linknacional_Filebrowser_Activator' );
}

if ( ! class_exists( 'Linknacional_Filebrowser_Deactivator' ) ) {
	class_alias( LinkNacionalFilebrowserDeactivator::class, 'Linknacional_Filebrowser_Deactivator' );
}

if ( ! class_exists( 'Linknacional_Filebrowser_Admin' ) ) {
	class_alias( LinkNacionalFilebrowserAdmin::class, 'Linknacional_Filebrowser_Admin' );
}

if ( ! class_exists( 'Linknacional_Filebrowser_Public' ) ) {
	class_alias( LinkNacionalFilebrowserPublic::class, 'Linknacional_Filebrowser_Public' );
}
