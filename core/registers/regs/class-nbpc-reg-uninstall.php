<?php
/**
 * NBPC: Uninstall reg.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Uninstall' ) ) {
	class NBPC_Reg_Uninstall implements NBPC_Reg {
		/** @var Closure|array|string */
		public $callback;

		public array $args;

		public bool $error_log;

		/**
		 * @param Closure|array|string $callback
		 * @param array                $args
		 * @param bool                 $error_log
		 */
		public function __construct( $callback, array $args = [], bool $error_log = false ) {
			$this->callback  = $callback;
			$this->args      = $args;
			$this->error_log = $error_log;
		}

		/**
		 * Method name can mislead, but it does its uninstall callback job.
		 *
		 * @param null $dispatch
		 */
		public function register( $dispatch = null ) {
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
				wp_die( $error );
			}

			if ( $callback ) {
				if ( $this->error_log ) {
					error_log( error_log( sprintf( 'Uninstall callback started: %s', $this->callback ) ) );
				}

				call_user_func( $callback, $this->args );

				if ( $this->error_log ) {
					error_log( sprintf( 'Uninstall callback finished: %s', $this->callback ) );
				}
			}
		}
	}
}
