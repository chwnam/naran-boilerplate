<?php
/**
 * NBPC: Option reg.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Option' ) ) {
	/**
	 * @property-read string        $type
	 * @property-read string        $group
	 * @property-read string        $description
	 * @property-read callable|null $sanitize_callback
	 * @property-read bool          $show_in_rest
	 * @property-read mixed         $default
	 */
	class NBPC_Reg_Option implements NBPC_Reg {
		private static array $options = [];

		private string $option_group;

		private string $option_name;

		public array $args;

		public static function factory( string $option_name ): ?NBPC_Reg_Option {
			global $wp_registered_settings;

			if ( isset( $wp_registered_settings[ $option_name ] ) ) {
				if ( ! isset( static::$options[ $option_name ] ) ) {
					$args = $wp_registered_settings[ $option_name ];

					static::$options[ $option_name ] = new NBPC_Reg_Option( $args['group'], $option_name, $args );
				}

				return static::$options[ $option_name ];
			}

			return null;
		}

		public function __construct(
			string $option_group,
			string $option_name,
			array $args = []
		) {
			$this->option_group = $option_group;
			$this->option_name  = $option_name;
			$this->args         = wp_parse_args(
				$args,
				[
					'type'              => 'string',
					'group'             => $this->option_group,
					'description'       => '',
					'sanitize_callback' => null,
					'show_in_rest'      => false,
					'default'           => '',
					'autoload'          => true,  // NBPC specific.
				]
			);
		}

		/**
		 * @param string $prop
		 *
		 * @return mixed|null
		 */
		public function __get( string $prop ) {
			if ( 'group' === $prop ) {
				return $this->option_group;
			}

			return $this->args[ $prop ] ?? null;
		}

		public function register( $dispatch = null ) {
			if ( $this->option_group && $this->option_name ) {
				if ( $this->args['sanitize_callback'] ) {
					try {
						$this->args['sanitize_callback'] = nbpc_parse_callback( $this->args['sanitize_callback'] );
					} catch ( Exception $e ) {
						$error = new WP_Error();
						$error->add(
							'nbpc_option_error',
							sprintf(
								'Option sanitize callback handler `%s` is invalid. Please check your option register items.',
								nbpc_format_callback( $this->args['sanitize_callback'] )
							)
						);
						wp_die( $error );
					}
				}
				register_setting( $this->option_group, $this->option_name, $this->args );
			}
		}

		public function get_option_group(): string {
			return $this->option_group;
		}

		public function get_option_name(): string {
			return $this->option_name;
		}

		public function get_value( $default = false ) {
			if ( func_num_args() > 0 ) {
				return get_option( $this->get_option_name(), $default );
			} else {
				return get_option( $this->get_option_name() );
			}
		}

		public function is_autoload(): bool {
			return $this->args['autoload'];
		}

		public function update( $value ): bool {
			return update_option( $this->get_option_name(), $value, $this->is_autoload() );
		}

		public function update_from_request(): bool {
			if ( isset( $_REQUEST[ $this->get_option_name() ] ) && is_callable( $this->sanitize_callback ) ) {
				return $this->update( $_REQUEST[ $this->get_option_name() ] );
			}

			return false;
		}

		public function delete(): bool {
			return delete_option( $this->get_option_name() );
		}
	}
}
