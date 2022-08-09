<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */


class Test_Register_Menu extends WP_UnitTestCase {
	private $register;

	public function setUp(): void {
		$this->register = new class() extends NBPC_Register_Base_Menu {
			public string $menu_slug = 'nbpc_test_menu';
			public string $submenu_slug = 'nbpc_test_submenu';

			public function get_items(): Generator {
				yield new NBPC_Reg_Menu(
					'NBPC Test Menu',
					'NBPC Test Menu',
					'administrator',
					$this->menu_slug,
					[ $this, 'menu_callback' ],
				);

				yield new NBPC_Reg_Submenu(
					$this->menu_slug,
					'NBPC Test Submenu',
					'NBPC Test Submenu',
					'administrator',
					$this->submenu_slug,
					[ $this, 'submenu_callback' ]
				);
			}

			public function menu_callback() {
			}

			public function submenu_callback() {
			}
		};
	}

	/**
	 * @throws ReflectionException
	 */
	public function test_menu() {
		$user = wp_get_current_user();
		if ( $user ) {
			$user->add_role( 'administrator' );
		}

		do_action( 'admin_menu' );

		$ref    = new ReflectionClass( $this->register );
		$parent = $ref->getParentClass();
		$prop   = $parent->getProperty( 'callbacks' );
		$prop->setAccessible( true );
		$callbacks = $prop->getValue( $this->register );

		// Check if menu is registered.
		$hook     = get_plugin_page_hookname( $this->register->menu_slug, '' );
		$priority = has_action( $hook, [ $this->register, 'dispatch' ] );

		$this->assertNotFalse( $priority );
		$this->assertArrayHasKey( $hook, $callbacks );
		$this->assertEquals( $callbacks[ $hook ], [ $this->register, 'menu_callback' ] );

		// Check if submenu is registered.
		$hook     = get_plugin_page_hookname( $this->register->submenu_slug, $this->register->menu_slug );
		$priority = has_action( $hook, [ $this->register, 'dispatch' ] );

		$this->assertNotFalse( $priority );
		$this->assertArrayHasKey( $hook, $callbacks );
		$this->assertEquals( $callbacks[ $hook ], [ $this->register, 'submenu_callback' ] );
	}
}
