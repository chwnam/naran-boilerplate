#!/usr/bin/env php
<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 */

class NBPC_Sync_Version {
	/**
	 * Run version change.
	 *
	 * @param string $main_path Plugin main file path.
	 */
	public function sync( string $main_path ) {
		$this->check_file_permission( $main_path, 'Main file' );

		$composer = dirname( $main_path ) . '/composer.json';
		$package  = dirname( $main_path ) . '/package.json';

		// Detect version info from the main file.
		$target_version = $this->get_main_file_version( $main_path );
		if ( empty( $target_version ) ) {
			die( "[Error] Version info not found from the main file header comment.\n" );
		} else {
			echo "* Target version: {$target_version}\n";
		}

		// Change main file const or define version.
		$this->apply_change_main( $main_path, $target_version );

		// Change composer.json verision.
		$this->apply_change_json( $composer, $target_version, 'composer.json' );

		// Change package.json version.
		$this->apply_change_json( $package, $target_version, 'package.json' );
	}

	/**
	 * Check the file is really exists, readable, and writable.
	 *
	 * @param string $path
	 * @param string $display
	 */
	private function check_file_permission( string $path, string $display ) {
		if ( ! file_exists( $path ) || ! is_file( $path ) ) {
			die( "{$display} is not found." );
		} elseif ( ! is_readable( $path ) ) {
			die( "{$display} is not readable." );
		} elseif ( ! is_writable( $path ) ) {
			die( "{$display} is not writable." );
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
	 * Main file vesion change.
	 *
	 * @param string $main_path
	 * @param string $target_version
	 */
	private function apply_change_main( string $main_path, string $target_version ) {
		$content = $this->get_content( $main_path );
		$detect  = $this->detect_constant( $content );

		if ( $this->detected( $detect ) && $target_version !== $detect[0] ) {
			echo "* Fix main [const] version: {$detect[0]} ---> {$target_version}\n";
			$content = $this->replace_version( $content, $target_version, $detect );
			file_put_contents( $main_path, $content );
		} else {
			$detect = $this->detect_define( $content );
			if ( $this->detected( $detect ) && $target_version !== $detect[0] ) {
				echo "* Fix main [define] version: {$detect[0]} ---> {$target_version}\n";
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
	 */
	private function apply_change_json( string $json_path, string $target_version, string $file_name ) {
		$content = $this->get_content( $json_path );
		if ( $content ) {
			$this->check_file_permission( $json_path, $file_name );
			$detect = $this->detect_json( $content );
			if ( $this->detected( $detect ) && $target_version !== $detect[0] ) {
				echo "* Fix {$file_name} version: {$detect[0]} ---> {$target_version}\n";
				$content = $this->replace_version( $content, $target_version, $detect );
				file_put_contents( $json_path, $content );
			}
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
		} else {
			return '';
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

		$headers = $matches[1][0];
		$offset  = $matches[1][1];
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
			$version = $matches[1][0];
			$offset  = $matches[1][1];
			$len     = strlen( $version );
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
	 *
	 * @return string
	 */
	private function replace_version( string $content, string $version, array $detect ): string {
		if ( $this->detected( $detect ) ) {
			if (
				strlen( $content ) > $detect[1] + $detect[2] &&
				$detect[0] &&
				$detect[0] === substr( $content, $detect[1], $detect[2] )
			) {
				$before  = substr( $content, 0, $detect[1] );
				$after   = substr( $content, $detect[1] + $detect[2] );
				$content = $before . $version . $after;
			}
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
			$version = $matches[1][0];
			$offset  = $matches[1][1];
			$len     = strlen( $version );
		}

		return [ $version, $offset, $len ];
	}

	/**
	 * Detect version string from JSON.
	 *
	 * @param string $content
	 *
	 * @return array 0: string
	 *               1: offset
	 *               2: length
	 */
	private function detect_json( string $content ): array {
		if ( ! $content ) {
			return [ '', 0, 0 ];
		}

		$version = '';
		$offset  = 0;
		$len     = 0;

		if ( preg_match( '/"version"\s*:\s*"(.+)"/i', $content, $matches, PREG_OFFSET_CAPTURE ) ) {
			$version = $matches[1][0];
			$offset  = $matches[1][1];
			$len     = strlen( $version );
		}

		return [ $version, $offset, $len ];
	}
}

function help() {
	echo "\nNaran sync version\n";
	echo "==================\n\n";
	echo "Usage: sync-version.php {MAIN_FILE}\n\n";
	echo "       MAIN_FILE your plugin main file.\n\n";
}

if ( 'cli' === php_sapi_name() ) {
	if ( 2 === $argc ) {
		( new NBPC_Sync_Version() )->sync( realpath( $argv[1] ) );
	} else {
		help();
	}
}
