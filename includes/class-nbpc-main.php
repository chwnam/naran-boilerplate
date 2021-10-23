<?php
/**
 * NBPC: Main
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Main' ) ) {
	/**
	 * Class NBPC_Main
	 *
	 * @property-read NBPC_Admins    $admins
	 * @property-read NBPC_Registers $registers
	 */
	final class NBPC_Main implements NBPC_Module {
		use NBPC_Hook_Impl;
		use NBPC_Submodule_Impl;

		/**
		 * @var NBPC_Main|null
		 */
		private static ?NBPC_Main $instance = null;

		private array $storage = [];

		/**
		 * Get instance method.
		 *
		 * @return NBPC_Main
		 */
		public static function get_instance(): NBPC_Main {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->initialize();
			}
			return self::$instance;
		}

		/**
		 * NBPC_Main constructor.
		 */
		private function __construct() {
		}

		public function get_main_file(): string {
			return NBPC_MAIN_FILE;
		}

		/**
		 * Get default priority
		 *
		 * @return int
		 */
		public function get_priority(): int {
			return NBPC_PRIORITY;
		}

		/**
		 * Get the theme version
		 *
		 * @return string
		 */
		public function get_version(): string {
			return NBPC_VERSION;
		}

		/**
		 * Get something from storage.
		 */
		public function get( string $key, $default = '' ) {
			return $this->storage[ $key ] ?? $default;
		}

		/**
		 * Set something to storage.
		 */
		public function set( string $key, $value ) {
			$this->storage[ $key ] = $value;
		}

		public function init_conditional_modules() {
		}

		private function initialize() {
			$this->assign_modules(
				[
					'admins'    => NBPC_Admins::class,
					'registers' => NBPC_Registers::class,
				]
			);

			$this->add_action( 'wp', 'init_conditional_modules' );

			do_action( 'nbpc_initialized' );
		}
	}
}
