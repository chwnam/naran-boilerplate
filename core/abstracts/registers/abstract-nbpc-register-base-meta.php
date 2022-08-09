<?php
/**
 * Naran Boilerplate Core
 *
 * abstracts/registers/abstract-nbpc-register-base-meta.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Meta' ) ) {
	abstract class NBPC_Register_Base_Meta implements NBPC_Register {
		use NBPC_Hook_Impl;

		/**
		 * @var array Key: alias
		 *            Value: array of size 3.
		 *            - 0: object type
		 *            - 1: object subtype
		 *            - 2: key.
		 */
		private array $fields = [];

		/**
		 * Constructor method.
		 */
		public function __construct() {
			$this->add_action( 'init', 'register' );
		}

		public function __get( string $alias ): ?NBPC_Reg_Meta {
			if ( isset( $this->fields[ $alias ] ) ) {
				return NBPC_Reg_Meta::factory( ...$this->fields[ $alias ] );
			}

			return null;
		}

		public function __set( string $alias, $value ) {
			throw new RuntimeException( 'Value assignment is now allowed.' );
		}

		public function __isset( string $alias ): bool {
			return isset( $this->fields[ $alias ] );
		}

		public function register(): void {
			foreach ( $this->get_items() as $idx => $item ) {
				if ( $item instanceof NBPC_Reg_Meta ) {
					$item->register();

					$alias = is_int( $idx ) ? $item->get_key() : $idx;

					$this->fields[ $alias ] = [ $item->get_object_type(), $item->object_subtype, $item->get_key() ];
				}
			}
		}
	}
}
