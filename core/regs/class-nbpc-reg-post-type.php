<?php
/**
 * NBPC: Custom post type reg.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Post_Type' ) ) {
	class NBPC_Reg_Post_Type implements NBPC_Reg {
		public string $post_type;

		public array $args;

		/**
		 * Constructor method
		 *
		 * @param string $post_type
		 * @param array  $args
		 */
		public function __construct( string $post_type, array $args ) {
			$this->post_type = $post_type;
			$this->args      = $args;
		}

		public function register( $dispatch = null ): void {
			if ( ! post_type_exists( $this->post_type ) ) {
				$return = register_post_type( $this->post_type, $this->args );
				if ( is_wp_error( $return ) ) {
					// $return is a WP_Error instance.
					// phpcs:ignore WordPress.Security.EscapeOutput
					wp_die( $return );
				}
			}
		}
	}
}
