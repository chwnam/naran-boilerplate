#!/usr/bin/env php
<?php

/**
 * NBPC: Prefix change
 *
 * Change all prefix strings.
 */
class NBPC_Prefix_Changer {
	private string $root_directory;

	private int $root_len;

	private string $old_prefix;

	private string $new_prefix;

	private array $subdirs = [ 'core', 'includes' ];

	public function __construct( string $root_directory, string $old_prefix, string $new_prefix ) {
		$this->root_directory = rtrim( realpath( $root_directory ), '\\/' );
		$this->root_len       = strlen( $this->root_directory );
		$this->old_prefix     = $old_prefix;
		$this->new_prefix     = $new_prefix;

		if (
			! file_exists( $this->root_directory ) ||
			! is_dir( $this->root_directory ) ||
			! is_executable( $this->root_directory ) ||
			! is_writable( $this->root_directory ) ||
			! is_readable( $this->root_directory )
		) {
			throw new RuntimeException(
				"$this->root_directory is not a directory, or does not have enough permission."
			);
		}

		if ( $this->old_prefix === $this->new_prefix ) {
			throw new RuntimeException( 'Old and new prefixes are the same.' );
		}

		if ( $this->old_prefix !== 'nbpc' ) {
			self::validate_prefix( $this->old_prefix );
		}

		self::validate_prefix( $this->new_prefix );
	}

	/**
	 * @param string $prefix
	 *
	 * @return bool
	 * @throws RuntimeException
	 */
	public static function validate_prefix( string $prefix ): bool {
		/*
		 * 1. It accepts lowercase alphabets, numbers, dashes, and underscores.
		 * 2. The first character must be a lowercase alphabet.
		 * 3. A dash and underscore cannot be used more than once in a row, e.g. st__gx, hp--1t.
		 * 4. Prefix cannot end with a dash or an underscore.
		 * 5. Prefix cannot contain 'nbpc', or 'cpbn'.
		 * 6. Maximum length: 25
		 */
		$pattern = '/^([a-z][a-z0-9]*)(([_\-])[a-z0-9]+)*$/';

		if ( ! preg_match( $pattern, $prefix ) ) {
			throw new RuntimeException( "Prefix `$prefix` is invalid." );
		} elseif ( false !== strpos( $prefix, 'nbpc' ) || false !== strpos( $prefix, 'cpbn' ) ) {
			throw new RuntimeException( "Prefix cannot contain 'nbpc' or 'cpbn'." );
		} elseif ( 25 < strlen( $prefix ) ) {
			throw new RuntimeException( "Maximum length exceeded." );
        }

		return true;
	}

	public function change_php_file_name_prefixes() {
		$old_prefix = $this->lower_dash( $this->old_prefix );
		$new_prefix = $this->lower_dash( $this->new_prefix );
		$pattern    = "/^(abstract|class|interface|trait)-$old_prefix-(.+)$/";

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
					$new_base = "$matches[1]-$new_prefix-$matches[2]";
					$old_path = $info->getRealPath();
					$new_path = "$path/$new_base";

					rename( $old_path, $new_path );

					$relative_from = substr( $old_path, $this->root_len + 1 );
					$relative_to   = substr( $new_path, $this->root_len + 1 );

					echo "Renamed: $relative_from ==> $relative_to" . PHP_EOL;
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

		$root_files = [
			'composer.json',
			'custom.dic',
			'index.php',
			'package.json',
			'phpunit.xml',
			'uninstall.php',
		];

		foreach ( $root_files as $root_file ) {
			$this->code_patch( $this->root_directory . '/' . $root_file );
		}
	}

	public function change_language_files() {
		$old_prefix = $this->lower_dash( $this->old_prefix );
		$new_prefix = $this->lower_dash( $this->new_prefix );
		$dir        = "$this->root_directory/languages";

		if ( file_exists( $dir ) && is_dir( $dir ) && is_executable( $dir ) ) {
			$iterator = new RegexIterator(
				new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ) ),
				'/\.(pot?|mo)$/i',
				RegexIterator::MATCH
			);

			foreach ( $iterator as $info ) {
				/** @var SplFileInfo $info */
				$this->code_patch( $info->getRealPath() );

				$base = $info->getBasename();
				$path = $info->getPath();

				$new_base = str_replace( $old_prefix, $new_prefix, $base );
				$old_path = $info->getRealPath();
				$new_path = "$path/$new_base";

				if ( $old_path !== $new_path ) {
					rename( $old_path, $new_path );

					$relative_from = substr( $old_path, $this->root_len + 1 );
					$relative_to   = substr( $new_path, $this->root_len + 1 );

					echo "Renamed: $relative_from ==> $relative_to" . PHP_EOL;
				}
			}
		}
	}

	private function code_patch( string $path ) {
		$content = file_get_contents( $path );

		if ( $content ) {
			$search = [
				"'" . $this->lower_dash( $this->old_prefix ) . "'",
				'"' . $this->lower_dash( $this->old_prefix ) . '"',
				$this->lower_underscore( $this->old_prefix ),
				$this->upper_underscore( $this->old_prefix ),
			];

			$replace = [
				"'" . $this->lower_dash( $this->new_prefix ) . "'",
				'"' . $this->lower_dash( $this->new_prefix ) . '"',
				$this->lower_underscore( $this->new_prefix ),
				$this->upper_underscore( $this->new_prefix ),
			];

			$content = str_replace( $search, $replace, $content );

			file_put_contents( $path, $content );

			$relative = substr( $path, $this->root_len + 1 );

			echo "Code patched: $relative" . PHP_EOL;
		}
	}

	private function lower_dash( string $string ): string {
		return strtolower( str_replace( '_', '-', $string ) );
	}

	private function lower_underscore( string $string ): string {
		return strtolower( str_replace( '-', '_', $string ) );
	}

	private function upper_underscore( string $string ): string {
		return strtoupper( str_replace( '-', '_', $string ) );
	}
}


function help() {
	echo "\nNaran boilerplate code prefix changer\n";
	echo "=====================================\n\n";
	echo "Usage: prefix-change.php [NEW_PREFIX] [OLD_PREFIX]\n\n";
	echo "       NEW_PREFIX your new prefix string. Accepts only lowercase alphabets, numbers, and hyphen. The first character must be an alphabet.\n\n";
	echo "       OLD_PREFIX current prefix string. Defaults to 'nbpc'.\n\n";
}


function confirm( string $message ): bool {
	echo $message . " [Y/n] ";
	return 'y' === trim( strtolower( fgets( STDIN ) ) );
}


function get_new_prefix(): string {
	while ( true ) {
		try {
			echo 'Please enter your new prefix (Enter \'exit\' to skip): ';
			$new_prefix = trim( fgets( STDIN ) );
			if ( 'exit' === $new_prefix ) {
				echo 'prefix-change.php skipped.' . PHP_EOL;
				exit;
			} elseif ( true === NBPC_Prefix_Changer::validate_prefix( $new_prefix ) ) {
				break;
			}
		} catch ( RuntimeException $e ) {
			echo 'Error: ' . $e->getMessage() . PHP_EOL;
		}
	}

	return $new_prefix;
}

if ( 'cli' === php_sapi_name() ) {
	$root_dir = dirname( __DIR__ );

	if ( 1 === $argc ) {
		question:
		$new_prefix = get_new_prefix();
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
		if ( confirm( "Replace prefix from `$old_prefix` to `$new_prefix`. Are you sure?" ) ) {
			$change = new NBPC_Prefix_Changer( $root_dir, $old_prefix, $new_prefix );
			$change->change_source_codes();
			$change->change_php_file_name_prefixes();
			$change->change_language_files();
		} elseif ( 1 === $argc ) {
			goto question;
		}
	} catch ( RuntimeException $e ) {
		die( 'Error: ' . $e->getMessage() . PHP_EOL );
	}
}
