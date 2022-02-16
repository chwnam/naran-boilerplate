<?php
/**
 * NBPC: Sidebar reg.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Sidebar' ) ) {
	/**
	 * @property-read string $name
	 * @property-read string $id
	 * @property-read string $description
	 * @property-read string $class
	 * @property-read string $before_widget
	 * @property-read string $after_widget
	 * @property-read string $before_title
	 * @property-read string $after_title
	 * @property-read string $before_sidebar
	 * @property-read string $after_sidebar
	 */
	class NBPC_Reg_Sidebar implements NBPC_Reg {
		public array $args = [];

		/**
		 * Constructor method
		 *
		 * @param array $args
		 */
		public function __construct( array $args = [] ) {
			$this->args = $args;
		}

		/**
		 * @param string $prop
		 *
		 * @return string|null
		 */
		public function __get( string $prop ) {
			return $this->args[ $prop ] ?? '';
		}

		public function __set( string $prop, $value ) {
			throw new RuntimeException( 'Value assignment is now allowed.' );
		}

		public function __isset( string $prop ): bool {
			return isset( $this->args[ $prop ] );
		}

		public function register( $dispatch = null ): void {
			register_sidebar( $this->args );
		}
	}
}
