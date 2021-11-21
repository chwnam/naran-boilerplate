<?php
/**
 * NBPC: WP-CLI reg.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_WP_CLI' ) ) {
	class NBPC_Reg_WP_CLI implements NBPC_Reg {
		public string $name;

		public $callback;

		public array $args;

		/**
		 * @param string          $name
		 * @param callable|string $callback
		 * @param array           $args
		 *
		 * @link https://make.wordpress.org/cli/handbook/references/internal-api/wp-cli-add-command/
		 */
		public function __construct( string $name, $callback, array $args = [] ) {
			$this->name     = $name;
			$this->callback = $callback;
			$this->args     = $args;
		}

		/**
		 * @param null $dispatch
		 *
		 * @throws Exception
		 */
		public function register( $dispatch = null ) {
			WP_CLI::add_command( $this->name, $this->callback, $this->args );
		}
	}
}