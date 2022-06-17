<?php
/**
 * NBPC: Rest route register base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Rest_Route' ) ) {
	abstract class NBPC_Register_Base_Rest_Route implements NBPC_Register {
		use NBPC_Hook_Impl;

		/**
		 * Store real callbacks
		 *
		 * Callback may be a method of a lazy-load module, and you do not want to wake up that module so early.
		 *
		 * @var array
		 */
		private array $real_callbacks = [];

		/**
		 * Store real validators
		 *
		 * @var array
		 */
		private array $real_validators = [];

		/**
		 * Store real sanitizers
		 *
		 * @var array
		 */
		private array $real_sanitizers = [];

		/**
		 * Constructor method
		 */
		public function __construct() {
			$this->add_action( 'rest_api_init', 'register' );
		}

		/**
		 * Register all regs.
		 *
		 * @return void
		 */
		public function register(): void {
			$index = 0;

			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Rest_Route ) {
					$item->args['nbpc'] = $index;

					// Switch callback.
					if ( isset( $item->args['callback'] ) ) {
						$this->real_callbacks[ $index ] = $item->args['callback'];
						$item->args['callback']         = [ $this, 'dispatch_request' ];
					}

					// Switch validator, and sanitizer.
					foreach ( $item->args['args'] as $key => $arg ) {
						if ( ! empty( $arg['validate_callback'] ) ) {
							$this->real_validators[ $index ][ $key ]         = $item->args['args'][ $key ]['validate_callback'];
							$item->args['args'][ $key ]['validate_callback'] = [ $this, 'dispatch_validator' ];
						}
						if ( ! empty( $arg['sanitize_callback'] ) ) {
							$this->real_sanitizers[ $index ][ $key ]         = $item->args['args'][ $key ]['sanitize_callback'];
							$item->args['args'][ $key ]['sanitize_callback'] = [ $this, 'dispatch_sanitizer' ];
						}
					}
					$item->register();

					++ $index;
				}
			}
		}

		/**
		 * Call cached, real validator.
		 *
		 * @param string          $value
		 * @param WP_REST_Request $request
		 * @param string          $key
		 *
		 * @return bool|WP_error
		 */
		public function dispatch_validator( string $value, WP_REST_Request $request, string $key ) {
			$attributes = $request->get_attributes();

			try {
				if ( ! isset( $attributes['nbpc'] ) || ! isset( $this->real_validators[ $attributes['nbpc'] ] ) ) {
					throw new Exception();
				}
				$callback = nbpc_parse_callback( $this->real_validators[ $attributes['nbpc'] ][ $key ] );
				if ( is_callable( $callback ) ) {
					return call_user_func_array( $callback, [ $value, $request, $key ] );
				} else {
					throw new Exception();
				}
			} catch ( Exception $e ) {
				return new WP_Error(
					'nbpc_invlid_validator',
					sprintf( 'The validator of route \'%s\', param \'%s\' is invalid.', $request->get_route(), $key ),
					[ 'status' => 500 ]
				);
			}
		}

		/**
		 * Call cached, real sanitizer.
		 *
		 * @param string          $value
		 * @param WP_REST_Request $request
		 * @param string          $key
		 *
		 * @return mixed|WP_Error
		 */
		public function dispatch_sanitizer( string $value, WP_REST_Request $request, string $key ) {
			$attributes = $request->get_attributes();

			try {
				if ( ! isset( $attributes['nbpc'] ) || ! isset( $this->real_sanitizers[ $attributes['nbpc'] ] ) ) {
					throw new Exception();
				}
				$callback = nbpc_parse_callback( $this->real_sanitizers[ $attributes['nbpc'] ][ $key ] );
				if ( is_callable( $callback ) ) {
					return call_user_func_array( $callback, [ $value, $request, $key ] );
				} else {
					throw new Exception();
				}
			} catch ( Exception $e ) {
				return new WP_Error(
					'nbpc_invlid_sanitizer',
					sprintf( 'The sanitizer of route \'%s\', param \'%s\' is invalid.', $request->get_route(), $key ),
					[ 'status' => 500 ]
				);
			}
		}

		/**
		 * Dispatch our real callback, return value as expected.
		 *
		 * @param WP_REST_Request $request
		 *
		 * @return mixed
		 */
		public function dispatch_request( WP_REST_Request $request ) {
			$attributes = $request->get_attributes();

			try {
				if ( ! isset( $attributes['nbpc'] ) || ! isset( $this->real_callbacks[ $attributes['nbpc'] ] ) ) {
					throw new Exception();
				}
				$callback = nbpc_parse_callback( $this->real_callbacks[ $attributes['nbpc'] ] );
				if ( is_callable( $callback ) ) {
					return call_user_func( $callback, $request );
				} else {
					throw new Exception();
				}
			} catch ( Exception $e ) {
				return new WP_Error(
					'rest_invalid_handler',
					__( 'The handler for the route is invalid.' ),
					[ 'status' => 500 ]
				);
			}
		}
	}
}
