<?php
/**
 * NBPC: Custom taxonomy reg.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Taxonomy' ) ) {
	class NBPC_Reg_Taxonomy implements NBPC_Reg {
		public string $taxonomy;

		public array $object_type;

		public array $args;

		/**
		 * @param string       $taxonomy
		 * @param string|array $object_type
		 * @param array        $args
		 */
		public function __construct( string $taxonomy, $object_type, array $args ) {
			$this->taxonomy    = $taxonomy;
			$this->object_type = (array) $object_type;
			$this->args        = $args;
		}
		public function register( $dispatch = null ) {
			if ( ! taxonomy_exists( $this->taxonomy ) ) {
				$return = register_taxonomy( $this->taxonomy, $this->object_type, $this->args );
				if ( is_wp_error( $return ) ) {
					wp_die( $return );
				}
			}
		}
	}
}
