<?php
/**
 * NBPC: Role reg.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Role' ) ) {
	class NBPC_Reg_Role implements NBPC_Reg {
		public string $role;

		public string $display_name;

		public array $capabilities;

		/**
		 * @param string              $role         Role identifier
		 * @param string              $display_name Display name, human-friendly string.
		 * @param array<string, bool> $capabilities Capabilities. Key: capability, value: boolean value.
		 */
		public function __construct( string $role, string $display_name, array $capabilities = [] ) {
			$this->role         = $role;
			$this->display_name = $display_name;
			$this->capabilities = $capabilities;
		}

		public function register( $dispatch = null ) {
			add_role( $this->role, $this->display_name, $this->capabilities );
		}

		public function unregister() {
			remove_role( $this->role );
		}
	}
}
