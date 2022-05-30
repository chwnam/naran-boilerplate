<?php
/**
 * Remove *.hot-update.{js,json,js.map}
 *
 * @param string $target
 *
 * @return void
 */
function remove_hot_update( string $target ) {
	$target = rtrim( $target, '/' );
	foreach ( glob( "$target/*.hot-update.{js,js.map,json}", GLOB_BRACE ) as $file ) {
		unlink( $file );
	}
}

if ( 'cli' === PHP_SAPI ) {
	$target = dirname( __DIR__ ) . '/assets/js/dist';
	if ( file_exists( $target ) && is_dir( $target ) && is_executable( $target ) ) {
		remove_hot_update( $target );
	}
}
