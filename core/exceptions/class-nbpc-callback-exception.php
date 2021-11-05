<?php
/**
 * NBPC: Callback exception
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Callback_Exception' ) ) {
	class NBPC_Callback_Exception extends Exception{
	}
}
