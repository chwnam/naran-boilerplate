#!/usr/bin/env php
<?php
if ( 'cli' !== PHP_SAPI ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

define( 'NBPC_CLI_ROOT', __DIR__ );
define( 'NBPC_ROOT', dirname( __DIR__ ) );
define( 'THE_GULS', 'cpbn' );
define( 'THE_SLUG', strrev( THE_GULS ) );

if ( ! defined( 'NBPC_CLI_TEST' ) ) {
	$app = new NBPC_CLI_App();
	$app->run();
}
