<?php

/**
 * Naran Boilerplate Core Tests
 *
 * tests/traits/test-template-impl.php
 */
class Test_Template_Impl extends WP_UnitTestCase {
	use NBPC_Template_Impl;

	private static string $dir;

	public static function setupBeforeClass(): void {
		self::$dir = dirname( __DIR__ ) . '/test-stuffs/tmpl';
	}

	public function setUp(): void {
		add_filter( 'nbpc_locate_file_paths', function ( array $paths, string $cache_name ) {
			[ $tmpl_type, $relpath, $variant, $ext ] = explode( ':', $cache_name );
			$dir       = dirname( $relpath );
			$file_name = wp_basename( $relpath );

			return [
				$variant ? self::$dir . "/$dir/$file_name-$variant.$ext" : false,
				self::$dir . "/$dir/$file_name.$ext",
			];
		}, 10, 2 );
	}

	public function test_locate_file_no_variant() {
		$dir = self::$dir;

		// Check template without variant.
		$located = $this->locate_file( 'template', 'a' );
		$this->assertEquals( "$dir/a.php", realpath( $located ) );

		// Check template in subdirectory without variant.
		$file    = "$dir/sub/dir/a.php";
		$located = $this->locate_file( 'template', 'sub/dir/a' );
		$this->assertEquals( $file, realpath( $located ) );
	}

	public function test_locate_file_variant() {
		$dir  = self::$dir;
		$file = "$dir/a-v.php";

		// Check template with variant.
		$located = $this->locate_file( 'template', 'a', 'v' );
		$this->assertEquals( $file, realpath( $located ) );

		// Check template in subdirectory with variant.
		$file    = "$dir/sub/dir/a-v.php";
		$located = $this->locate_file( 'template', 'sub/dir/a', 'v' );
		$this->assertEquals( $file, realpath( $located ) );
	}

	public function test_locate_file_variant_default() {
		$dir = self::$dir;

		// Check template with variant, b.php is created, not b-v.php.
		$located = $this->locate_file( 'template', 'b', 'v' );
		$this->assertEquals( "$dir/b.php", realpath( $located ) );

		// Check template with variant in subdirectory.
		$file    = "$dir/sub/dir/b-v.php";  // evade locate file cache.
		$located = $this->locate_file( 'template', 'sub/dir/b', 'v' );
		$this->assertEquals( $file, realpath( $located ) );
	}

	public function test_extending() {
		$dir = self::$dir;

		$child = $this->render(  'child', [], '', false );

		$this->assertEquals(
			"This is grandparent. Expo.\nThis is a parent. Expo.\nThis is child. Expo.",
			trim( $child )
		);
	}
}
