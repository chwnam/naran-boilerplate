<?php
/**
 * Naran Boilerplate Core
 *
 * regs/class-nbpc-reg-submit.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Submit' ) ) {
	class NBPC_Reg_Submit implements NBPC_Reg {
		/**
		 * Constructor method
		 *
		 * @param string               $action       Action name.
		 * @param Closure|array|string $callback     Callback.
		 * @param string|bool          $allow_nopriv true, false, or 'only_nopriv'.
		 * @param int|null             $priority     Priority number. Defaults to NBPC_PRIORITY.
		 */
		public function __construct(
			public string $action,
			public Closure|array|string $callback,
			public string|bool $allow_nopriv = false,
			public ?int $priority = null
		) {
			$this->priority = is_null( $priority ) ? nbpc_priority() : $priority;
		}

		public function register( $dispatch = null ): void {
			if ( $this->action && $this->callback && $dispatch ) {
				if ( 'only_nopriv' !== $this->allow_nopriv ) {
					add_action( "admin_post_$this->action", $dispatch, $this->priority );
				}
				if ( true === $this->allow_nopriv || 'only_nopriv' === $this->allow_nopriv ) {
					add_action( "admin_post_nopriv_$this->action", $dispatch, $this->priority );
				}
			}
		}
	}
}
