<?php
/**
 * NBPC: Theme setup register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Theme_Support' ) ) {
	class NBPC_Register_Theme_Support extends NBPC_Register_Base_Theme_Support {
		protected ?NBPC_Theme_Hierarchy $hierarchy = null;

		public function __construct() {
			parent::__construct();

			if ( ! is_admin() ) {
				$this->add_action( 'wp', 'map_front_modules' );
			}
		}

		public function get_items(): Generator {
			yield null; // yield new NBPC_Reg_Theme_Support( 'feature', ... );

			yield new NBPC_Reg_Theme_Support( 'post-thumbnails' );

			yield new NBPC_Reg_Theme_Support(
				'html5',
				[
					'caption',
					'comment-form',
					'comment-list',
					'gallery',
					'script',
					'style',
				]
			);

			yield new NBPC_Reg_Theme_Support( 'title-tag' );
		}

		public function map_front_modules() {
			if ( ! $this->hierarchy ) {
				$this->hierarchy = new NBPC_Theme_Hierarchy();
			}

			// Map your front modules here.
		}

		protected function extra_register(): void {
			// Do additional register process here
			// E.g. menu, template_hierarchy, ...

			register_nav_menus(
				[
					'primary-menu' => '주 메뉴',
				]
			);
		}
	}
}
