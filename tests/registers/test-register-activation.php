<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

class Test_Register_Activation extends WP_UnitTestCase {
	public bool $closure_called = false;

	private bool $method_called = false;

	private $log_errors;

	private $log_path;

	private string $test_log_file;

	public function setUp() {
		new class( $this ) extends NBPC_Register_Activation {
			private Test_Register_Activation $test;

			public function __construct( Test_Register_Activation $test ) {
				parent::__construct();
				$this->test = $test;
			}

			public function get_items(): Generator {
				yield new NBPC_Reg_Activation(
					function ( Test_Register_Activation $test ) {
						$test->closure_called = true;
					},
					[ $this->test ],
					true
				);

				yield new NBPC_Reg_Activation(
					[ $this->test, 'activationCallback' ],
					[],
					true
				);
			}
		};

		$this->test_log_file = __DIR__ . '/activation.log';
		if ( file_exists( $this->test_log_file ) ) {
			unlink( $this->test_log_file );
		}

		$this->log_errors = ini_set( 'log_errors', 1 );
		$this->log_path   = ini_set( 'error_log', __DIR__ . '/activation.log' );

		$file = plugin_basename( nbpc()->get_main_file() );
		do_action( 'activate_' . $file );
	}

	public function tearDown() {
		ini_set( 'log_errors', $this->log_errors );
		ini_set( 'error_log', $this->log_path );

		if ( file_exists( $this->test_log_file ) ) {
			unlink( $this->test_log_file );
		}
	}

	public function test_register_callback() {
		// Check if all callbacks are invoked.
		$this->assertTrue( $this->closure_called );
		$this->assertTrue( $this->method_called );

		// Check if logs are written.
		$this->assertFileExists( $this->test_log_file );

		$log = file_get_contents( $this->test_log_file );
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
		$this->method_called = true;
	}
}