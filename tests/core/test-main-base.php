<?php
/**
 * Naran Boilerplate Core Tests
 *
 * tests/test-main-base.php
 */

class Test_Main_Base extends WP_UnitTestCase {
	private NBPC_Main_Base $instance;

	public static function setUpBeforeClass(): void {
		require_once __DIR__ . '/test-stuffs/module-stuff.php';
		require_once __DIR__ . '/test-stuffs/sub/submodule-stuff.php';
	}

	public function setUp(): void {
		$this->instance = new
		/**
		 * @property-read NBPC_Module_Stuff $stuff
		 */
		class () extends NBPC_Main_Base {
			protected function get_early_modules(): array {
				return [
					'stuff' => NBPC_Module_Stuff::class,
				];
			}
		};

		NBPC_Main_Base::set_instance( $this->instance );
	}

	public function test_nbpc_set_get() {
		// Check if set/set method works.
		$this->instance->set( 'foo', '1' );
		$this->assertEquals( '1', $this->instance->get( 'foo' ) );
		$this->assertEquals( '', $this->instance->get( 'bar' ) );
	}

	public function test_did_actions() {
		$this->assertTrue( boolval( did_action( 'nbpc_initialized' ) ) );
	}

	public function test_get_module_by_notation() {
		// Check module.
		$this->assertInstanceOf( NBPC_Module_Stuff::class, $this->instance->get_module_by_notation( 'stuff' ) );
		$this->assertInstanceOf( NBPC_Submodule_Stuff::class, $this->instance->get_module_by_notation( 'stuff.sub' ) );
	}

	/**
	 * @throws NBPC_Callback_Exception
	 */
	public function test_parse_callback() {
		// Check if closure is returned as-is.
		$func = function () { };
		$this->assertSame( $func, $this->instance->parse_callback( $func ) );

		$closure = Closure::fromCallable( $func );
		$this->assertSame( $closure, $this->instance->parse_callback( $closure ) );

		// Check if callable array is returned as-is.
		$this->assertSame(
			[ $this, 'test_parse_callback' ],
			$this->instance->parse_callback( [ $this, 'test_parse_callback' ] )
		);

		// Check if function is returned as-is.
		$this->assertSame( 'get_post', $this->instance->parse_callback( 'get_post' ) );

		// Check if string notation parsing works.
		$this->assertSame(
			[ $this->instance->stuff->sub, 'test_stuff' ],
			$this->instance->parse_callback( 'stuff.sub@test_stuff' )
		);

		// Check if wrong notation raise exception.
		$this->expectException( NBPC_Callback_Exception::class );
		$this->instance->parse_callback( 'non_exist@method' );
	}

	public function test_load_textdomain() {
		// Force load ko_KR locale.
		$locale_fix = function ( $locale, $domain ) {
			return 'nbpc' === $domain ? 'ko_KR' : $locale;
		};

		// While running unit test, WP_PLUGIN_DIR is incorrect.
		$plugin_path_fix = function ( $path, $domain ) {
			if ( 'nbpc' === $domain ) {
				$path = dirname( nbpc_main_file() ) . '/languages/' . wp_basename( $path );
			}
			return $path;
		};

		add_filter( 'plugin_locale', $locale_fix, 10, 2 );
		add_filter( 'load_textdomain_mofile', $plugin_path_fix, 10, 2 );

		// Trigger load textdomain.
		do_action( 'plugins_loaded' );

		remove_filter( 'plugin_locale', $locale_fix );
		remove_filter( 'load_textdomain_mofile', $plugin_path_fix );

		global $l10n, $l10n_unloaded;

		// Check if $l10n has 'nbpc' key.
		$this->assertArrayHasKey( 'nbpc', $l10n );

		// Check if the file is what we expect.
		$this->assertEquals(
			dirname( nbpc_main_file() ) . '/languages/nbpc-ko_KR.mo',
			$l10n['nbpc']->get_filename()
		);

		// Check if $l10n_unloaded does not have 'nbpc' key.
		$this->assertArrayNotHasKey( 'nbpc', $l10n_unloaded );
	}
}
