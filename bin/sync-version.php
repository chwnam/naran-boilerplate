#!/usr/bin/env php
<?php

class NBPC_Sync_Version {
	/**
	 * Run version change.
	 *
	 * @param string $main_path Plugin main file path.
	 *
	 * @throws JsonException
	 */
	public function sync( string $main_path ): void {
		$this->check_file_permission( $main_path, 'Main file' );

		$composer = dirname( $main_path ) . '/composer.json';
		$package  = dirname( $main_path ) . '/package.json';

		// Detect version info from the main file.
		$target_version = $this->get_main_file_version( $main_path );
		if ( empty( $target_version ) ) {
			die( "[Error] Version info not found from the main file header comment.\n" );
		}

		echo "* Target version: $target_version\n";

		// Change main file const or define version.
		$this->apply_change_main( $main_path, $target_version );

		// Change composer.json version.
		$this->apply_change_json( $composer, $target_version, 'composer.json' );

		// Change package.json version.
		$this->apply_change_json( $package, $target_version, 'package.json' );

		// Change CPBN version. Only applied when developing NBPC.
		$this->apply_change_cpbn( $main_path, $target_version );
	}

	/**
	 * Check the file is really exists, readable, and writable.
	 *
	 * @param string $path
	 * @param string $display
	 *
	 * @return void
	 */
	private function check_file_permission( string $path, string $display ): void {
		if ( ! file_exists( $path ) || ! is_file( $path ) ) {
			die( "$display is not found.\n" );
		}

		if ( ! is_readable( $path ) ) {
			die( "$display is not readable.\n" );
		}

		if ( ! is_writable( $path ) ) {
			die( "$display is not writable.\n" );
		}
	}

	/**
	 * Get version from main file.
	 *
	 * @param string $main_file
	 *
	 * @return string
	 */
	private function get_main_file_version( string $main_file ): string {
		$content = $this->get_content( $main_file );
		$detect  = $this->detect_comment( $content );

		return $detect[0];
	}

	/**
	 * Fix CPBN version string, if it is not modified.
	 *
	 * @param string $main_path      Main file path.
	 * @param string $target_version Target version string.
	 */
	private function apply_change_cpbn( string $main_path, string $target_version ): void {
		$dir  = basename( dirname( $main_path ) );
		$base = basename( $main_path );

		// Check directory name and main file name.
		if ( 'naran-boilerplate' !== $dir || 'index.php' !== $base ) {
			return;
		}

		$content = $this->get_content( $main_path );

		// Check constants
		if (
			! preg_match( '/^const NBPC_MAIN_FILE/m', $content ) ||
			! preg_match( '/^const NBPC_VERSION/m', $content )
		) {
			return;
		}

		// Get CPBN version
		if ( preg_match( '/^ \* CPBN version:\s+(\S+)$/m', $content, $match, PREG_OFFSET_CAPTURE ) ) {
			$cpbn_version = rtrim( $match[1][0] );
			$offset       = $match[1][1];
			$length       = strlen( $cpbn_version );
			$detected     = [ $cpbn_version, $offset, $length ];

			if ( $cpbn_version !== $target_version ) {
				echo "* Fix CPBN version: $cpbn_version ---> $target_version\n";
				$content = $this->replace_version( $content, $target_version, $detected );
				file_put_contents( $main_path, $content );
			}
		}
	}

	/**
	 * Main file version change.
	 *
	 * @param string $main_path
	 * @param string $target_version
	 *
	 * @return void
	 */
	private function apply_change_main( string $main_path, string $target_version ): void {
		$content = $this->get_content( $main_path );
		$detect  = $this->detect_constant( $content );

		if ( $this->detected( $detect ) && $target_version !== $detect[0] ) {
			echo "* Fix main [const] version: $detect[0] ---> $target_version\n";
			$content = $this->replace_version( $content, $target_version, $detect );
			file_put_contents( $main_path, $content );
		} else {
			$detect = $this->detect_define( $content );
			if ( $this->detected( $detect ) && $target_version !== $detect[0] ) {
				echo "* Fix main [define] version: $detect[0] ---> $target_version\n";
				$content = $this->replace_version( $content, $target_version, $detect );
				file_put_contents( $main_path, $content );
			}
		}
	}

	/**
	 * JSON file version change.
	 *
	 * @param string $json_path      'composer.json' path.
	 * @param string $target_version Version to change.
	 * @param string $file_name      File name to display.
	 *
	 * @return void
	 * @throws JsonException
	 */
	private function apply_change_json( string $json_path, string $target_version, string $file_name ): void {
		$this->check_file_permission( $json_path, $file_name );
		$content = json_decode( $this->get_content( $json_path ), true, 512, JSON_THROW_ON_ERROR );

		if ( is_array( $content ) ) {
			$version = $content['version'] ?? '{empty version}';
			if ( $version !== $target_version ) {
				echo "* Fix $file_name version: $version ---> $target_version\n";
			}
			$content['version'] = $target_version;
			$this->save_as_json( $json_path, $content );
		}
	}

	/**
	 * Grab file content.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	private function get_content( string $path ): string {
		if ( file_exists( $path ) && is_readable( $path ) ) {
			return file_get_contents( $path );
		}

		return '';
	}

	/**
	 * Save array as JSON file.
	 *
	 * @param string $path
	 * @param array  $content
	 *
	 * @return void
	 * @throws JsonException
	 */
	private function save_as_json( string $path, array $content ): void {
		$dump = json_encode( $content, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

		if ( $dump ) {
			// JSON_PRETTY_PRINT gets indent of 4.
			$dump = preg_replace_callback(
				'/^(\s+)(.+)/m',
				static function ( array $match ) { return str_pad( '', strlen( $match[1] ) / 2 ) . $match[2]; },
				$dump
			);
			$dump .= PHP_EOL;

			file_put_contents( $path, $dump );
		}
	}

	/**
	 * Detect version string from comment string.
	 *
	 * @param string $content
	 *
	 * @return array 0: string
	 *               1: offset
	 *               2: length
	 */
	private function detect_comment( string $content ): array {
		if ( ! $content ) {
			return [ '', 0, 0 ];
		}

		$m = preg_match( ';/\*(.+?)\*/;ms', $content, $matches, PREG_OFFSET_CAPTURE );
		if ( ! $m ) {
			return [ '', 0, 0 ];
		}

		[ $headers, $offset ] = $matches[1];

		$len     = 0;
		$version = '';

		if ( preg_match( '/^\s*\*?\s*version\s*:\s*(.+)$/im', $headers, $matches, PREG_OFFSET_CAPTURE ) ) {
			$version = $matches[1][0];
			$offset  += $matches[1][1];
			$len     = strlen( $version );
		}

		return [ $version, $offset, $len ];
	}

	/**
	 * Detect version string from define().
	 *
	 * {PREFIX}_VERSION
	 *
	 * @param string $content
	 *
	 * @return array 0: string
	 *               1: offset
	 *               2: length
	 */
	private function detect_constant( string $content ): array {
		if ( ! $content ) {
			return [ '', 0, 0 ];
		}

		$version = '';
		$offset  = 0;
		$len     = 0;

		$m = preg_match(
			'/const\s+[A-Za-z0-9]+_VERSION\s*=\s*[\'\"](.+)[\'\"]\s*;/',
			$content,
			$matches,
			PREG_OFFSET_CAPTURE
		);

		if ( $m ) {
			[ $version, $offset ] = $matches[1];

			$len = strlen( $version );
		}

		return [ $version, $offset, $len ];
	}

	/**
	 * Check if the version string is detected.
	 *
	 * @param array $detect
	 *
	 * @return bool
	 */
	private function detected( array $detect ): bool {
		return count( $detect ) === 3 && ! empty( $detect[0] ) && $detect[1] > 0 && $detect[2] > 0;
	}

	/**
	 * Replace version string.
	 *
	 * @param string $content Input string.
	 * @param string $version Target version.
	 * @param array  $detect  Detect info.
	 *                        0: string.
	 *                        1: offeset.
	 *                        2: length.
	 *
	 * @return string
	 */
	private function replace_version( string $content, string $version, array $detect ): string {
		if (
			$this->detected( $detect ) &&
			strlen( $content ) > $detect[1] + $detect[2] &&
			$detect[0] === substr( $content, $detect[1], $detect[2] ) ) {
			$before  = substr( $content, 0, $detect[1] );
			$after   = substr( $content, $detect[1] + $detect[2] );
			$content = $before . $version . $after;
		}

		return $content;
	}

	/**
	 * Detect version string from define().
	 *
	 * {PREFIX}_VERSION
	 *
	 * @param string $content
	 *
	 * @return array 0: string
	 *               1: offset
	 *               2: length
	 */
	private function detect_define( string $content ): array {
		if ( ! $content ) {
			return [ '', 0, 0 ];
		}

		$version = '';
		$offset  = 0;
		$len     = 0;

		$m = preg_match(
			'/define\s*\(\s*[\'\"][A-Za-z0-9]+_VERSION[\'\"]\s*,\s*[\'\"](.+)[\'\"]\s*\)\s*;/',
			$content,
			$matches,
			PREG_OFFSET_CAPTURE
		);

		if ( $m ) {
			[ $version, $offset ] = $matches[1];

			$len = strlen( $version );
		}

		return [ $version, $offset, $len ];
	}
}

function help() {
	echo "Naran sync version\n";
	echo "==================\n";
	echo "Usage: sync-version.php [MAIN_FILE]\n\n";
	echo "  MAIN_FILE    Your plugin main file name, such as index.php, or functions.php.\n";
	echo "               You may leave it blank, and the script will find it for you.\n\n";
}

/**
 * Return the main path.
 *
 * @return string
 */
function find_main_path(): string {
	$root = dirname( __DIR__ );
	$base = basename( $root );

	$candiates = [
		"$root/style.css", // Theme main file.
		"$root/index.php", // Plugin main file.
		"$root/$base.php", // Plugin alternative main file.
	];

	foreach ( $candiates as $candiate ) {
		if ( file_exists( $candiate ) ) {
			return $candiate;
		}
	}

	die( "Error! Main file not found." );
}

if ( 'cli' === PHP_SAPI ) {
    if ( 2 === $argc && '-h' === $argv[1] ) {
        help();
        exit;
    }

	if ( 2 === $argc || 1 === $argc ) {
		try {
			if ( isset( $argv[1] ) ) {
				$main_path = realpath( dirname( __DIR__ ) . '/' . $argv[1] );
			} else {
				$main_path = find_main_path();
                echo "* Found main path: $main_path\n";
			}
			( new NBPC_Sync_Version() )->sync( $main_path );
		} catch ( JsonException $e ) {
			die( $e->getMessage() );
		}
	}
}
