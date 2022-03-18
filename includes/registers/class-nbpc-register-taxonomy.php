<?php
/**
 * NBPC: Custom taxonomy register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Taxonomy' ) ) {
	class NBPC_Register_Taxonomy extends NBPC_Register_Base_Taxonomy {
		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_Taxonomy();
			yield new NBPC_Reg_Taxonomy(
				'article_cat',
				[ 'article' ],
				[
					'label'        => 'Article Cat',
					'public'       => true,
					'hierarchical' => true,
				]
			);
		}
	}
}
