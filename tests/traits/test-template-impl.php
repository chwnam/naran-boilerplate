<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */


/**
 * Class Test_Teplate_Impl
 *
 * @package nbpc
 */
class Test_Teplate_Impl extends WP_UnitTestCase {
	use NBPC_Template_Impl;

	private static string $dir;

	public static function setUpBeforeClass() {
		self::$dir = plugin_dir_path( nbpc()->get_main_file() ) . "includes/templates";

		if ( self::is_test_available() ) {
			// Subdirectory
			mkdir( self::$dir . "/sub/dir", 0777, true );
		}
	}

	private static function is_test_available(): bool {
		if (
			file_exists( self::$dir ) &&
			is_dir( self::$dir ) &&
			is_executable( self::$dir ) &&
			is_writable( self::$dir )
		) {
			return true;
		} else {
			fwrite( STDERR, self::$dir . " wants more privilege. Test is skipped.\n" );
			return false;
		}
	}

	public static function tearDownAfterClass() {
		if ( self::is_test_available() ) {
			// Subdirectory
			rmdir( self::$dir . "/sub/dir" );
			rmdir( self::$dir . "/sub" );
		}
	}

	public function test_locate_file_no_variant() {
		if ( $this->is_test_available() ) {
			$dir = self::$dir;

			// Check template without variant.
			$file = "{$dir}/a.php";
			touch( $file );
			$located = $this->locate_file( 'template', 'a', '' );
			$this->assertEquals( "$dir/a.php", realpath( $located ) );
			unlink( $file );

			// Check template in subdirectory without variant.
			$file = "{$dir}/sub/dir/a.php";
			touch( $file );
			$located = $this->locate_file( 'template', 'sub/dir/a', '' );
			$this->assertEquals( $file, realpath( $located ) );
			unlink( $file );
		}
	}

	public function test_locate_file_variant() {
		if ( $this->is_test_available() ) {
			$dir  = self::$dir;
			$file = "{$dir}/a-v.php";

			// Check template with variant.
			touch( $file );
			$located = $this->locate_file( 'template', 'a', 'v' );
			$this->assertEquals( $file, realpath( $located ) );
			unlink( $file );

			// Check template in subdirectory with variant.
			$file = "{$dir}/sub/dir/a-v.php";
			touch( $file );
			$located = $this->locate_file( 'template', 'sub/dir/a', 'v' );
			$this->assertEquals( $file, realpath( $located ) );
			unlink( $file );
		}
	}

	public function test_locate_file_variant_default() {
		if ( $this->is_test_available() ) {
			$dir = self::$dir;

			// Check template with variant, b.php is created, not b-v.php.
			$file = "{$dir}/b.php";  // evade locate file cache.
			touch( $file );
			$located = $this->locate_file( 'template', 'b', 'v' );
			$this->assertEquals( "$dir/b.php", realpath( $located ) );
			unlink( $file );

			// Check template with variant in subdirectory.
			$file = "{$dir}/sub/dir/b-v.php";  // evade locate file cache.
			touch( $file );
			$located = $this->locate_file( 'template', 'sub/dir/b', 'v' );
			$this->assertEquals( $file, realpath( $located ) );
			unlink( $file );
		}
	}
}
