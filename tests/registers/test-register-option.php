<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

class Test_Register_Option extends WP_UnitTestCase {
	private $register;

	public function setUp() {
		$this->register = new class() extends NBPC_Register_Option {
			public bool $sanitize_called = false;

			public function get_items(): Generator {
				// Option without alias.
				yield new NBPC_Reg_Option(
					'test_option_group',
					'test_option_red',
					[
						'type'              => 'string',
						'description'       => 'Red option description',
						'sanitize_callback' => [ $this, 'sanitize' ],
						'default'           => 'red_default',
						'autoload'          => false,
					]
				);

				// Option with alias.
				yield 'blue' => new NBPC_Reg_Option(
					'test_option_group',
					'test_option_blue'
				);
			}

			public function sanitize( $value ): string {
				$this->sanitize_called = true;

				return sanitize_text_field( $value );
			}
		};

		do_action( 'init' );
	}

	public function test_option_red() {
		$option = $this->register->test_option_red;

		$this->assertInstanceOf( NBPC_Reg_Option::class, $option );

		// Check methods
		$this->assertEquals( 'test_option_group', $option->get_option_group() );
		$this->assertEquals( 'test_option_red', $option->get_option_name() );
		$this->assertFalse( $option->is_autoload() );

		// Check if the default value is retrieved.
		$this->assertEquals( 'red_default', $option->get_value() );

		// Check if the default value can be overridden.
		$this->assertEquals( 'alt_default', $option->get_value( 'alt_default' ) );

		// Save option value, and check if the sanitization callback is called.
		$option->update( ' X ' );
		$this->assertTrue( $this->register->sanitize_called );
		$this->assertEquals( 'X', $option->get_value() );
		$this->assertEquals( 'X', get_option( 'test_option_red' ) );

		// Check if the option is found in the option DB.
		global $wpdb;

		$value = $wpdb->get_var(
			$wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name=%s", 'test_option_red' )
		);
		$this->assertEquals( 'X', $value );

		// Check if autoload is set to 'no'.
		$value = $wpdb->get_var(
			$wpdb->prepare( "SELECT autoload FROM {$wpdb->options} WHERE option_name=%s", 'test_option_red' )
		);
		$this->assertEquals( 'no', $value );

		// Check if the option is removed.
		$option->delete();
		$value = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->options} WHERE option_name=%s", 'test_option_red' )
		);
		$this->assertNull( $value );

		// Check if the default is returned after removal.
		$this->assertEquals( 'red_default', $option->get_value() );
	}

	public function test_option_blue() {
		// Check if alias is okay. Most tests are done in test_option_red.
		$option = $this->register->blue;
		$this->assertInstanceOf( NBPC_Reg_Option::class, $option );
	}
}