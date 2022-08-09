<?php
/**
 * Naran Boilerplate Core
 *
 * regs/class-nbpc-reg-meta.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Meta' ) ) {
	/**
	 * @property-read string    $object_subtype
	 * @property-read string    $type
	 * @property-read string    $description
	 * @property-read mixed     $default
	 * @property-read bool      $single
	 * @property-read ?callable $sanitize_callback
	 * @property-read ?callable $auth_callback
	 * @property-read bool      $show_in_rest
	 */
	class NBPC_Reg_Meta implements NBPC_Reg {
		private static array $meta = [];

		private string $object_type;

		private string $meta_key;

		private array $args;

		/**
		 * Constructor method
		 *
		 * @param string $object_type    Type of object metadata. Accepts 'post', 'comment', 'term', 'user'.
		 * @param string $object_subtype Subtype.
		 * @param string $meta_key       Meta key name.
		 *
		 * @return ?NBPC_Reg_Meta
		 * @see register_meta()
		 */
		public static function factory(
			string $object_type,
			string $object_subtype,
			string $meta_key
		): ?NBPC_Reg_Meta {
			global $wp_meta_keys;

			if ( isset( $wp_meta_keys[ $object_type ][ $object_subtype ][ $meta_key ] ) ) {
				$args = $wp_meta_keys[ $object_type ][ $object_subtype ][ $meta_key ];

				if ( ! isset( static::$meta[ $object_type ][ $object_subtype ][ $meta_key ] ) ) {
					static::$meta[ $object_type ][ $object_subtype ][ $meta_key ] =
						new NBPC_Reg_Meta( $object_type, $meta_key, $args );
				}

				return static::$meta[ $object_type ][ $object_subtype ][ $meta_key ];
			}

			return null;
		}

		/**
		 * Constructor method
		 *
		 * @param string              $object_type meta field type.
		 * @param string              $meta_key    meta key name.
		 * @param object|array|string $args        meta field args.
		 *
		 * @see register_meta()
		 */
		public function __construct( string $object_type, string $meta_key, object|array|string $args = [] ) {
			$this->object_type = $object_type;
			$this->meta_key    = $meta_key;
			$this->args        = wp_parse_args(
				$args,
				[
					'object_subtype'    => '',
					'type'              => 'string',
					'description'       => '',
					'default'           => '',
					'single'            => false,
					'sanitize_callback' => null,
					'auth_callback'     => null,
					'show_in_rest'      => false,
				]
			);
		}

		public function register( $dispatch = null ): void {
			if ( $this->object_type && $this->get_key() ) {
				try {
					if ( $this->args['sanitize_callback'] ) {
						$this->args['sanitize_callback'] = NBPC_Main::get_instance()->parse_callback( $this->args['sanitize_callback'] );
					}
				} catch ( NBPC_Callback_Exception $e ) {
					$error = new WP_Error();
					$error->add(
						'nbpc_meta_error',
						sprintf(
							'Meta sanitize_callback handler `%s` is invalid. Please check your meta register items.',
							nbpc_format_callable( $this->args['sanitize_callback'] )
						)
					);
					// $error is a WP_Error instance.
					// phpcs:ignore WordPress.Security.EscapeOutput
					wp_die( $error );
				}

				try {
					if ( $this->args['auth_callback'] ) {
						$this->args['auth_callback'] = NBPC_Main::get_instance()->parse_callback( $this->args['auth_callback'] );
					}
				} catch ( NBPC_Callback_Exception $e ) {
					$error = new WP_Error();
					$error->add(
						'nbpc_meta_error',
						sprintf(
							'Meta auth_callback handler `%s` is invalid. Please check your meta register items.',
							nbpc_format_callable( $this->args['auth_callback'] )
						)
					);
					// $error is a WP_Error instance.
					// phpcs:ignore WordPress.Security.EscapeOutput
					wp_die( $error );
				}

				register_meta( $this->object_type, $this->meta_key, $this->args );
			}
		}

		/**
		 * Get each register_meta() argument.
		 *
		 * @param string $prop
		 *
		 * @return mixed|string|null
		 *
		 * @see register_meta()
		 */
		public function __get( string $prop ) {
			return $this->args[ $prop ] ?? null;
		}

		public function __set( string $prop, $value ) {
			throw new RuntimeException( 'Value assignment is now allowed.' );
		}

		public function __isset( string $prop ): bool {
			return isset( $this->args[ $prop ] );
		}

		/**
		 * Get object type.
		 *
		 * @return string
		 */
		public function get_object_type(): string {
			return $this->object_type;
		}

		/**
		 * Get meta key.
		 *
		 * @return string
		 */
		public function get_key(): string {
			return $this->meta_key;
		}

		/**
		 * Get meta field value.
		 *
		 * @param mixed $object_id
		 *
		 * @return mixed
		 */
		public function get_value( mixed $object_id ): mixed {
			return match ( $this->object_type ) {
				'comment' => get_comment_meta(
					$this->safe_get_id( $object_id ),
					$this->meta_key,
					$this->single ?? false
				),
				'post'    => get_post_meta(
					$this->safe_get_id( $object_id ),
					$this->meta_key,
					$this->single ?? false
				),
				'term'    => get_term_meta(
					$this->safe_get_id( $object_id ),
					$this->meta_key,
					$this->single ?? false
				),
				'user'    => get_user_meta(
					$this->safe_get_id( $object_id ),
					$this->meta_key,
					$this->single ?? false
				),
				default   => get_metadata(
					$this->object_type,
					$this->safe_get_id( $object_id ),
					$this->meta_key,
					$this->args['single'] ?? false
				),
			};
		}

		/**
		 * Add meta value.
		 *
		 * @param mixed $object_id
		 * @param mixed $meta_value
		 * @param bool  $unique
		 *
		 * @return bool|int|WP_Error
		 */
		public function add( mixed $object_id, mixed $meta_value, bool $unique = false ): int|bool|WP_Error {
			return match ( $this->object_type ) {
				'comment' => add_comment_meta(
					$this->safe_get_id( $object_id ),
					$this->meta_key,
					$meta_value,
					$unique
				),
				'post'    => add_post_meta(
					$this->safe_get_id( $object_id ),
					$this->meta_key,
					$meta_value,
					$unique
				),
				'term'    => add_term_meta(
					$this->safe_get_id( $object_id ),
					$this->meta_key,
					$meta_value,
					$unique
				),
				'user'    => add_user_meta(
					$this->safe_get_id( $object_id ),
					$this->meta_key,
					$meta_value,
					$unique
				),
				default   => add_metadata(
					$this->object_type,
					$this->safe_get_id( $object_id ),
					$this->meta_key,
					$meta_value,
					$unique
				),
			};
		}

		/**
		 * Update meta field.
		 */
		public function update( mixed $object_id, mixed $meta_value, mixed $prev_value = '' ): int|bool|WP_Error {
			return match ( $this->object_type ) {
				'comment' => update_comment_meta(
					$this->safe_get_id( $object_id ),
					$this->meta_key,
					$meta_value,
					$prev_value
				),
				'post'    => update_post_meta(
					$this->safe_get_id( $object_id ),
					$this->meta_key,
					$meta_value,
					$prev_value
				),
				'term'    => update_term_meta(
					$this->safe_get_id( $object_id ),
					$this->meta_key,
					$meta_value,
					$prev_value
				),
				'user'    => update_user_meta(
					$this->safe_get_id( $object_id ),
					$this->meta_key,
					$meta_value,
					$prev_value
				),
				default   => update_metadata(
					$this->object_type,
					$this->safe_get_id( $object_id ),
					$this->meta_key,
					$meta_value,
					$prev_value
				),
			};
		}

		/**
		 * Delete meta value of an object.
		 */
		public function delete( mixed $object_id, mixed $meta_value = '' ): bool {
			return match ( $this->object_type ) {
				'comment'  => delete_comment_meta( $this->safe_get_id( $object_id ), $this->meta_key, $meta_value ),
				'post'     => delete_post_meta( $this->safe_get_id( $object_id ), $this->meta_key, $meta_value ),
				'taxonomy' => delete_term_meta( $this->safe_get_id( $object_id ), $this->meta_key, $meta_value ),
				'user'     => delete_user_meta( $this->safe_get_id( $object_id ), $this->meta_key, $meta_value ),
				default    => delete_metadata(
					$this->object_type,
					$this->safe_get_id( $object_id ),
					$this->meta_key,
					$meta_value
				),
			};
		}

		/**
		 * Update meta field with value form request.
		 */
		public function update_from_request( mixed $object_id ): int|false|WP_Error {
			// Boilerplate code cannot check nonce values.
			// phpcs:disable WordPress.Security.NonceVerification.Recommended

			if ( is_callable( $this->sanitize_callback ) && isset( $_REQUEST[ $this->get_key() ] ) ) {
				// Meta sanitize_callback will sanitize the value.
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				return $this->update( $object_id, wp_unslash( $_REQUEST[ $this->get_key() ] ) );
			}

			return false;

			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Get save object ID.
		 */
		protected function safe_get_id( mixed $object_id ): int|false {
			if ( is_int( $object_id ) || is_numeric( $object_id ) ) {
				return (int) $object_id;
			}

			if ( $object_id instanceof WP_Post || $object_id instanceof WP_User ) {
				return $object_id->ID;
			}

			if ( $object_id instanceof WP_Term ) {
				return $object_id->term_id;
			}

			if ( $object_id instanceof WP_Comment ) {
				return $object_id->comment_ID;
			}

			if ( is_array( $object_id ) && isset( $object_id['ID'] ) ) {
				return (int) $object_id['ID'];
			}

			if ( is_array( $object_id ) && isset( $object_id['id'] ) ) {
				return (int) $object_id['id'];
			}

			if ( is_object( $object_id ) && method_exists( $object_id, 'get_id' ) ) {
				return (int) $object_id->get_id();
			}

			if ( is_object( $object_id ) && isset( $object_id->ID ) ) {
				return (int) $object_id->ID;
			}

			if ( is_object( $object_id ) && isset( $object_id->id ) ) {
				return (int) $object_id->id;
			}

			return false;
		}
	}
}
