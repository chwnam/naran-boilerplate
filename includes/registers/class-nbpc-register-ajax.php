<?php
/**
 * NBPC: AJAX (admin-ajax.php, or wc-ajax) register.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Ajax' ) ) {
	class NBPC_Register_Ajax implements NBPC_Register {
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
			$dispatch = Closure::fromCallable( [ $this, 'dispatch' ] );

			foreach ( $this->get_items() as $item ) {
				if (
					$item instanceof NBPC_Reg_Ajax &&
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
				} catch ( Exception $e ) {
					$error = new WP_Error();
					$error->add(
						'npbc_ajax_error',
						sprintf(
							'AJAX callback handler `%s` is invalid. Please check your AJAX register items.',
							$this->inner_handlers[ $action ]
						)
					);
					wp_send_json_error( $error, 404 );
				}
			}
		}

		public function get_items(): Generator {
			yield null;
		}
	}
}
