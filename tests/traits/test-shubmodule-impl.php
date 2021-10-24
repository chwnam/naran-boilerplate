<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */


/**
 * Class for unit test.
 */
if ( ! class_exists( 'Submodule_Impl_Test' ) ) {
	class Submodule_Impl_Test implements NBPC_Module {
		public function get_string(): string {
			return 'Dock::get_string() called.';
		}
	}
}


/**
 * Class Test_Submodule_Impl
 *
 * @package nbpc
 */
class Test_Submodule_Impl extends WP_UnitTestCase {
	public function test_module_assing() {
		$test = self::get_test_class();

		// Check if assign is forbidden.
		$exception_catched = false;
		try {
			$test->runtime_assign = 'is forbidden';
		} catch ( RuntimeException $e ) {
			$exception_catched = true;
		}
		$this->assertTrue( $exception_catched );
	}

	private static function get_test_class( array $modules = [] ): object {
		return new class ( $modules ) {
			use NBPC_Submodule_Impl;

			public function __construct( array $modules ) {
				if ( ! empty( $modules ) ) {
					$this->assign_modules( $modules );
				}
			}
		};
	}

	public function test_module_assign() {
		$obj = self::get_test_class(
			[
				'foo'  => 'bar',
				'test' => Submodule_Impl_Test::class
			]
		);

		// Check if unassigned module retuns null
		$this->assertNull( $obj->unassigned );

		// Check if test->foo is bar
		$this->assertEquals( 'bar', $obj->foo );

		// Check if module 'test' is returned as instance, not as string.
		$this->assertInstanceOf( Submodule_Impl_Test::class, $obj->test );
		$this->assertIsObject( $obj->test );
		$this->assertEquals( 'Dock::get_string() called.', $obj->test->get_string() );
	}

	/**
	 * @throws ReflectionException
	 */
	public function test_module_closure() {
		$obj = self::get_test_class(
			[ 'test' => function () { return new Submodule_Impl_Test; } ]
		);

		$ref     = new ReflectionClass( $obj );
		$modules = $ref->getProperty( 'modules' );
		$modules->setAccessible( true );

		// Check if module 'test' is stored as closure and not converted.
		$value = $modules->getValue( $obj );
		$this->assertArrayHasKey( 'test', $value );
		$this->assertIsCallable( $value['test'] );
		$this->assertInstanceOf( Closure::class, $value['test'] );

		// Check if module 'test' is called, and it is now stored as Dock instance.
		$test  = $obj->test;
		$value = $modules->getValue( $obj );
		$this->assertArrayHasKey( 'test', $value );
		$this->assertTrue( $value['test'] === $test ); // Test if two objects are the same.
		$this->assertInstanceOf( Submodule_Impl_Test::class, $obj->test );
		$this->assertInstanceOf( Submodule_Impl_Test::class, $value['test'] ); // Now we expect 'test' is converted.
		$this->assertEquals( 'Dock::get_string() called.', $test->get_string() );
	}

	/**
	 * @throws ReflectionException
	 */
	public function test_module_default() {
		$obj = self::get_test_class();

		// Check if module class is empty.
		$ref = new ReflectionClass( $obj );

		$modules = $ref->getProperty( 'modules' );
		$modules->setAccessible( true );
		$values = $modules->getValue( $obj );
		$this->assertEmpty( $values );
	}
}
