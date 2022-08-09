<?php

if ( ! class_exists( 'NBPC_CLI_Command_Sync_Version' ) ) {
	final class NBPC_CLI_Command_Sync_Version implements NBPC_CLI_Command {
		public static function get_command_name(): string {
			return 'sync-version';
		}

		public static function add_command( Console_CommandLine $parser ): void {
			$cmd = $parser->addCommand(
				self::get_command_name(),
				[ 'description' => "Sync project's version across files." ]
			);

			$cmd->addOption(
				'main_file',
				[
					'short_name'  => '-m',
					'long_name'   => '--main-file',
					'description' => "Specify the project's main file, a .php or .css containing the header comment. " .
					                 "Defaults to an empty string, discovering automatically.",
					'help_name'   => 'FILE',
					'optional'    => true,
					'default'     => '',
				]
			);

			$cmd->addOption(
				'slug',
				[
					'short_name'  => '-s',
					'long_name'   => '--slug',
					'description' => "Override slug. Defaults to the 'slug' value from the .slug.json.",
					'help_name'   => 'SLUG',
					'optional'    => true,
					'default'     => '',
				]
			);

			$cmd->addOption(
				'new_version',
				[
					'short_name'  => '-n',
					'long_name'   => '--new-version',
					'description' => "Override project's version. Defaults to the main file's version header value.",
					'help_name'   => 'VER',
					'optional'    => true,
					'default'     => '',
				]
			);
		}

		/**
		 * @throws JsonException
		 */
		public function run( Console_CommandLine_Result $parsed ): void {
			// Get main file and version.
			if ( $parsed->options['main_file'] ) {
				// Main file from option.
				$main_file = $parsed->options['main_file'];
				$result    = NBPC_CLI_Header_Detect::get_header_data( $main_file );
			} else {
				// Discover.
				$result    = NBPC_CLI_Header_Detect::detect( NBPC_ROOT );
				$main_file = $result['path'] ?? '';
			}

			if ( $parsed->options['new_version'] ) {
				$target_version = $parsed->options['new_version'];
			} else {
				$target_version = $result['version'] ?? '';
			}

			if ( NBPC_CLI_Header_Detect::TYPE_THEME === $result['type'] && $main_file ) {
				$functions_file = dirname( $main_file ) . '/functions.php';
			} else {
				$functions_file = '';
			}

			// Get slug information.
			$slug_data = nbpc_cli_get_dot_slug();
			$slug      = $slug_data['slug'] ?? nbpc_cli_the_slug();

			if ( ! file_exists( $main_file ) ) {
				throw new RuntimeException( 'Main file not found.' );
			}

			if ( ! $target_version ) {
				throw new RuntimeException( 'Version string not found.' );
			}

			if ( ! $slug ) {
				throw new RuntimeException( 'Slug string not found from .slug.json.' );
			}

			echo '* Main file: ' . substr( $main_file, strlen( NBPC_ROOT ) + 1 ) . PHP_EOL;
			echo '* Target version: ' . $target_version . PHP_EOL;
			echo '* Slug: ' . $slug . PHP_EOL;

			$files = [
				$main_file,
				$functions_file,
				NBPC_ROOT . '/composer.json',
				NBPC_ROOT . '/package.json',
			];

			foreach ( array_filter( $files ) as $file ) {
				if ( file_exists( $file ) ) {
					if ( str_ends_with( $file, '.php' ) ) {
						$this->apply_change_php( $file, $slug, $target_version );
					} elseif ( str_ends_with( $file, '.css' ) ) {
						$this->apply_change_css( $file, $slug, $target_version );
					} elseif ( str_ends_with( $file, '.json' ) ) {
						$this->apply_change_json( $file, $target_version );
					}
				}
			}
		}

		public function apply_change_php(
			string $php_file,
			string $slug,
			string $target_version
		): void {
			$extractor = new NBPC_CLI_Token_Extract( new NBPC_CLI_Node_Visitor_Const() );
			$content   = file_get_contents( $php_file ) ?: '';

			if ( $content ) {
				$content = $this->replace_header_version(
					$content,
					$slug,
					$target_version
				);

				/**
				 * Extract const, or define and replace version.
				 *
				 * @var NBPC_CLI_Token_Const[] $tokens
				 */
				$tokens = $extractor->extract( $content );
				$const  = strtoupper( $slug ) . '_VERSION';

				foreach ( $tokens as $token ) {
					if ( $token->name->value === $const && $token->value->value !== $target_version ) {
						echo "* Sync constant version: {$token->value->value} ---> $target_version\n";
						$content = substr_replace(
							$content,
							$target_version,
							$token->value->start_pos,
							$token->value->end_pos - $token->value->start_pos
						);
						break;
					}
				}

				file_put_contents( $php_file, $content );
			}
		}

		public function apply_change_css( string $css_path, string $slug, string $target_version ): void {
			$content = $this->replace_header_version(
				file_get_contents( $css_path ) ?: '',
				$slug,
				$target_version
			);
			file_put_contents( $css_path, $content );
		}

		/**
		 * @throws JsonException
		 */
		public function apply_change_json( string $json_path, string $target_version ): void {
			$content = json_decode( file_get_contents( $json_path ), true, 512, JSON_THROW_ON_ERROR );

			if ( is_array( $content ) ) {
				$version = $content['version'] ?? '{empty version}';
				if ( $version !== $target_version ) {
					$file_name = substr( $json_path, strlen( NBPC_ROOT ) + 1 );
					echo "* Sync $file_name version: $version ---> $target_version\n";
				}
				$content['version'] = $target_version;

				$this->save_as_json( $json_path, $content );
			}
		}

		private function replace_header_version( string $content, string $slug, string $target_version ): string {
			if ( $content ) {
				// Extract version from header and change it.
				[ $value, $offset ] = NBPC_CLI_Header_Detect::extract_header( $content, 'Version' );
				if ( $value && $offset && $value !== $target_version ) {
					echo "* Sync header version: $value ---> $target_version\n";
					$content = substr_replace( $content, $target_version, $offset, strlen( $value ) );
				}

				// Extract 'CPBN Version' value and change if it is not slug-changed.
				[ $value, $offset ] = NBPC_CLI_Header_Detect::extract_header( $content, 'CPBN Version' );
				if ( nbpc_cli_the_slug() === $slug && $offset && $value !== $target_version ) {
					echo "* Sync CPBN header version: $value ---> $target_version\n";
					$content = substr_replace( $content, $target_version, $offset, strlen( $value ) );
				}
			}

			return $content;
		}

		/** @throws JsonException */
		private function save_as_json( string $path, array|object $content ): void {
			$dump = json_encode(
				$content,
				JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
			);

			if ( $dump ) {
				// JSON_PRETTY_PRINT gets indent of 4.
				$dump = preg_replace_callback(
					'/^(\s+)(.+)/m',
					fn( array $match ) => str_pad( '', strlen( $match[1] ) / 2 ) . $match[2],
					$dump
				);
				$dump .= PHP_EOL;

				file_put_contents( $path, $dump );
			}
		}
	}
}
