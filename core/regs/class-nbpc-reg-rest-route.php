<?php
/**
 * Naran Boilerplate Core
 *
 * @link https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
 *
 * regs/class-nbpc-reg-rest-route.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_REST_Route' ) ) {
	class NBPC_Reg_REST_Route implements NBPC_Reg {
		/**
		 * Constructor method
		 *
		 * @param string $namespace The first URL segment after core prefix.
		 *                          e.g. myplugin/v1 ==> https://example.com/wp-json/myplugin/v1
		 *
		 * @param string $route     The base URL for route. Accepts regex.
		 *                          e.g. /author/(?P<id>\d+)
		 *
		 * @param array  $args      API options.
		 *                          e.g. sample
		 *                          [
		 *                            'methods'  => 'GET',
		 *                            'callabck' => 'my_callback',
		 *                            'args'     => [
		 *                              'id => [
		 *                                'sanitize_callback' => 'sanitize_func',
		 *                                'validate_callback' => 'validate_func',
		 *                                'required'          => true,
		 *                                'default'           => '',
		 *                              ],
		 *                            ],
		 *                          ]
		 *
		 * @param bool $override Optional. If the route already exists, should we override it?
		 *
		 * @sample
		 * Sanitization function signature
		 * function sanitize_func( $param, $request, $key ): mixed
		 *
		 * Validation funciton:
		 * function validate_func( $param, $request, $key ): bool
		 *
		 */
		public function __construct(
			public string $namespace,
			public string $route,
			public array $args = [],
			public bool $override = false
		) {
			$this->namespace = trim( $namespace, '/' );
			$this->route     = trim( $route, '/' );
		}

		/**
		 * Register the current route.
		 *
		 * @param null $dispatch Do not use in this reg.
		 *
		 * @return void
		 */
		public function register( $dispatch = null ): void {
			register_rest_route( $this->namespace, $this->route, $this->args, $this->override );
		}
	}
}
