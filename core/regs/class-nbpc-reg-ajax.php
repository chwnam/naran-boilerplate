<?php
/**
 * Naran Boilerplate Core
 *
 * regs/class-nbpc-reg-ajax.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_AJAX' ) ) {
	class NBPC_Reg_AJAX implements NBPC_Reg {
		/**
		 * Constructor method
		 *
		 * @param string               $action       Action name.
		 * @param Closure|array|string $callback     Callback.
		 * @param string|bool          $allow_nopriv true, false, or 'only_nopriv'.
		 * @param bool                 $is_wc_ajax   Is this for wc-ajax (WooCommerce AJAX) or regular admin-ajax.php.
		 * @param int|null             $priority     Priority number. Defaults to NBPC_PRIORITY.
		 */
		public function __construct(
			public string $action,
			public Closure|array|string $callback,
			public string|bool $allow_nopriv = false,
			public bool $is_wc_ajax = false,
			public int|null $priority = null
		) {
			$this->priority = is_null( $priority ) ? nbpc_priority() : $priority;
		}

		public function register( $dispatch = null ): void {
			if ( $this->action && $this->callback && $dispatch ) {
				if ( $this->is_wc_ajax ) {
					add_action( "wc_ajax_$this->action", $dispatch, $this->priority );
				} else {
					if ( 'only_nopriv' !== $this->allow_nopriv ) {
						add_action( "wp_ajax_$this->action", $dispatch, $this->priority );
					}
					if ( true === $this->allow_nopriv || 'only_nopriv' === $this->allow_nopriv ) {
						add_action( "wp_ajax_nopriv_$this->action", $dispatch, $this->priority );
					}
				}
			}
		}
	}
}
