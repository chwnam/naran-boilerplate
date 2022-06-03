<?php
/**
 * NBPC: Rest route reg.
 *
 * @link https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Rest_Route' ) ) {
	class NBPC_Reg_Rest_Route implements NBPC_Reg {
		/**
		 * The first URL segment after core prefix.
		 *
		 * e.g. myplugin/v1 ==> https://example.com/wp-json/myplugin/v1
		 *
		 * @var string
		 */
		public string $namespace;

		/**
		 * The base URL for route.
		 *
		 * Accepts regex. e.g. /author/(?P<id>\d+)
		 *
		 * @var string
		 */
		public string $route;

		/**
		 * Options.
		 *
		 * @var array
		 *
		 * @sample [
		 *           'methods'  => 'GET',
		 *           'callabck' => 'my_callback',
		 *           'args'     => [
		 *             'id => [
		 *               'sanitize_callback' => 'sanitize_func',
		 *               'validate_callback' => 'validate_func',
		 *               'required'          => true,
		 *               'default'           => '',
		 *             ],
		 *           ],
		 *         ]
		 *
		 * function sanitize_func( $param, $request, $key ): mixed {
		 * }
		 *
		 * function validate_func( $param, $request, $key ): bool {
		 * }
		 */
		public array $args;

		/**
		 * Optional. If the route already exists, should we override it?
		 *
		 * @var bool
		 */
		public bool $override;

		/**
		 * Constructor method
		 */
		public function __construct( string $namespace, string $route, array $args = [], bool $override = false ) {
			$this->namespace = trim( $namespace, '/' );
			$this->route     = trim( $route, '/' );
			$this->args      = $args;
			$this->override  = $override;
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
