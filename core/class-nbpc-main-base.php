<?php
/**
 * Naran Boilerplate Core
 *
 * class-nbpc-main-base.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Main_Base' ) ) {
	/**
	 * Class NBPC_Main_Base
	 */
	class NBPC_Main_Base implements NBPC_Module {
		use NBPC_Hook_Impl;
		use NBPC_Submodule_Impl;

		/**
		 * Singleton instance
		 */
		protected static ?NBPC_Main_Base $instance = null;

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
		 * Module's constructor parameters.
		 * Key:   module name
		 * Value: Array for constructor.
		 *
		 * @var array
		 */
		private array $constructor_params = [];

		/**
		 * Get instance method.
		 *
		 * @return static
		 */
		public static function get_instance(): self {
			if ( is_null( static::$instance ) ) {
				static::$instance = new static();
				static::$instance->initialize();
			}
			return static::$instance;
		}

		public static function set_instance( NBPC_Main_Base $instance ): void {
			static::$instance = $instance;
			static::$instance->initialize();
		}

		/**
		 * NBPC_Main_Base constructor.
		 */
		public function __construct() {
			// Do not place initialize() here.
		}

		/**
		 * @throws Exception
		 */
		public function __sleep() {
			throw new Exception( 'NBPC_Main does not support object serialization.' );
		}

		/**
		 * @throws Exception
		 */
		public function __wakeup() {
			throw new Exception( 'NBPC_Main does not support object serialization.' );
		}

		/**
		 * Retrieve submodule by given string notation.
		 */
		public function get_module_by_notation( string $module_notation ): object|false {
			if ( class_exists( $module_notation ) ) {
				return $this->new_instance( $module_notation );
			}

			if ( $module_notation ) {
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
		 * @throws NBPC_Callback_Exception Thrown if callback is invalid.
		 *
		 * @example foo.bar@baz ---> array( nbpc()->foo->bar, 'baz' )
		 */
		public function parse_callback( Closure|array|string $item ): Closure|array|string {
			if ( is_callable( $item ) ) {
				return $item;
			}

			if ( is_string( $item ) && str_contains( $item, '@' ) ) {
				[ $module_part, $method ] = explode( '@', $item, 2 );

				$module = $this->get_module_by_notation( $module_part );

				if ( $module && is_callable( [ $module, $method ] ) ) {
					return [ $module, $method ];
				}
			}

			throw new NBPC_Callback_Exception(
				sprintf(
				/* translators: formatted module name. */
					__( '%s is invalid for callback.', 'nbpc' ),
					nbpc_format_callable( $item )
				)
			);
		}

		/**
		 * Get something from storage.
		 *
		 * @param string $key     Indexing key string.
		 * @param mixed  $default Value when key is missing in the storage.
		 */
		public function get( string $key, mixed $default = '' ): mixed {
			return $this->storage[ $key ] ?? $default;
		}

		/**
		 * Set something to storage.
		 *
		 * @param string $key   Indexing key string.
		 * @param mixed  $value Value to store.
		 */
		public function set( string $key, mixed $value ): void {
			$this->storage[ $key ] = $value;
		}

		/**
		 * Load textdomain
		 *
		 * @used-by initialize()
		 */
		public function load_textdomain(): void {
			if ( nbpc_is_theme() ) {
				load_theme_textdomain(
					'nbpc',
					get_stylesheet_directory() . '/languages'
				);
			} else {
				load_plugin_textdomain(
					'nbpc',
					false,
					wp_basename( dirname( nbpc_main_file() ) ) . '/languages'
				);
			}
		}

		/**
		 * Return constructor params.
		 *
		 * @return array
		 */
		public function get_constructor_params(): array {
			return $this->constructor_params;
		}

		public function activation(): void {
			do_action( 'nbpc_activation' );
		}

		public function deactivation(): void {
			do_action( 'nbpc_deactivation' );
		}

		/**
		 * Callback for modules that should be started after init action.
		 *
		 * @return void
		 */
		public function assign_later_modules(): void {
			$this->assign_modules( $this->get_late_modules() );
		}

		/**
		 * Initialize plugin.
		 *
		 * This method is called only once.
		 *
		 * @uses NBPC_Main_Base::init_conditional_modules()
		 * @uses NBPC_Main_Base::load_textdomain()
		 */
		protected function initialize(): void {
			$this
				->setup_activation_deactivation()
				->assign_constructors( $this->get_constructors() )
				/**
				 * Higher priority modules.
				 */
				->assign_modules( $this->get_early_modules() )
				/**
				 * Lower priority modules.
				 *
				 * @uses assign_later_modules()
				 */
				->add_action( 'init', 'assign_later_modules', nbpc_priority() + 10 )
			;

			if ( nbpc_is_theme() ) {
				$this->add_action( 'after_setup_theme', 'load_textdomain', nbpc_priority() + 10 );
			} else {
				$this->add_action( 'plugins_loaded', 'load_textdomain', nbpc_priority() + 10 );
			}

			// Add 'init_conditional_modules' method if exists.
			if ( method_exists( $this, 'init_conditional_modules' ) ) {
				$this->add_action( 'wp', 'init_conditional_modules' );
			}

			$this->extra_initialize();

			do_action( 'nbpc_initialized' );
		}

		/**
		 * Setup activation-deactivation hook.
		 *
		 * @return self
		 */
		protected function setup_activation_deactivation(): self {
			if ( nbpc_is_theme() ) {
				$this
					->add_action( 'after_switch_theme', 'activation' )
					->add_action( 'switch_theme', 'deactivation' )
				;
			} else {
				$file = plugin_basename( nbpc_main_file() );
				$this
					->add_action( "activate_$file", 'activation' )
					->add_action( "deactivate_$file", 'deactivation' )
				;
			}

			return $this;
		}

		/**
		 * Assign constructors
		 *
		 * @param array{string, callable|array} $constructors Each key should be FQCN, and value can be an array, or a callable which returns an array.
		 *
		 * @return $this
		 */
		protected function assign_constructors( array $constructors ): NBPC_Main_Base {
			$this->constructor_params = $constructors;

			return $this;
		}

		/**
		 * Return modules that are initialized before 'init' action.
		 *
		 * @return array{string: object}
		 */
		protected function get_early_modules(): array {
			return [];
		}

		/**
		 * Return modules that should be initialized after 'init' action.
		 *
		 * @return array{string: object}
		 */
		protected function get_late_modules(): array {
			return [];
		}

		/**
		 * Return constructor params
		 *
		 * @return array{string: callable|array}
		 */
		protected function get_constructors(): array {
			return [];
		}

		/**
		 * Do NBPC_Main specific initialization.
		 */
		protected function extra_initialize(): void {
		}
	}
}
