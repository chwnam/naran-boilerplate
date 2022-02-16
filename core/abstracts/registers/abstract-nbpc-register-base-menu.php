<?php
/**
 * NBPC: Menu register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Menu' ) ) {
	abstract class NBPC_Register_Base_Menu implements NBPC_Register {
		use NBPC_Hook_Impl;

		private array $callbacks = [];

		/**
		 * Constructor method.
		 */
		public function __construct() {
			$this->add_action( 'admin_menu', 'register' );
		}

		public function register(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Menu || $item instanceof NBPC_Reg_Submenu ) {
					$this->callbacks[ $item->register( [ $this, 'dispatch' ] ) ] = $item->callback;
					if ( $item instanceof NBPC_Reg_Menu && $item->remove_submenu ) {
						remove_submenu_page( $item->menu_slug, $item->menu_slug );
					}
				}
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
