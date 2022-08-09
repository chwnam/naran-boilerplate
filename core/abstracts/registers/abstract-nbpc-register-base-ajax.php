<?php
/**
 * Naran Boilerplate Core
 *
 * abstracts/registers/abstract-nbpc-register-base-ajax.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_AJAX' ) ) {
	abstract class NBPC_Register_Base_AJAX implements NBPC_Register {
		use NBPC_Hook_Impl;

		private array $inner_handlers = [];

		private array $wc_ajax = [];

		/**
		 * Constructor method
		 */
		public function __construct() {
			$this->add_action( 'init', 'register' );
		}

		/**
		 * Register regs
		 *
		 * @callback
		 * @actin       init
		 *
		 * @return void
		 */
		public function register(): void {
			foreach ( $this->get_items() as $item ) {
				if (
					$item instanceof NBPC_Reg_AJAX &&
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

		/**
		 * Generic callback method.
		 *
		 * @return void
		 */
		public function dispatch(): void {
			// Boilerplate code cannot check nonce values.
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$action = sanitize_key( $_REQUEST['action'] ?? '' );

			// Action value may come from wc-ajax.
			if ( ! $action ) {
				$wc_ajax = sanitize_key( $_GET['wc-ajax'] ?? '' );
				if ( isset( $this->wc_ajax[ $wc_ajax ] ) ) {
					$action = $wc_ajax;
				}
			}

			if ( $action && isset( $this->inner_handlers[ $action ] ) ) {
				try {
					$callback = NBPC_Main::get_instance()->parse_callback( $this->inner_handlers[ $action ] );
					if ( is_callable( $callback ) ) {
						$callback();
					}
				} catch ( NBPC_Callback_Exception $e ) {
					$error = new WP_Error();
					$error->add(
						'nbpc_ajax_error',
						sprintf(
							'AJAX callback handler `%s` is invalid. Please check your AJAX register items.',
							nbpc_format_callable( $this->inner_handlers[ $action ] )
						)
					);
					wp_send_json_error( $error, 400 );
				}
			}

			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}
	}
}
