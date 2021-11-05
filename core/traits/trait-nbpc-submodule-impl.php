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
		 * @param string $name
		 *
		 * @return object|null
		 */
		public function __get( string $name ) {
			$module = $this->modules[ $name ] ?? null;

			if ( $module instanceof Closure ) {
				$this->modules[ $name ] = $module = $module();
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
		 */
		protected function assign_modules( array $modules ) {
			foreach ( $modules as $idx => $module ) {
				if ( is_string( $module ) && class_exists( $module ) ) {
					$this->modules[ $idx ] = new $module();
				} else {
					$this->modules[ $idx ] = $module;
				}
			}
		}
	}
}
