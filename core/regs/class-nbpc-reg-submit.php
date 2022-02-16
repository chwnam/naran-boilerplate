<?php
/**
 * NBPC: Submit (admin-post.php) reg.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Submit' ) ) {
	class NBPC_Reg_Submit implements NBPC_Reg {
		/** @var string */
		public string $action;

		/** @var Closure|array|string */
		public $callback;

		/** @var string|bool */
		public $allow_nopriv;

		public int $priority;

		/**
		 * Constructor method
		 *
		 * @param string               $action       Action name.
		 * @param Closure|array|string $callback     Callback.
		 * @param string|bool          $allow_nopriv true, false, or 'only_nopriv'.
		 * @param int|null             $priority     Priority number. Defaults to NBPC_PRIORITY.
		 */
		public function __construct(
			string $action,
			$callback,
			$allow_nopriv = false,
			?int $priority = null
		) {
			$this->action       = $action;
			$this->callback     = $callback;
			$this->allow_nopriv = $allow_nopriv;
			$this->priority     = is_null( $priority ) ? nbpc()->get_priority() : $priority;
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
