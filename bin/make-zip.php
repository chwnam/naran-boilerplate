<?php

class Make_Zip {
	private array $exclude_dirs;

	private array $exclude_files;

	private string $plugin_dir;

	private string $zip_base;

	private string $zip_file;

	private int $target_dir_len;

	private int $plugin_dir_len;

	public function __construct() {
		$this->exclude_dirs = [
			'.git',
			'.idea',
			'bin',
			'node_modules',
			'tests',
		];

		$this->exclude_files = [
			'.',
			'..',
			'.gitignore',
			'.phpcs.xml.dist',
			'.travis.yml',
			'phpunit.xml',
			'README.md',
		];

		$target_dir = dirname( __DIR__, 2 );

		$this->plugin_dir = dirname( __DIR__ );
		$this->zip_base   = basename( $this->plugin_dir );
		$this->zip_file   = "$target_dir/$this->zip_base.zip";

		$this->target_dir_len = strlen( $target_dir ) + 1;
		$this->plugin_dir_len = strlen( $this->plugin_dir ) + 1;
	}

	public function make(): void {
		if ( file_exists( $this->zip_file ) ) {
			unlink( $this->zip_file );
		}

		$zip = new ZipArchive();
		$zip->open( $this->zip_file, ZipArchive::CREATE );
		$zip->addEmptyDir( $this->zip_base );

		foreach ( $this->get_files() as $abspath ) {
			$zip->addFile( $abspath, $this->get_target_relpath( $abspath ) );
		}

		// Add the root README.md.
		$root_readme = "$this->plugin_dir/README.md";
		if ( file_exists( $root_readme ) ) {
			$zip->addFile( $root_readme, $this->get_target_relpath( $root_readme ) );
		}

		$zip->close();
	}

	private function get_files(): Generator {
		$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $this->plugin_dir ) );

		foreach ( $iterator as $iter ) {
			/** @var SplFileInfo $iter */
			$excluded_dir  = $this->is_excluded_dir( $iter );
			$excluded_file = $this->is_excluded_file( $iter );
			$is_vendor     = $this->is_vendor( $iter );

			if ( $excluded_dir || $excluded_file || $is_vendor ) {
				continue;
			}

			yield $iter->getPathname();
		}
	}

	private function get_target_relpath( string $abspath ): string {
		return substr( $abspath, $this->target_dir_len );
	}

	private function get_plugin_relpath( string $abspath ): string {
		return substr( $abspath, $this->plugin_dir_len );
	}

	private function get_topmost_dir( string $path ): string {
		$slash_pos = strpos( $path, '/' );

		if ( false !== $slash_pos ) {
			return substr( $path, 0, $slash_pos );
		}

		return '';
	}

	private function is_excluded_dir( SplFileInfo $info ): bool {
		return in_array(
			$this->get_topmost_dir( $this->get_plugin_relpath( $info->getPathname() ) ),
			$this->exclude_dirs,
			true
		);
	}

	private function is_excluded_file( SplFileInfo $info ): bool {
		return in_array( $info->getBasename(), $this->exclude_files, true );
	}

	private function is_vendor( SplFileInfo $info ): bool {
		$relpath = $this->get_plugin_relpath( $info->getPathname() );
		$topmost = $this->get_topmost_dir( $relpath );

		return 'vendor' === $topmost &&
		       'vendor/autoload.php' !== $relpath &&
		       false === strpos( $relpath, 'vendor/composer' );
	}
}

if ( 'cli' === PHP_SAPI ) {
	( new Make_Zip() )->make();
}
