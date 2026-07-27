<?php

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

if ( ! class_exists( 'Lkn_Filebrowser' ) ) {
	class_alias( LinkNacionalFilebrowser::class, 'Lkn_Filebrowser' );
}

if ( ! class_exists( 'Lkn_Filebrowser_Loader' ) ) {
	class_alias( LinkNacionalFilebrowserLoader::class, 'Lkn_Filebrowser_Loader' );
}

if ( ! class_exists( 'Lkn_Filebrowser_Activator' ) ) {
	class_alias( LinkNacionalFilebrowserActivator::class, 'Lkn_Filebrowser_Activator' );
}

if ( ! class_exists( 'Lkn_Filebrowser_Deactivator' ) ) {
	class_alias( LinkNacionalFilebrowserDeactivator::class, 'Lkn_Filebrowser_Deactivator' );
}

if ( ! class_exists( 'Lkn_Filebrowser_Admin' ) ) {
	class_alias( LinkNacionalFilebrowserAdmin::class, 'Lkn_Filebrowser_Admin' );
}

if ( ! class_exists( 'Lkn_Filebrowser_Public' ) ) {
	class_alias( LinkNacionalFilebrowserPublic::class, 'Lkn_Filebrowser_Public' );
}
