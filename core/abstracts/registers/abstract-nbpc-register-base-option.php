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
		 * Keys are autoload 'no' options.
		 *
		 * @var array<string, true>
		 */
		private array $autoload_no = [];

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
					$option_name            = $item->get_option_name();
					$alias                  = is_int( $idx ) ? $option_name : $idx;
					$this->fields[ $alias ] = $option_name;
					if ( ! $item->is_autoload() ) {
						$this->autoload_no[ $option_name ] = true;
					}
					$item->register();
				}
			}

			if ( ! empty( $this->autoload_no ) ) {
				$this
					->add_action( 'added_option', 'fix_autoload', null, 2 )
					->add_action( 'updated_option', 'fix_autoload', null, 3 )
				;
			}
		}

		/**
		 * Forcibly fix autoload field.
		 *
		 * @param string $option
		 *
		 * @return void
		 */
		public function fix_autoload( string $option ) {
			global $wpdb;

			if ( isset( $this->autoload_no[ $option ] ) ) {
				$wpdb->update( $wpdb->options, [ 'autoload' => 'no' ], [ 'option_name' => $option ] );
			}
		}
	}
}
