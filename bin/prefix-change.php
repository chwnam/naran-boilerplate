#!/usr/bin/env php
<?php

require dirname( __DIR__ ) . '/vendor/autoload.php';

/**
 * NBPC: Prefix change
 *
 * Change all prefix strings.
 */
class NBPC_Prefix_Changer {
	/**
	 * Our root directory.
	 *
	 * @var string
	 */
	private string $root_directory;

	/**
	 * Root directory string length.
	 *
	 * @var int
	 */
	private int $root_len;

	/**
	 * Old prefix.
	 *
	 * @var string
	 */
	private string $old_prefix;

	/**
	 * New prefix.
	 *
	 * @var string
	 */
	private string $new_prefix;

	/**
	 * New text domain.
	 *
	 * @var string
	 */
	private string $old_text_domain;

	/**
	 * Old text domain.
	 *
	 * @var string
	 */
	private string $new_text_domain;

	/**
	 * Subdirectories.
	 *
	 * @var array|string[]
	 */
	private array $subdirs = [ 'core', 'includes' ];

	/**
	 * PHP parser instance.
	 *
	 * @var PhpParser\Parser
	 */
	private PhpParser\Parser $parser;

	/**
	 * PHP node traverser instance.
	 *
	 * @var PhpParser\NodeTraverser
	 */
	private PhpParser\NodeTraverser $traverser;

	/**
	 * PHP node visitor instance.
	 *
	 * @var PhpParser\NodeVisitor
	 */
	private PhpParser\NodeVisitor $visitor;

	/**
	 * Constructor
	 *
	 * @param string $root_directory  Root directory.
	 * @param string $old_prefix      Old prefix.
	 * @param string $new_prefix      New prefix.
	 * @param string $old_text_domain Old text domain.
	 * @param string $new_text_domain New text domain.
	 */
	public function __construct(
		string $root_directory,
		string $old_prefix,
		string $new_prefix,
		string $old_text_domain,
		string $new_text_domain
	) {
		$this->root_directory  = rtrim( realpath( $root_directory ), '\\/' );
		$this->root_len        = strlen( $this->root_directory );
		$this->old_prefix      = $old_prefix;
		$this->new_prefix      = $new_prefix;
		$this->old_text_domain = $old_text_domain;
		$this->new_text_domain = $new_text_domain;

		ini_set( 'xdebug.max_nesting_level', 3000 );

		$this->parser = ( new PhpParser\ParserFactory() )->create(
			PhpParser\ParserFactory::PREFER_PHP7,
			new PhpParser\Lexer\Emulative( [ 'usedAttributes' => [ 'startFilePos' ] ] )
		);

		$this->visitor = new class extends PhpParser\NodeVisitorAbstract {
			/**
			 * Discovered text domain information
			 *
			 * @var array{int, string}>
			 */
			private array $offsets = [];

			/**
			 * Return offset information
			 *
			 * @return array<string, array{int, string}>
			 */
			public function get_offsets(): array {
				return $this->offsets;
			}

			public function beforeTraverse( array $nodes ) {
				$this->offsets = [];
			}

			public function leaveNode( PhpParser\Node $node ) {
				$this->keepTextDomain( $node );
			}

			/**
			 * Check if node is text domain related function calling.
			 *
			 * @param PhpParser\Node $node
			 *
			 * @return void
			 */
			private function keepTextDomain( PhpParser\Node $node ): void {
				$found = $node instanceof PhpParser\Node\Expr\FuncCall &&
				         isset( $node->name->parts ) &&
				         (
					         (
						         2 === count( $node->args ) &&
						         in_array(
							         $node->name->parts[0],
							         [
								         '__',
								         '_e',
								         'esc_attr__',
								         'esc_attr_e',
								         'esc_html__',
								         'esc_html_e',
							         ],
							         true
						         )
					         ) ||
					         (
						         3 === count( $node->args ) &&
						         in_array(
							         $node->name->parts[0],
							         [
								         '_ex',
								         '_n_noop',
								         '_x',
								         'esc_attr_x',
								         'esc_html_x',
							         ],
							         true
						         )
					         ) ||
					         (
						         4 === count( $node->args ) &&
						         in_array(
							         $node->name->parts[0],
							         [ '_n', '_nx_loop' ],
							         true
						         )
					         ) ||
					         ( 5 === count( $node->args ) && $node->name->parts[0] === '_nx' )
				         );

				if ( $found ) {
					$arg = $node->args[ count( $node->args ) - 1 ];
					if ( $arg->value instanceof PhpParser\Node\Scalar\String_ ) {
						$this->offsets[] = [
							$arg->getAttribute( 'startFilePos' ) + 1, // String starts with a quotation mark.
							$arg->value->value,
						];
					}
				}
			}
		};

		$this->traverser = new PhpParser\NodeTraverser();
		$this->traverser->addVisitor( $this->visitor );

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

		if ( $this->old_text_domain === $this->new_text_domain ) {
			throw new RuntimeException( 'Old and new text domains are the same.' );
		}

		if ( $this->old_prefix !== 'nbpc' ) {
			self::validate_prefix( $this->old_prefix );
		}

		self::validate_prefix( $this->new_prefix );

		if ( $this->old_text_domain !== 'nbpc' ) {
			self::validate_text_domain( $this->old_text_domain );
		}

		self::validate_text_domain( $this->new_text_domain );
	}

	/**
	 * Validate prefix string.
	 *
	 * @param string $prefix Input prefix.
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
		}

		if ( false !== strpos( $prefix, 'nbpc' ) || false !== strpos( $prefix, 'cpbn' ) ) {
			throw new RuntimeException( "Prefix cannot contain 'nbpc' or 'cpbn'." );
		}

		if ( 25 < strlen( $prefix ) ) {
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
	public static function validate_text_domain( string $text_domain ): bool {
		$pattern = '/^[a-z\d\-]+$/';

		if ( ! preg_match( $pattern, $text_domain ) ) {
			throw new RuntimeException( "Text domain must use dashes, not underscores, and be lowercase." );
		}

		if ( false !== strpos( $text_domain, 'nbpc' ) || false !== strpos( $text_domain, 'cpbn' ) ) {
			throw new RuntimeException( "Text domain cannot contain 'nbpc' or 'cpbn'." );
		}

		if ( 25 < strlen( $text_domain ) ) {
			throw new RuntimeException( "Text domain is too long." );
		}

		return true;
	}

	/**
	 * Convert into lowercase dash string.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function lower_dash( string $string ): string {
		return strtolower( str_replace( '_', '-', $string ) );
	}

	/**
	 * Convert into lowercase underscore string.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function lower_underscore( string $string ): string {
		return strtolower( str_replace( '-', '_', $string ) );
	}

	/**
	 * Convert into uppercase underscore string.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function upper_underscore( string $string ): string {
		return strtoupper( str_replace( '-', '_', $string ) );
	}

	/**
	 * Rename source files.
	 *
	 * Change all prefix parts, e.g. class-nbpc-something.php.
	 *
	 * @return void
	 */
	public function change_php_file_name_prefixes(): void {
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

	/**
	 * Change PHP source codes, and some supportive configuration files.
	 *
	 * @return void
	 */
	public function change_files_contents(): void {
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
				if ( $this->new_text_domain !== $this->new_prefix ) {
					$this->substitute_textdomain( $info->getRealPath() );
				}
				$this->substitute_prefix( $info->getRealPath() );
			}
		}

		$root_files = [
			'composer.json',
			'custom.dic',
			'index.php',
			'nbpc.php',
			'package.json',
			'phpunit.xml',
			'uninstall.php',
		];

		foreach ( $root_files as $root_file ) {
			$this->substitute_prefix( $this->root_directory . '/' . $root_file );
		}
	}

	/**
	 * Rename language files.
	 *
	 * @return void
	 */
	public function change_language_files(): void {
		$old_prefix = self::lower_dash( $this->old_prefix );
		$new_prefix = self::lower_dash( $this->new_prefix );
		$dir        = "$this->root_directory/languages";

		if ( file_exists( $dir ) && is_dir( $dir ) && is_executable( $dir ) ) {
			$iterator = new RegexIterator(
				new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ) ),
				'/\.(pot?|mo)$/i',
				RegexIterator::MATCH
			);

			foreach ( $iterator as $info ) {
				/** @var SplFileInfo $info */
				$this->substitute_prefix( $info->getRealPath() );

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

	/**
	 * Replace prefix strings.
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	private function substitute_prefix( string $path ): void {
		$content = file_get_contents( $path );

		if ( $content ) {
			$search = [
				self::lower_underscore( $this->old_prefix ),
				self::upper_underscore( $this->old_prefix ),
			];

			$replace = [
				self::lower_underscore( $this->new_prefix ),
				self::upper_underscore( $this->new_prefix ),
			];

			$content = str_replace( $search, $replace, $content );

			file_put_contents( $path, $content );

			$relative = substr( $path, $this->root_len + 1 );

			echo "Prefix replaced: $relative" . PHP_EOL;
		}
	}

	/**
	 * Patch PHP codes.
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	private function substitute_textdomain( string $path ): void {
		if ( 'php' !== pathinfo( $path, PATHINFO_EXTENSION ) ) {
			return;
		}

		$content = file_get_contents( $path );
		if ( empty( $content ) ) {
			return;
		}

		// Traverse PHP code and keep symbols that we need.
		$this->traverser->traverse( $this->parser->parse( $content ) );

		// Get text domain strings information.
		$offsets = $this->visitor->get_offsets();

		// Reverse sort by offset.
		usort( $offsets, function ( $a, $b ) {
			$oa = $a[0];
			$ob = $b[0];
			return $oa === $ob ? 0 : ( $oa > $ob ? - 1 : 1 );
		} );

		foreach ( $offsets as [$offset, $text_domain] ) {
			if ( $text_domain === $this->old_text_domain ) {
				$before  = substr( $content, 0, $offset );
				$after   = substr( $content, $offset + strlen( $text_domain ) );
				$content = $before . $this->new_text_domain . $after;
			}
		}

		if ( $offsets ) {
			echo $path . PHP_EOL;
			echo $content;
			echo PHP_EOL;
		}
	}
}


function get_console_width(): int {
	try {
		if ( 'Windows' === PHP_OS_FAMILY ) {
			$a1 = shell_exec( 'MODE' );
			/*
			 * Status for device CON:
             * ----------------------
             * Lines:          300
             * Columns:        80
             * Keyboard rate:  31
             * Keyboard delay: 1
             * Code page:      437
			 */
			$arr = explode( "\n", $a1 );
			$col = trim( explode( ':', $arr[4] )[1] ); // TODO: test in windows.
		} else {
			$col = exec( 'tput cols' );
		}
	} catch ( Exception $ex ) {
		$col = 80;
	}

	return (int) $col;
}


function confirm( string $message ): bool {
	echo $message . " [Y/n] ";
	return 'y' === strtolower( trim( fgets( STDIN ) ) );
}


function get_new_prefix(): string {
	while ( true ) {
		try {
			echo 'Please enter your new prefix (Enter \'exit\' to skip): ';
			$new_prefix = strtolower( trim( fgets( STDIN ) ) );

			if ( 'exit' === $new_prefix ) {
				echo 'prefix-change.php skipped.' . PHP_EOL;
				exit;
			}

			if ( true === NBPC_Prefix_Changer::validate_prefix( $new_prefix ) ) {
				break;
			}
		} catch ( RuntimeException $e ) {
			echo 'Error: ' . $e->getMessage() . PHP_EOL;
		}
	}

	return $new_prefix;
}


function get_parser(): Console_CommandLine {
	$parser                       = new Console_CommandLine();
	$parser->description          = 'Prefix and text domain changer.';
	$parser->renderer->line_width = get_console_width();

	$parser->addArgument(
		'new_prefix',
		[
			'description' => 'Your new prefix string. Accepts only lowercase alphabets, numbers, and hyphen. The first character must be an alphabet.',
			'multiple'    => false,
			'optional'    => true,
			'help_name'   => 'NEW_PREFIX',
		]
	);

	$parser->addArgument(
		'old_prefix',
		[
			'description' => 'Current prefix string. Defaults to \'nbpc\'.',
			'multiple'    => false,
			'optional'    => true,
			'help_name'   => 'NEW_PREFIX',
			'default'     => 'nbpc',
		]
	);

	$parser->addOption(
		'new_text_domain',
		[
			'short_name'  => '-t',
			'long_name'   => '--text-domain',
			'description' => 'You can enter an alternate new text domain.',
			'action'      => 'StoreString',
			'help_name'   => 'NEW_TEXT_DOMAIN',
		]
	);

	$parser->addOption(
		'old_text_domain',
		[
			'short_name'  => '-o',
			'long_name'   => '--old-text-domain',
			'description' => 'The text domain is replaced in \'nbpc\' by default, but if it has already been replaced, you can specify the old text domain separately.',
			'action'      => 'StoreString',
			'help_name'   => 'OLD_TEXT_DOMAIN',
		]
	);

	$parser->addOption(
		'yes',
		[
			'short_name'  => '-y',
			'long_name'   => '--yes',
			'description' => 'Do not confirm before changing. Use it carefully.',
			'action'      => 'StoreTrue',
			'default'     => false,
		]
	);

	return $parser;
}

if ( PHP_SAPI !== 'cli' ) {
	die( 'This is CLI only script.' );
}

try {
	if ( 1 === $argc ) {
		question:
		$new_prefix      = get_new_prefix();
		$old_prefix      = 'nbpc';
		$new_text_domain = NBPC_Prefix_Changer::lower_dash( $new_prefix );
		$old_text_domain = 'nbpc';
		$yes             = false;
	} else {
		$parser = get_parser();
		$result = $parser->parse();

		$new_prefix      = $result->args['new_prefix'];
		$old_prefix      = $result->args['old_prefix'];
		$new_text_domain = NBPC_Prefix_Changer::lower_dash( $result->options['new_text_domain'] ?: $new_prefix );
		$old_text_domain = NBPC_Prefix_Changer::lower_dash( $result->options['old_text_domain'] ?: $old_prefix );
		$yes             = $result->options['yes'];
	}

	if ( $new_text_domain === $new_prefix && $old_text_domain === $old_prefix ) {
		$confirm_message = "Replace prefix from `$old_prefix` to `$new_prefix`";
	} else {
		$confirm_message = "Replace prefix from `$old_prefix` to `$new_prefix`, and replace text doamin from `$old_text_domain` to `$new_text_domain`";
	}

	if ( $yes || confirm( "$confirm_message. Are you sure?" ) ) {
		$change = new NBPC_Prefix_Changer( dirname( __DIR__ ), $old_prefix, $new_prefix, $old_text_domain, $new_text_domain );
		$change->change_files_contents();
		$change->change_php_file_name_prefixes();
		$change->change_language_files();
	} elseif ( 1 === $argc ) {
		goto question;
	}
} catch ( Exception $e ) {
	die( $e->getMessage() );
}
