<?php
/**
 * Naran Boilerplate Core
 *
 * exceptions/class-nbpc-callback-exception.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Callback_Exception' ) ) {
	class NBPC_Callback_Exception extends Exception {
	}
}
