<?php
/**
 * NBPC: Submit (admin-post.php) register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Submit' ) ) {
	abstract class NBPC_Register_Base_Submit implements NBPC_Register {
		use NBPC_Hook_Impl;

		private array $inner_handlers = [];

		public function __construct() {
			$this->add_action( 'init', 'register' );
		}

		/**
		 * @callback
		 * @actin       init
		 */
		public function register() {
			$dispatch = [ $this, 'dispatch' ];

			foreach ( $this->get_items() as $item ) {
				if (
					$item instanceof NBPC_Reg_Submit &&
					$item->action &&
					! isset( $this->inner_handlers[ $item->action ] )
				) {
					$this->inner_handlers[ $item->action ] = $item->callback;
					$item->register( $dispatch );
				}
			}
		}

		public function dispatch() {
			$action = $_REQUEST['action'] ?? '';

			if ( $action && isset( $this->inner_handlers[ $action ] ) ) {
				try {
					$callback = nbpc_parse_callback( $this->inner_handlers[ $action ] );
					if ( is_callable( $callback ) ) {
						call_user_func( $callback );
					}
				} catch ( NBPC_Callback_Exception $e ) {
					$error = new WP_Error();
					$error->add(
						'nbpc_submit_error',
						sprintf(
							'Submit callback handler `%s` is invalid. Please check your submit register items.',
							nbpc_format_callback( $this->inner_handlers[ $action ] )
						)
					);
					wp_die( $error, 404 );
				}
			}
		}
	}
}
