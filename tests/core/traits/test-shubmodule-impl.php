<?php
/**
 * Naran Boilerplate Core Tests
 *
 * test/traits/test-submodule-impl.php
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


if ( ! class_exists( 'Test_Submodule_Impl' ) ) {
	/**
	 * Class Test_Submodule_Impl
	 *
	 * @package nbpc
	 */
	class Test_Submodule_Impl extends WP_UnitTestCase {
		public function test_module_assign() {
			$test = self::get_test_class();

			// Check if assign is forbidden.
			$exception_caught = false;
			try {
				$test->runtime_assign = 'is forbidden';
			} catch ( RuntimeException $e ) {
				$exception_caught = true;
			}
			$this->assertTrue( $exception_caught );
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

		public function test_module_assign_2() {
			$obj = self::get_test_class(
				[
					'foo'  => 'bar',
					'test' => Submodule_Impl_Test::class,
				]
			);

			// Check if unassigned module returns null
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
			$v1 = $modules->getValue( $obj );
			$this->assertArrayHasKey( 'test', $v1 );
			$this->assertIsCallable( $v1['test'] );
			$this->assertInstanceOf( Closure::class, $v1['test'] );

			// Check if module 'test' is called, and it is now stored as Dock instance.
			$test = $obj->test;
			$v2   = $modules->getValue( $obj );
			$this->assertArrayHasKey( 'test', $v2 );
			$this->assertTrue( $v2['test'] === $test ); // Test if two objects are the same.
			$this->assertInstanceOf( Submodule_Impl_Test::class, $obj->test );
			$this->assertInstanceOf( Submodule_Impl_Test::class, $v2['test'] ); // Now we expect 'test' is converted.
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
}
