<?php
/**
 * NBPC: Rest reoute register
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Rest_Route' ) ) {
	class NBPC_Register_Rest_Route extends NBPC_Register_Base_Rest_Route {
		/**
		 * Define your custom API endpoint.
		 *
		 * @return Generator
		 *
		 * @link   https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
		 * @sample yield new NBPC_Reg_Rest_Route(
		 *           'nbpc/v1',
		 *           'author/(?P<id>\d+)',
		 *           [
		 *             'methods'  => 'GET',
		 *             'callback' => 'module_v1@author'
		 *             'args'     => [
		 *               'id' => [
		 *                 'sanitize_callback' => 'module_v1@sanitize_id',
		 *                 'validate_callback' => 'module_v1@validate_id',
		 *                 'required'          => true,
		 *                 'default'           => '',
		 *               ],
		 *             ],
		 *           ]
		 *         );
		 */
		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_Rest_Route();
		}
	}
}
