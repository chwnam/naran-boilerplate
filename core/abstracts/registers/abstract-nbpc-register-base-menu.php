<?php
/**
 * Naran Boilerplate Core
 *
 * abstracts/registers/abstract-nbpc-register-base-menu.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Menu' ) ) {
	abstract class NBPC_Register_Base_Menu implements NBPC_Register {
		use NBPC_Hook_Impl;

		/**
		 * All menus and submenus callbacks indexed by page hook handles.
		 *
		 * @var array<string, string|callable>
		 */
		private array $callbacks = [];

		/**
		 * Constructor method.
		 */
		public function __construct() {
			$this->add_action( 'admin_menu', 'register' );
		}

		public function register(): void {
			$slugs_to_remove = [];

			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Menu || $item instanceof NBPC_Reg_Submenu ) {
					$this->callbacks[ $item->register( [ $this, 'dispatch' ] ) ] = $item->callback;
					if ( $item instanceof NBPC_Reg_Menu && $item->remove_submenu ) {
						$slugs_to_remove[] = $item->menu_slug;
					}
				}
			}

			foreach ( $slugs_to_remove as $slug ) {
				remove_submenu_page( $slug, $slug );
			}
		}

		public function dispatch(): void {
			global $page_hook;

			try {
				$callback = nbpc_parse_callback( $this->callbacks [ $page_hook ] ?? '' );
				if ( is_callable( $callback ) ) {
					$callback();
				}
			} catch ( NBPC_Callback_Exception $e ) {
				wp_die( esc_html( $e->getMessage() ) );
			}
		}
	}
}
