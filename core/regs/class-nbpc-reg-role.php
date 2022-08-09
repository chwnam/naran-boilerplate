<?php
/**
 * Naran Boilerplate Core
 *
 * regs/class-nbpc-reg-role.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Role' ) ) {
	class NBPC_Reg_Role implements NBPC_Reg {
		/**
		 * Constructor method
		 *
		 * @param string              $role         Role identifier.
		 * @param string              $display_name Display name, human-friendly string.
		 * @param array{string: bool} $capabilities Capabilities. Key: capability, value: boolean value.
		 */
		public function __construct(
			public string $role,
			public string $display_name,
			public array $capabilities = []
		) {
		}

		public function register( $dispatch = null ): void {
			add_role( $this->role, $this->display_name, $this->capabilities );
		}

		public function unregister(): void {
			remove_role( $this->role );
		}
	}
}
