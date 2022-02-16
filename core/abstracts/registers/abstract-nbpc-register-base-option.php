<?php
/**
 * NBPC: Option register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Option' ) ) {
	abstract class NBPC_Register_Base_Option implements NBPC_Register {
		use NBPC_Hook_Impl;

		/** @var array Key: alias, value: option_name */
		private array $fields = [];

		/**
		 * Constructor method.
		 */
		public function __construct() {
			$this->add_action( 'init', 'register' );
		}

		public function __get( string $alias ): ?NBPC_Reg_Option {
			if ( isset( $this->fields[ $alias ] ) ) {
				return NBPC_Reg_Option::factory( $this->fields[ $alias ] );
			}

			return null;
		}

		public function __set( string $alias, $value ) {
			throw new RuntimeException( 'Value assignment is now allowed.' );
		}

		public function __isset( string $alias ): bool {
			return isset( $this->fields[ $alias ] );
		}

		/**
		 * @callback
		 * @action       init
		 *
		 * @return void
		 */
		public function register(): void {
			foreach ( $this->get_items() as $idx => $item ) {
				if ( $item instanceof NBPC_Reg_Option ) {
					$item->register();

					$alias = is_int( $idx ) ? $item->get_option_name() : $idx;

					$this->fields[ $alias ] = $item->get_option_name();
				}
			}
		}
	}
}
