<?php
/**
 * NBPC: Admin modules group
 *
 * Manage all admin modules
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Admin' ) ) {
	/**
	 * @property-read NBPC_Admin_Page $page
	 * @property-read NBPC_Admin_Post $post
	 */
	class NBPC_Admin implements NBPC_Module {
		use NBPC_Hook_Impl;
		use NBPC_Submodule_Impl;

		/**
		 * Constructor method
		 */
		public function __construct() {
			$this
				->add_action( 'current_screen', 'instantiate_admin_module' )
				->assign_modules(
					[
						'page' => NBPC_Admin_Page::class,
						'post' => NBPC_Admin_Post::class,
					],
					true // Automatic wrapping. Do not instantiate modules unless they are explicitly invoked.
				)
			;
		}

		/**
		 * Instantiate admin module by screen condition.
		 *
		 * @callback
		 * @action    current_screen
		 *
		 * @param WP_Screen $screen
		 *
		 * @return void
		 */
		public function instantiate_admin_module( WP_Screen $screen ) {
			if ( 'post' === $screen->post_type || 'page' === $screen->post_type ) {
				$this->touch( $screen->post_type );
			}
		}
	}
}
