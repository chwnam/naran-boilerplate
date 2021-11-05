#!/usr/bin/env php
<?php

/**
 * NBPC: Prefix change
 *
 * Change all prefix strings.
 *
 * @noinspection PhpIllegalPsrClassPathInspection
 */
class NBPC_Prefix_Changer {
	private string $root_directory = '';

	private int $root_len;

	private string $old_prefix = '';

	private string $new_prefix = '';

	private array $subdirs = [ 'core', 'includes' ];

	public function __construct( string $root_directory, string $old_prefix, string $new_prefix ) {
		$this->root_directory = rtrim( realpath( $root_directory ), '\\/' );
		$this->root_len       = strlen( $this->root_directory );
		$this->old_prefix     = strtolower( trim( $old_prefix, '_-' ) );
		$this->new_prefix     = strtolower( trim( $new_prefix, '-_' ) );

		if (
			! file_exists( $this->root_directory ) ||
			! is_dir( $this->root_directory ) ||
			! is_executable( $this->root_directory ) ||
			! is_writable( $this->root_directory ) ||
			! is_readable( $this->root_directory )
		) {
			throw new RuntimeException(
				"{$this->root_directory} is not a directory, or does not have enough permission."
			);
		}

		$pattern = '/^[a-z0-9]+$/';

		if ( ! preg_match( $pattern, $this->old_prefix ) || ! preg_match( $pattern, $this->new_prefix ) ) {
			throw new RuntimeException( 'Prefixes allow lowercase alphabets and numbers only.' );
		} elseif ( $this->old_prefix === $this->new_prefix ) {
			throw new RuntimeException( 'Old and new prefixes are the same.' );
		}
	}

	public function change_php_file_name_prefixes() {
		$pattern = "/^(abstract|class|interface|trait)-{$this->old_prefix}-(.+)$/";

		foreach ( $this->subdirs as $subdir ) {
			$iterator = new RegexIterator(
				new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator( $this->root_directory . '/' . $subdir )
				),
				'/\.php$/i',
				RegexIterator::MATCH
			);

			foreach ( $iterator as $info ) {
				/** @var SplFileInfo $info */
				$base = $info->getBasename();
				$path = $info->getPath();

				if ( preg_match( $pattern, $base, $matches ) ) {
					$new_base = "$matches[1]-{$this->new_prefix}-$matches[2]";
					$old_path = $info->getRealPath();
					$new_path = "{$path}/{$new_base}";

					rename( $old_path, $new_path );

					$relative_from = substr( $old_path, $this->root_len + 1 );
					$relative_to   = substr( $new_path, $this->root_len + 1 );
					echo "Renamed: {$relative_from} ==> {$relative_to}\n";
				}
			}
		}
	}

	public function change_source_codes() {
		foreach ( $this->subdirs as $subdir ) {
			$iterator = new RegexIterator(
				new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator( $this->root_directory . '/' . $subdir )
				),
				'/\.(php|md)$/i',
				RegexIterator::MATCH
			);

			foreach ( $iterator as $info ) {
				/** @var SplFileInfo $info */
				$this->code_patch( $info->getRealPath() );
			}
		}

		$this->code_patch( $this->root_directory . '/index.php' );
	}

	private function code_patch( string $path ) {
		$content = file_get_contents( $path );

		if ( $content ) {
			$content = str_replace(
				[ strtoupper( $this->old_prefix ), $this->old_prefix ],
				[ strtoupper( $this->new_prefix ), $this->new_prefix ],
				$content
			);

			file_put_contents( $path, $content );

			$relative = substr( $path, $this->root_len + 1 );
			echo "Code patched: {$relative}\n";
		}
	}
}


function help() {
	echo "\nNaran boilerplate code prefix changer\n";
	echo "=====================================\n\n";
	echo "Usage: prefix-change.php [NEW_PREFIX] [OLD_PREFIX]\n\n";
	echo "       NEW_PREFIX your new prefix string. Accepts only lowercase alphabets, and numbers.\n\n";
	echo "       OLD_PREFIX current prefix string. Defaults to 'nbpc'.\n\n";
}


function confirm( string $message ): bool {
	echo $message . " [Y/n] ";
	return 'y' === trim( strtolower( readline() ) );
}


if ( 'cli' === php_sapi_name() ) {
	$root_dir = dirname( __DIR__ );

	if ( 1 === $argc ) {
		echo 'Please input your new prefix: ';
		$new_prefix = trim( fgets( STDIN ) );
		$old_prefix = 'nbpc';
	} elseif ( 2 === $argc ) {
		$new_prefix = $argv[1];
		$old_prefix = 'nbpc';
	} elseif ( 3 === $argc ) {
		$new_prefix = $argv[1];
		$old_prefix = $argv[2];
	} else {
		help();
		exit;
	}

	try {
		if ( confirm( "Replace prefix from `{$old_prefix}` to `{$new_prefix}`. Are you sure?" ) ) {
			$change = new NBPC_Prefix_Changer( $root_dir, $old_prefix, $new_prefix );
			$change->change_source_codes();
			$change->change_php_file_name_prefixes();
		}
	} catch ( RuntimeException $e ) {
		die( 'Error: ' . $e->getMessage() . PHP_EOL );
	}
}
