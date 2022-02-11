<?php
/**
 * NBPC: AJAX (admin-ajax.php, or wc-ajax) register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Ajax' ) ) {
	abstract class NBPC_Register_Base_Ajax implements NBPC_Register {
		use NBPC_Hook_Impl;

		private array $inner_handlers = [];

		private array $wc_ajax = [];

		public function __construct() {
			$this->add_action( 'init', 'register' );
		}

		/**
		 * @callback
		 * @actin       init
		 */
		public function register() {
			foreach ( $this->get_items() as $item ) {
				if (
					$item instanceof NBPC_Reg_Ajax &&
					$item->action &&
					! isset( $this->inner_handlers[ $item->action ] )
				) {
					$this->inner_handlers[ $item->action ] = $item->callback;
					if ( $item->is_wc_ajax ) {
						$this->wc_ajax[ $item->action ] = true;
					}
					$item->register( [ $this, 'dispatch' ] );
				}
			}
		}

		public function dispatch() {
			$action = $_REQUEST['action'] ?? '';

			// Action value may come from wc-ajax.
			if ( ! $action && $this->wc_ajax[ $_GET['wc-ajax'] ?? '' ] ?? false ) {
				$action = $_GET['wc-ajax'];
			}

			if ( $action && isset( $this->inner_handlers[ $action ] ) ) {
				try {
					$callback = nbpc_parse_callback( $this->inner_handlers[ $action ] );
					if ( is_callable( $callback ) ) {
						call_user_func( $callback );
					}
				} catch ( NBPC_Callback_Exception $e ) {
					$error = new WP_Error();
					$error->add(
						'nbpc_ajax_error',
						sprintf(
							'AJAX callback handler `%s` is invalid. Please check your AJAX register items.',
							nbpc_format_callback( $this->inner_handlers[ $action ] )
						)
					);
					wp_send_json_error( $error, 404 );
				}
			}
		}
	}
}
