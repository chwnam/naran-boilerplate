<?php

if ( ! ( defined( 'WP_UNINSTALL_PLUGIN' ) && WP_UNINSTALL_PLUGIN ) ) {
	exit;
}

require_once __DIR__ . '/index.php';
require_once __DIR__ . '/core/uninstall-functions.php';

$uninstall = nbpc()->registers->uninstall;
if ( $uninstall ) {
	$uninstall->register();
}

// You may use these functions to purge data.
// nbpc_cleanup_option();
// nbpc_cleanup_meta();
// nbpc_cleanup_terms();
// nbpc_cleanup_posts();
