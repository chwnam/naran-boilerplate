<?php
if ( ! class_exists( 'NBPC_CLI_Command_Slug_Change' ) ) {
	final class NBPC_CLI_Command_Slug_Change implements NBPC_CLI_Command {
		private NBPC_CLI_Regex_Replace $regex_replace;

		private NBPC_CLI_Textdomain_Replace $textdomain_replace;

		private string $old_slug;

		private string $new_slug;

		private string $old_textdomain;

		private string $new_textdomain;

		private string $root_dir = NBPC_ROOT;

		private array $subdirs = [];

		public static function get_command_name(): string {
			return 'slug-change';
		}

		public static function add_command( Console_CommandLine $parser ): void {
			$cmd = $parser->addCommand(
				self::get_command_name(),
				[ 'description' => 'Change slugs and textdomains found in your boilerplate codes.' ]
			);

			$the_slug = nbpc_cli_the_slug();

			$cmd->addArgument(
				'new_slug',
				[
					'description' => 'your new prefix slug. Accepts only lowercase alphabets, numbers, and hyphen.' .
					                 'The first character must be an alphabet. If omitted, prompt is used.',
					'multiple'    => false,
					'optional'    => true,
					'help_name'   => 'NEW_SLUG',
				]
			);

			$cmd->addArgument(
				'old_slug',
				[
					'description' => "current slug string. Defaults to '$the_slug'.",
					'multiple'    => false,
					'optional'    => true,
					'help_name'   => 'OLD_SLUG',
					'default'     => nbpc_cli_the_slug(),
				]
			);

			$cmd->addOption(
				'new_textdomain',
				[
					'short_name'  => '-t',
					'long_name'   => '--textdomain',
					'description' => 'you can enter an alternate new text domain. Defaults to the same as new slug.',
					'action'      => 'StoreString',
					'help_name'   => 'TEXTDOMAIN',
				]
			);

			$cmd->addOption(
				'old_textdomain',
				[
					'short_name'  => '-o',
					'long_name'   => '--old-textdomain',
					'description' => "the text domain is replaced from '$the_slug' by default, " .
					                 'but if it has already been replaced, you can specify the text domain separately.',
					'action'      => 'StoreString',
					'help_name'   => 'OLD_TEXTDOMAIN',
				]
			);

			$cmd->addOption(
				'subdirs',
				[
					'short_name'  => '-s',
					'long_name'   => '--subdirs',
					'description' => "target subdirectories. Use commas to enter multiple directories. " .
					                 "Defaults to 'cli, core, includes'.",
					'action'      => 'StoreString',
					'help_name'   => 'SUBDIRS',
					'default'     => 'cli, core, includes',
				]
			);

			$cmd->addOption(
				'yes',
				[
					'short_name'  => '-y',
					'long_name'   => '--yes',
					'description' => 'proceed without confirmation. Use it carefully.',
					'action'      => 'StoreTrue',
					'default'     => false,
				]
			);
		}

		/**
		 * Validate slug string.
		 *
		 * @throws RuntimeException
		 */
		public static function validate_slug( string $slug ): bool {
			/*
			 * 1. It accepts lowercase alphabets, numbers, dashes, and underscores.
			 * 2. The first character must be a lowercase alphabet.
			 * 3. A dash and underscore cannot be used more than once in a row, e.g. st__gx, hp--1t.
			 * 4. Prefix cannot end with a dash or an underscore.
			 * 5. Prefix cannot contain the slug (n-b-p-c), or the guls (cpbn).
			 * 6. Maximum length: 25
			 */
			$pattern  = '/^([a-z][a-z0-9]*)(([_\-])[a-z0-9]+)*$/';
			$the_slug = nbpc_cli_the_slug();
			$the_guls = nbpc_cli_the_guls();

			if ( ! preg_match( $pattern, $slug ) ) {
				throw new RuntimeException( "Slug `$slug` is invalid." );
			}

			if ( str_contains( $slug, $the_slug ) || str_contains( $slug, $the_guls ) ) {
				throw new RuntimeException( "Slug cannot contain '$the_slug' or '$the_guls'." );
			}

			if ( 25 < strlen( $slug ) ) {
				throw new RuntimeException( "Maximum length exceeded." );
			}

			return true;
		}

		/**
		 * Validate text domain string.
		 *
		 * @param string $text_domain Input text domain.
		 *
		 * @return bool
		 * @throws RuntimeException
		 */
		public static function validate_textdomain( string $text_domain ): bool {
			/*
			 * 1. Lowercase alphabets, numbers, dashes, and undercores only.
			 * 2. Starts with alphabets, and numbers.
			 * 3. Ends with alphabets, and numbers.
			 */
			$pattern  = '/^[a-z\d][a-z\d_\-]+[a-z\d]$/';
			$the_slug = nbpc_cli_the_slug();
			$the_guls = nbpc_cli_the_guls();

			if ( ! preg_match( $pattern, $text_domain ) ) {
				throw new RuntimeException( "Only lowercase alphabets, numbers, dashes, and undercores are allowed for textdomain." );
			}

			if ( str_contains( $text_domain, $the_slug ) || str_contains( $text_domain, $the_guls ) ) {
				throw new RuntimeException( "Text domain cannot contain '$the_slug' or '$the_guls'." );
			}

			if ( 25 < strlen( $text_domain ) ) {
				throw new RuntimeException( "Text domain is too long." );
			}

			return true;
		}

		/** Run this command */
		public function run( Console_CommandLine_Result $parsed ): void {
			$this->initialize( $parsed );
			$this->recursive_change_file_contents();
			$this->recursive_change_file_names();
			$this->change_localization_files();
			$this->update_dot_slug();
		}

		/** Initialize this command */
		public function initialize( Console_CommandLine_Result $parsed ): void {
			do {
				if ( empty( $parsed->args['new_slug'] ) ) {
					$this->new_slug = $this->slug_prompt();
				} else {
					$this->new_slug = $parsed->args['new_slug'];
				}
				$this->old_slug       = $parsed->args['old_slug'];
				$this->old_textdomain = $parsed->options['old_textdomain'] ?: $this->old_slug;
				$this->new_textdomain = $parsed->options['new_textdomain'] ?: $this->new_slug;

				if ( $parsed->options['subdirs'] ) {
					$subdirs = array_filter( array_map( 'trim', explode( ',', $parsed->options['subdirs'] ) ) );
					if ( $subdirs ) {
						$this->subdirs = array_unique( [ ...$this->subdirs, ...$subdirs ] );
					}
				}

				if ( nbpc_cli_the_slug() !== $this->old_slug ) {
					self::validate_slug( $this->old_slug );
				}
				self::validate_slug( $this->new_slug );

				if ( nbpc_cli_the_slug() !== $this->old_textdomain ) {
					self::validate_textdomain( $this->old_textdomain );
				}
				self::validate_textdomain( $this->new_textdomain );

				if ( $parsed->options['yes'] ) {
					$confirmed = true;
				} else {
					$confirmed = nbpc_cli_confirm(
						sprintf(
							"Slug:       from `%s` to `%s`.\n" .
							"Textdomain: from `%s` to `%s`.\n" .
							"Are you sure?",
							$this->old_slug,
							$this->new_slug,
							$this->old_textdomain,
							$this->new_textdomain
						)
					);
				}
			} while ( ! $confirmed );

			$this->regex_replace      = new NBPC_CLI_Regex_Replace( $this->old_slug, $this->new_slug );
			$this->textdomain_replace = new NBPC_CLI_Textdomain_Replace( $this->old_textdomain, $this->new_textdomain );
		}

		/** Change file contents recusively. */
		public function recursive_change_file_contents() {
			$root_dir = $this->get_root_dir();
			$root_len = strlen( $this->get_root_dir() );

			foreach ( $this->subdirs as $subdir ) {
				$iterator = new RegexIterator(
					new RecursiveIteratorIterator(
						new RecursiveDirectoryIterator( $root_dir . '/' . $subdir )
					),
					'/\.(php|md)$/i',
					RegexIterator::MATCH
				);

				foreach ( $iterator as $info ) {
					/** @var SplFileInfo $info */
					echo substr( $info->getPathname(), $root_len + 1 ) . PHP_EOL;
					$this->change_file_content( $info->getPathname() );
				}
			}

			$other_files = [
				$root_dir . '/composer.json',
				$root_dir . '/custom.dic',
				$root_dir . '/package.json',
				$root_dir . '/phpunit.xml',
				$root_dir . '/style.css',
			];

			foreach ( glob( $root_dir . '/*.php' ) as $item ) {
				$other_files[] = $item;
			}

			foreach ( $other_files as $file ) {
				echo substr( $file, $root_len + 1 ) . PHP_EOL;
				$this->change_file_content( $file );
			}
		}

		/** Change one file content. */
		public function change_file_content( string $file_path, string $output_path = '' ) {
			if ( ! file_exists( $file_path ) ) {
				return;
			}

			$content = file_get_contents( $file_path );
			if ( ! $content ) {
				return;
			}

			if ( str_ends_with( $content, '.php' ) && $this->old_textdomain !== $this->old_slug ) {
				// Token extracting is required only when textdomain is not the same as slug.
				$content = $this->textdomain_replace->replace( $content );
			}
			$content = $this->regex_replace->replace( $content );

			if ( $output_path ) {
				file_put_contents( $output_path, $content );
			} else {
				file_put_contents( $file_path, $content );
			}
		}

		/** Change file names recursively. */
		public function recursive_change_file_names() {
			$root_dir = $this->get_root_dir();
			$root_len = strlen( $root_dir );
			$old      = str_replace( '_', '-', $this->old_slug );
			$new      = str_replace( '_', '-', $this->new_slug );

			$pattern = "/^(abstract|class|interface|trait)-$old-(.+)$/";

			foreach ( $this->subdirs as $subdir ) {
				$iterator = new RegexIterator(
					new RecursiveIteratorIterator(
						new RecursiveDirectoryIterator( $root_dir . '/' . $subdir )
					),
					'/\.php$/i',
					RegexIterator::MATCH
				);

				foreach ( $iterator as $info ) {
					/** @var SplFileInfo $info */
					$base = $info->getBasename();
					$path = $info->getPath();

					if ( preg_match( $pattern, $base, $matches ) ) {
						$new_base = "$matches[1]-$new-$matches[2]";
						$old_path = $info->getRealPath();
						$new_path = "$path/$new_base";

						rename( $old_path, $new_path );

						$relative_from = substr( $old_path, $root_len + 1 );
						$relative_to   = substr( $new_path, $root_len + 1 );

						echo "Rename: $relative_from ==> $relative_to" . PHP_EOL;
					}
				}
			}
		}

		public function change_localization_files(): void {
			$dir      = $this->get_root_dir() . '/languages';
			$root_len = strlen( $this->get_root_dir() );

			if ( file_exists( $dir ) && is_dir( $dir ) && is_executable( $dir ) ) {
				$iterator = new RegexIterator(
					new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ) ),
					'/\.(pot?|mo)$/i',
					RegexIterator::MATCH
				);

				foreach ( $iterator as $info ) {
					/** @var SplFileInfo $info */
					file_put_contents(
						$info->getRealPath(),
						$this->regex_replace->replace( file_get_contents( $info->getRealPath() ) )
					);

					$old_path = $info->getRealPath();
					$new_base = str_replace( $this->old_textdomain, $this->new_textdomain, $info->getBasename() );
					$new_path = $info->getPath() . "/$new_base";

					if ( $old_path !== $new_path ) {
						rename( $old_path, $new_path );
						$relative_from = substr( $old_path, $root_len + 1 );
						$relative_to   = substr( $new_path, $root_len + 1 );
						echo "Renamed: $relative_from ==> $relative_to" . PHP_EOL;
					}
				}
			}
		}

		public function update_dot_slug() {
			nbpc_cli_update_dot_slug(
				[
					'slug'       => $this->new_slug,
					'textdomain' => $this->new_textdomain,
				]
			);
		}

		public function get_root_dir(): string {
			return $this->root_dir;
		}

		public function set_root_dir( string $root_dir ) {
			$this->root_dir = $root_dir;
		}

		public function get_subdirs(): array {
			return $this->subdirs;
		}

		public function set_subdirs( array $subdirs ) {
			$this->subdirs = $subdirs;
		}

		/** Prompt slug string */
		private function slug_prompt(): string {
			do {
				$confirmed = false;
				$new_slug  = nbpc_cli_prompt( 'Please enter your new slug (Enter \'exit\' to skip):' );
				if ( 'exit' === $new_slug ) {
					echo 'Changing slug is skipped.' . PHP_EOL;
					exit;
				}
				try {
					self::validate_slug( $new_slug );
				} catch ( RuntimeException $e ) {
					echo $e->getMessage() . PHP_EOL;
					continue;
				}
				$confirmed = true;
			} while ( ! $confirmed );

			return $new_slug;
		}
	}
}
