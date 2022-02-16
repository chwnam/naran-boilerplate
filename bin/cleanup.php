#!/usr/bin/env php
<?php

/**
 * Recursively remove files and directories.
 *
 * @param string $dir
 */
function rrmdir( string $dir ) {
	if ( is_dir( $dir ) ) {
		foreach ( scandir( $dir ) as $object ) {
			if ( $object !== '.' && $object !== '..' ) {
				$path = "$dir/$object";
				if ( is_dir( $path ) ) {
					rrmdir( $path );
				} else {
					unlink( $path );
					echo "Removed $path\n";
				}
			}
		}
		rmdir( $dir );
		echo "Removed $dir\n";
	} elseif ( is_file( $dir ) ) {
		unlink( $dir );
		echo "Removed $dir\n";
	}
}

if ( 'cli' === PHP_SAPI ) {
	$root_dir  = dirname( __DIR__ );
	$tests_dir = $root_dir . '/tests';

	if ( ! is_dir( $tests_dir ) ) {
		echo 'Test directory not found.' . PHP_EOL;
		exit( 1 );
	}

	$excludes = [
		'.',
		'..',
		'bootstrap.php',
		'test-sample.php',
	];

	// Remove test files.
	foreach ( scandir( $tests_dir ) as $item ) {
		if ( ! in_array( $item, $excludes, true ) ) {
			rrmdir( "$tests_dir/$item" );
		}
	}

	// Remove changelog content.
	$changelog = "$root_dir/CHANGELOG";
	if ( file_exists( $changelog ) ) {
		file_put_contents( $changelog, '' );
		echo "CHANGELOG removed.\n";
	}
}
