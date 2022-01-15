<?php
/**
 * NBPC: Submodule trait
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! trait_exists( 'NBPC_Submodule_Impl' ) ) {
	trait NBPC_Submodule_Impl {
		private array $modules = [];

		/**
		 * Get submodule by name.
		 *
		 * @param string $name The name must be non-numeric.
		 *
		 * @return object|null
		 */
		public function __get( string $name ) {
			if ( ! is_numeric( $name ) ) {
				$module = $this->modules[ $name ] ?? null;
				if ( $module instanceof Closure ) {
					$this->modules[ $name ] = $module = $this->invoke_module( $name, $module );
				}
			} else {
				$module = null;
			}

			return $module;
		}

		/**
		 * Check if submodule exists
		 *
		 * @param string $name
		 *
		 * @return bool
		 */
		public function __isset( string $name ): bool {
			return isset( $this->modules[ $name ] );
		}

		/**
		 * Block __set() magic method.
		 *
		 * @param string $name
		 * @param mixed  $value
		 */
		public function __set( string $name, $value ) {
			throw new RuntimeException( 'Assigning object at runtime is not allowed.' );
		}

		/**
		 * Assign modules.
		 *
		 * @param array $modules
		 *
		 * @return self
		 */
		protected function assign_modules( array $modules ): self {
			$cps = nbpc()->get_constructor_params();

			foreach ( $modules as $idx => $module ) {
				if ( is_string( $module ) && class_exists( $module ) ) {
					$this->modules[ $idx ] = $this->new_instance( $module, $cps );
				} else {
					$this->modules[ $idx ] = $module;
				}
			}

			return $this;
		}

		/**
		 * @param string     $class_name
		 * @param array|null $constructor_params
		 *
		 * @return object|NBPC_Module
		 */
		protected function new_instance( string $class_name, ?array $constructor_params = null ) {
			if ( is_null( $constructor_params ) ) {
				$constructor_params = nbpc()->get_constructor_params();
			}

			if ( $constructor_params ) {
				if ( isset( $constructor_params[ $class_name ] ) ) {
					$constructor = $this->__call_if_callable_or_as_is( $constructor_params[ $class_name ] );
					return new $class_name( ...$constructor );
				} else {
					$items = array_filter(
						array_merge(
							(array) class_parents( $class_name ),
							(array) class_implements( $class_name )
						)
					);
					foreach ( $items as $item ) {
						if ( isset( $constructor_params[ $item ] ) ) {
							$constructor = $this->__call_if_callable_or_as_is( $constructor_params[ $item ] );
							return new $class_name( ... $constructor );
						}
					}
				}
			}

			return new $class_name();
		}

		/**
		 * @param string  $name
		 * @param Closure $module
		 *
		 * @return object|NBPC_Module
		 */
		protected function invoke_module( string $name, Closure $module ) {
			$cps = nbpc()->get_constructor_params();

			if ( isset( $cps[ $name ] ) ) {
				$params = $this->__call_if_callable_or_as_is( $cps[ $name ] );
				return $module( ...$params );
			} else {
				return $module();
			}
		}

		/**
		 * @param mixed $object
		 *
		 * @return mixed
		 */
		private function __call_if_callable_or_as_is( $object ) {
			return is_callable( $object ) ? $object() : $object;
		}
	}
}
