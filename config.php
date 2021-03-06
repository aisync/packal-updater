<?php

if ( 'cli-server' === php_sapi_name() ) {
	$vars = json_decode( file_get_contents( '/tmp/com.packal/config.json' ), true );
	foreach( $vars as $var => $val ) {
		$$var = $val;
	}
	$_SERVER['alfred_workflow_data'] = $data_dir;
	$_SERVER['alfred_workflow_cache'] = $cache_dir;
	$_SERVER['HOME'] = $HOME;
	$_SERVER['alfred_version'] = $alfred_version;
	$_SERVER['alfred_preferences'] = $alfred_preferences;
}

// These are just here for now...
$bundle = isset( $_SERVER['alfred_workflow_bundleid'] ) ? $_SERVER['alfred_workflow_bundleid'] : 'com.packal';
$home   = $_SERVER['HOME'];

function guess_alfred_version() {
	if ( isset( $_SERVER['alfred_version'] ) ) {
		return floor( floatval( $_SERVER['alfred_version'] ) );
	}
	if ( file_exists( '/Applications/Alfred 3.app' ) ) {
			return 3;
	}
	if ( file_exists( "{$_SERVER['HOME']}/Applications/Alfred 3.app" ) ) {
			return 3;
	}
	if ( file_exists( '/Applications/Alfred 2.app' ) ) {
		return 2;
	}
	if ( file_exists( "{$_SERVER['HOME']}/Applications/Alfred 2.app" ) ) {
			return 2;
	}
	throw new Exception( 'Cannot guess what version of Alfred to use' );
}

function data() {
	global $bundle, $home;

	if ( isset( $_SERVER['alfred_workflow_data'] ) ) {
		return $_SERVER['alfred_workflow_data'];
	}

	$v = guess_alfred_version();
	return "{$home}/Library/Application Support/Alfred {$v}/Workflow Data/{$bundle}";
}

function cache() {
	global $bundle, $home;

	if ( isset( $_SERVER['alfred_workflow_cache'] ) ) {
		return $_SERVER['alfred_workflow_cache'];
	}

	$v = guess_alfred_version();
	return "{$home}/Library/Caches/com.runningwithcrayons.Alfred-{$v}/Workflow Data/{$bundle}";
}

define( '__DATA__', data() );
define( '__CACHE__', cache() );
define( '__BUNDLE__', $bundle );
define( '__HOME__', $home );

$environments = [
	'development' => 'http://localhost:3000', 	  // Local Passenger Server
	'dev-staging' => 'http://packal.dev', 		  // Local nginx proxying to Passenger
	'staging'     => 'https://mellifluously.org', // Staging Server
	'production'  => 'https://www.packal.org', 	  // Actual Production (not setup yet)
];

// Turns on Development Code (extra logging, etc...)
define( 'DEVELOPMENT_TESTING', true );
define( '__LEGACY__', true );

// The current environment is defined in environment.txt
define( 'BUNDLE',            'com.packal' );

define( 'CACHE',             __CACHE__ );
define( 'DATA',              __DATA__ );
define( 'ERROR_ICON',        '/System/Library/CoreServices/CoreTypes.bundle/Contents/Resources/Unsupported.icns' );

if ( ! isset( $_SERVER['alfred_workflow_cache'] ) ) {
	$_SERVER['alfred_workflow_cache'] = __CACHE__;
}
if ( ! isset( $_SERVER['alfred_workflow_data'] ) ) {
	$_SERVER['alfred_workflow_data'] = __DATA__;
}
if ( ! isset( $_SERVER['alfred_workflow_bundleid'] ) ) {
	$_SERVER['alfred_workflow_bundleid'] = $bundle;
}

foreach ( [ __CACHE__, __DATA__ ] as $dir ) :
	if ( ! file_exists( $dir ) ) {
		mkdir( $dir, 0775, true );
	}
endforeach;
