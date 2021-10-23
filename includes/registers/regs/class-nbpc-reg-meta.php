<?php
/**
 * NBPC: Meta reg.
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

		private string $object_type = '';

		private string $meta_key = '';

		private array $args;

		/**
		 * @param string $object_type    Type of object metadata. Accepts 'post', 'comment', 'term', 'user'
		 * @param string $meta_key       Meta key name.
		 * @param string $object_subtype Subtype.
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
		 * @param string $meta_key    meta key name.
		 * @param string $object_type meta field type.
		 * @param array  $args        meta field args.
		 *
		 * @see register_meta()
		 */
		public function __construct( string $object_type, string $meta_key, array $args ) {
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

		public function register( $dispatch = null ) {
			if ( $this->object_type && $this->get_key() ) {
				try {
					if ( $this->args['sanitize_callback'] ) {
						$this->args['sanitize_callback'] = nbpc_parse_callback( $this->args['sanitize_callback'] );
					}
				} catch (Exception $e) {
					$error = new WP_Error();
					$error->add(
						'npbc_meta_error',
						sprintf(
							'Meta sanitize_callback handler `%s` is invalid. Please check your meta register items.',
							$this->args['sanitize_callback']
						)
					);
					wp_die( $error );
				}

				try {
					if ( $this->args['auth_callback'] ) {
						$this->args['auth_callback'] = nbpc_parse_callback( $this->args['auth_callback'] );
					}
				} catch (Exception $e) {
					$error = new WP_Error();
					$error->add(
						'npbc_meta_error',
						sprintf(
							'Meta auth_callback handler `%s` is invalid. Please check your meta register items.',
							$this->args['auth_callback']
						)
					);
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
		public function get_value( $object_id ) {
			switch ( $this->object_type ) {
				case 'comment':
					return get_comment_meta(
						$this->_get_id( $object_id ),
						$this->meta_key,
						$this->single ?? false
					);

				case 'post':
					return get_post_meta(
						$this->_get_id( $object_id ),
						$this->meta_key,
						$this->single ?? false
					);

				case 'term':
					return get_term_meta(
						$this->_get_id( $object_id ),
						$this->meta_key,
						$this->single ?? false
					);

				case 'user':
					return get_user_meta(
						$this->_get_id( $object_id ),
						$this->meta_key,
						$this->single ?? false
					);

				default:
					return get_metadata(
						$this->object_type,
						$this->_get_id( $object_id ),
						$this->meta_key,
						$this->args['single'] ?? false
					);
			}
		}

		/**
		 * Update meta field.
		 *
		 * @param mixed $object_id
		 * @param mixed $meta_value
		 * @param mixed $prev_value
		 *
		 * @return bool|int|WP_Error
		 */
		public function update( $object_id, $meta_value, $prev_value = '' ) {
			switch ( $this->object_type ) {
				case 'comment':
					return update_comment_meta(
						$this->_get_id( $object_id ),
						$this->meta_key,
						$meta_value,
						$prev_value
					);

				case 'post':
					return update_post_meta(
						$this->_get_id( $object_id ),
						$this->meta_key,
						$meta_value,
						$prev_value
					);

				case 'term':
					return update_term_meta(
						$this->_get_id( $object_id ),
						$this->meta_key,
						$meta_value,
						$prev_value
					);

				case 'user':
					return update_user_meta(
						$this->_get_id( $object_id ),
						$this->meta_key,
						$meta_value,
						$prev_value
					);

				default:
					return update_metadata(
						$this->object_type,
						$this->_get_id( $object_id ),
						$this->meta_key,
						$meta_value,
						$prev_value
					);
			}
		}

		/**
		 * Delete meta value of an object.
		 *
		 * @param mixed  $object_id
		 * @param string $meta_value
		 *
		 * @return bool
		 */
		public function delete( $object_id, $meta_value = '' ): bool {
			switch ( $this->object_type ) {
				case 'comment':
					return delete_comment_meta( $this->_get_id( $object_id ), $this->meta_key, $meta_value );
				case 'post':
					return delete_post_meta( $this->_get_id( $object_id ), $this->meta_key, $meta_value );
				case 'taxonomy':
					return delete_term_meta( $this->_get_id( $object_id ), $this->meta_key, $meta_value );
				case 'user':
					return delete_user_meta( $this->_get_id( $object_id ), $this->meta_key, $meta_value );
				default:
					return delete_metadata(
						$this->object_type, $this->_get_id( $object_id ), $this->meta_key, $meta_value );
			}
		}

		/**
		 * Update meta field with valeu form request.
		 *
		 * @param $object_id
		 *
		 * @return bool|int|WP_Error
		 */
		public function update_from_request( $object_id ) {
			if ( isset( $_REQUEST[ $this->get_key() ] ) && is_callable( $this->sanitize_callback ) ) {
				return $this->update( $object_id, $_REQUEST[ $this->get_key() ] );
			} else {
				return false;
			}
		}


		/**
		 * Get save object ID.
		 *
		 * @param mixed $object_id
		 *
		 * @return false|int
		 */
		protected function _get_id( $object_id ) {
			if ( is_int( $object_id ) || is_numeric( $object_id ) ) {
				return intval( $object_id );
			} elseif ( $object_id instanceof WP_Post || $object_id instanceof WP_User ) {
				return $object_id->ID;
			} elseif ( $object_id instanceof WP_Term ) {
				return $object_id->term_id;
			} elseif ( $object_id instanceof WP_Comment ) {
				return $object_id->comment_post_ID;
			} elseif ( is_array( $object_id ) && isset( $object_id['ID'] ) ) {
				return $object_id['ID'];
			} elseif ( is_object( $object_id ) && isset( $object_id->ID ) ) {
				return $object_id->ID;
			} elseif ( class_exists( 'WC_Product' ) && $object_id instanceof WC_Product ) {
				return $object_id->get_id();
			}

			return false;
		}
	}
}
