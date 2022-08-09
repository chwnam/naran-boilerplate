<?php
/**
 * Naran Boilerplate Core
 *
 * regs/class-nbpc-reg-uninstall.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Uninstall' ) ) {
	class NBPC_Reg_Uninstall implements NBPC_Reg {
		/**
		 * Constructor method
		 */
		public function __construct(
			public Closure|array|string $callback,
			public array $args = [],
			public bool $error_log = false
		) {
		}

		/**
		 * Method name can mislead, but it does its uninstall callback job.
		 *
		 * @param null $dispatch
		 */
		public function register( $dispatch = null ): void {
			try {
				$callback = nbpc_parse_callback( $this->callback );
			} catch ( NBPC_Callback_Exception $e ) {
				$error = new WP_Error();
				$error->add(
					'nbpc_uninstall_error',
					sprintf(
						'Uninstall callback handler `%s` is invalid. Please check your uninstall register items.',
						$this->callback
					)
				);
				// $return is a WP_Error instance.
				// phpcs:ignore WordPress.Security.EscapeOutput
				wp_die( $error );
			}

			if ( $callback ) {
				if ( $this->error_log ) {
					error_log( error_log( sprintf( 'Uninstall callback started: %s', $this->callback ) ) );
				}

				$callback( $this->args );

				if ( $this->error_log ) {
					error_log( sprintf( 'Uninstall callback finished: %s', $this->callback ) );
				}
			}
		}
	}
}
