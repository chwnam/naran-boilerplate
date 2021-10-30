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

		/**
		 * Free storage for the plugin.
		 *
		 * @var array
		 */
		private array $storage = [];

		/**
		 * Parsed module cache.
		 * Key:   input string notation.
		 * Value: found module, or false.
		 *
		 * @var array
		 */
		private array $parsed_cache = [];

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

		/**
		 * Return plugin main file.
		 *
		 * @return string
		 */
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
		 * Retrieve submodule by given string notaion.
		 *
		 * @param string $module_notation
		 *
		 * @return object|false
		 */
		public function get_module_by_notation( string $module_notation ) {
			if ( class_exists( $module_notation ) ) {
				return new $module_notation();
			} elseif ( $module_notation ) {
				if ( ! isset( $this->parsed_cache[ $module_notation ] ) ) {
					$module = $this;
					foreach ( explode( '.', $module_notation ) as $crumb ) {
						if ( isset( $module->{$crumb} ) ) {
							$module = $module->{$crumb};
						} else {
							$module = false;
							break;
						}
					}
					$this->parsed_cache[ $module_notation ] = $module;
				}

				return $this->parsed_cache[ $module_notation ];
			}

			return false;
		}

		/**
		 * Return submodule's callback method by given string notation.
		 *
		 * @param Closure|array|string $item
		 *
		 * @return Closure|array|string
		 * @throws NBPC_Callback_Exception
		 * @example foo.bar@baz ---> array( nbpc()->foo->bar, 'baz )
		 */
		public function parse_callback( $item ) {
			if ( is_callable( $item ) ) {
				return $item;
			} elseif ( is_string( $item ) && false !== strpos( $item, '@' ) ) {
				[ $module_part, $method ] = explode( '@', $item, 2 );

				$module = $this->get_module_by_notation( $module_part );

				if ( $module && method_exists( $module, $method ) ) {
					return [ $module, $method ];
				}
			}

			throw new NBPC_Callback_Exception(
				sprintf(
				/* translators: formatted module name. */
					__( '%s is invalid for callback.', 'nbpc' ),
					nbpc_format_callback( $item )
				),
				100
			);
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
