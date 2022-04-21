<?php
/**
 * NBPC: Submodule trait
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! trait_exists( 'NBPC_Submodule_Impl' ) ) {
	trait NBPC_Submodule_Impl {
		/**
		 * Submodules
		 *
		 * @var array
		 */
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
					$module                 = $this->invoke_module( $name, $module );
					$this->modules[ $name ] = $module;
				}
			} else {
				$module = null;
			}

			return $module;
		}

		/**
		 * Check if submodule exists
		 *
		 * @param string $name Module name.
		 *
		 * @return bool
		 */
		public function __isset( string $name ): bool {
			return isset( $this->modules[ $name ] );
		}

		/**
		 * Block __set() magic method.
		 *
		 * @param string $name  Module name.
		 * @param mixed  $value Unused.
		 *
		 * @throws RuntimeException Do not assign.
		 */
		public function __set( string $name, $value ) {
			throw new RuntimeException( 'Assigning object at runtime is not allowed.' );
		}

		/**
		 * Just touch module, let it be instantiated.
		 *
		 * @param string $name Module name.
		 *
		 * @return void
		 */
		public function touch( string $name ) {
			if ( $this->__isset( $name ) ) {
				$this->__get( $name );
			}
		}

		/**
		 * Assign modules.
		 *
		 * @param array $modules   Modules
		 * @param bool  $auto_wrap Wrap callback function automatically.
		 *
		 * @return self
		 */
		protected function assign_modules( array $modules, bool $auto_wrap = false ): self {
			$cps = nbpc()->get_constructor_params();

			if ( $auto_wrap ) {
				foreach ( $modules as $idx => $module ) {
					$this->modules[ $idx ] = function () use ( $module, $cps ) {
						if ( is_string( $module ) && class_exists( $module ) ) {
							return $this->new_instance( $module, $cps );
						} else {
							return $module;
						}
					};
				}
			} else {
				foreach ( $modules as $idx => $module ) {
					if ( is_string( $module ) && class_exists( $module ) ) {
						$this->modules[ $idx ] = $this->new_instance( $module, $cps );
					} else {
						$this->modules[ $idx ] = $module;
					}
				}
			}

			return $this;
		}

		/**
		 * Get a new instance.
		 *
		 * @param string     $class_name       Class name string.
		 * @param array|null $constructor_args Class construct arguments.
		 *
		 * @return object|NBPC_Module
		 */
		protected function new_instance( string $class_name, ?array $constructor_args = null ) {
			if ( is_null( $constructor_args ) ) {
				$constructor_args = nbpc()->get_constructor_params();
			}

			if ( $constructor_args ) {
				if ( isset( $constructor_args[ $class_name ] ) ) {
					$constructor = $this->call_if_callable_or_as_is( $constructor_args[ $class_name ] );
					return new $class_name( ...$constructor );
				}

				$items = array_filter(
					array_merge(
						(array) class_parents( $class_name ),
						(array) class_implements( $class_name )
					)
				);

				foreach ( $items as $item ) {
					if ( isset( $constructor_args[ $item ] ) ) {
						$constructor = $this->call_if_callable_or_as_is( $constructor_args[ $item ] );
						return new $class_name( ... $constructor );
					}
				}
			}

			return new $class_name();
		}

		/**
		 * Call module.
		 *
		 * @param string  $name   Module name.
		 * @param Closure $module Closure module to call.
		 *
		 * @return object|NBPC_Module
		 */
		protected function invoke_module( string $name, Closure $module ) {
			$cps = nbpc()->get_constructor_params();

			if ( isset( $cps[ $name ] ) ) {
				$params = $this->call_if_callable_or_as_is( $cps[ $name ] );
				return $module( ...$params );
			}

			return $module();
		}

		/**
		 * Call or return as-is.
		 *
		 * @param mixed $object Input object.
		 *
		 * @return mixed
		 */
		protected function call_if_callable_or_as_is( $object ) {
			return is_callable( $object ) ? $object() : $object;
		}
	}
}
