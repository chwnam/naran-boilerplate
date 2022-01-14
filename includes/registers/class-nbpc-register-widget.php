<?php
/**
 * NBPC: Widget register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Widget' ) ) {
	class NBPC_Register_Widget extends NBPC_Register_Base_Widget {
		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_Widget();
		}
	}
}
