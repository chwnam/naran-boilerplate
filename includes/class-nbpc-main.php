<?php
/**
 * NBPC: Main class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Main' ) ) {
	/**
	 * Class NBPC_Main
	 *
	 * @property-read NBPC_Admins    $admins
	 * @property-read NBPC_Registers $registers
	 */
	final class NBPC_Main extends NBPC_Main_Base {
		/**
		 * Return root modules
		 *
		 * @return array
		 *
		 * @used-by NBPC_Main_Base::initialize()
		 */
		protected function get_modules(): array {
			return [
				'admins'    => NBPC_Admins::class,
				'registers' => NBPC_Registers::class,
			];
		}
	}
}
