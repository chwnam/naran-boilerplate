<?php
/**
 * Naran Boilerplate Core
 *
 * regs/class-nbpc-reg-taxonomy.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Taxonomy' ) ) {
	class NBPC_Reg_Taxonomy implements NBPC_Reg {
		/**
		 * Constructor method.
		 *
		 * @param string       $taxonomy    Taxonomy.
		 * @param string|array $object_type Related objects.
		 * @param array        $args        Arguments array.
		 */
		public function __construct(
			public string $taxonomy,
			public array|string $object_type,
			public array $args
		) {
			$this->object_type = (array) $object_type;
		}

		/**
		 * Register taxonomy regs.
		 *
		 * @param null $dispatch Not used.
		 *
		 * @return void
		 */
		public function register( $dispatch = null ): void {
			if ( ! taxonomy_exists( $this->taxonomy ) ) {
				$return = register_taxonomy( $this->taxonomy, $this->object_type, $this->args );
				if ( is_wp_error( $return ) ) {
					// $return is a WP_Error instance.
					// phpcs:ignore WordPress.Security.EscapeOutput
					wp_die( $return );
				}
			}
		}
	}
}
