<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

class Test_Register_Activation extends WP_UnitTestCase {
	private $register;

	public function setUp() {
		$this->register = new class( $this ) extends NBPC_Register_Activation {
			public Test_Register_Activation $tester;

			/** @var string|int|bool */
			public $old_log_errors;

			public string $old_log_path;

			public string $test_log_file;

			public bool $closure_called = false;

			public bool $method_called = false;

			public function __construct( Test_Register_Activation $tester ) {
				parent::__construct();

				$this->tester        = $tester;
				$this->test_log_file = __DIR__ . '/activation.log';
			}

			public function get_items(): Generator {
				yield new NBPC_Reg_Activation(
					function ( $register ) { $register->closure_called = true; },
					[ $this ]
				);

				yield new NBPC_Reg_Activation( [ $this->tester, 'activationCallback' ] );
			}
		};

		if ( file_exists( $this->register->test_log_file ) ) {
			unlink( $this->register->test_log_file );
		}

		$this->register->old_log_errors = ini_set( 'log_errors', 1 );
		$this->register->old_log_path   = ini_set( 'error_log', $this->register->test_log_file );

		$file = plugin_basename( nbpc()->get_main_file() );
		do_action( 'activate_' . $file );
	}

	public function tearDown() {
		ini_set( 'log_errors', $this->register->old_log_errors );
		ini_set( 'error_log', $this->register->old_log_path );

		if ( file_exists( $this->register->test_log_file ) ) {
			unlink( $this->register->test_log_file );
		}
	}

	public function test_register_callback() {
		// Check if all callbacks are invoked.
		$this->assertTrue( $this->register->closure_called );
		$this->assertTrue( $this->register->method_called );

		// Check if logs are written.
		$this->assertFileExists( $this->register->test_log_file );

		$log = file_get_contents( $this->register->test_log_file );
		$log = array_filter( array_map( 'trim', explode( "\n", $log ) ) );

		$this->assertStringContainsString( 'Activation callback started: {Closure}', $log[0] );
		$this->assertStringContainsString( 'Activation callback finished: {Closure}', $log[1] );

		$this->assertStringContainsString(
			'Activation callback started: ' . __CLASS__ . '::' . 'activationCallback',
			$log[2]
		);

		$this->assertStringContainsString(
			'Activation callback finished: ' . __CLASS__ . '::' . 'activationCallback',
			$log[3]
		);
	}

	public function activationCallback() {
		$this->register->method_called = true;
	}
}