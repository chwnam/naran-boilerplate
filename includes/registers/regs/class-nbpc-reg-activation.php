<?php
/**
 * NBPC: Activation reg.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Activation' ) ) {
	class NBPC_Reg_Activation implements NBPC_Reg {
		/** @var Closure|array|string */
		public $callback;

		public array $args;

		public bool $error_log;

		/**
		 * @param Closure|array|string $callback
		 * @param array                $args
		 * @param bool                 $error_log
		 */
		public function __construct( $callback, array $args = [], bool $error_log = true ) {
			$this->callback  = $callback;
			$this->args      = $args;
			$this->error_log = $error_log;
		}

		/**
		 * Method name can mislead, but it does its activation callback job.
		 *
		 * @param null $dispatch
		 */
		public function register( $dispatch = null ) {
			try {
				$callback = nbpc_parse_callback( $this->callback );
			} catch ( Exception $e ) {
				$error = new WP_Error();
				$error->add(
					'nbpc_activation_error',
					sprintf(
						'Activation callback handler `%s` is invalid. Please check your activation register items.',
						$this->callback
					)
				);
				wp_die( $error );
			}

			if ( $callback ) {
				if ( $this->error_log ) {
					error_log( sprintf( 'Activation callback started: %s', $this->format_callback() ) );
				}

				call_user_func_array( $callback, $this->args );

				if ( $this->error_log ) {
					error_log( sprintf( 'Activation callback finished: %s', $this->format_callback() ) );
				}
			}
		}

		private function format_callback(): string {
			if ( is_string( $this->callback ) ) {
				return $this->callback;
			} elseif ( is_array( $this->callback ) && 2 === count( $this->callback ) ) {
				return get_class( $this->callback[0] ) . '::' . $this->callback[1];
			} else {
				return '{Closure}';
			}
		}
	}
}
