<?php
/**
 * NBPC: Option register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Option' ) ) {
	/**
	 * NOTE: Add 'property-read' phpdoc to make your editor inspect option items properly.
	 */
	class NBPC_Register_Option extends NBPC_Register_Base_Option {
		/**
		 * Define items here.
		 *
		 * To use alias, do not forget to return generator as 'key => value' form!
		 *
		 * @return Generator
		 */
		public function get_items(): Generator {
			yield; // yield 'alias' => new NBPC_Reg_Option();
		}
	}
}
