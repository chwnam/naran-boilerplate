<?php
/**
 * NBPC: rewrite rule register
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Rewrite_Rule' ) ) {
	class NBPC_Register_Rewrite_Rule extends NBPC_Register_Base_Rewrite_Rule {
		/**
		 * Get rewrite rule regs.
		 *
		 * @return Generator
		 */
		public function get_items(): Generator {
			yield; // yield NBPC_Reg_Rewrite_Rule();
		}
	}
}
