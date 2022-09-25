<?php
/**
 * Naran Boilerplate Core
 *
 * traits/trait-nbpc-autobind-impl.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! trait_exists( 'NBPC_Autobind_Impl' ) ) {
	trait NBPC_Autobind_Impl {
		/**
		 * Parse autobind string.
		 *
		 * - Parse $REQUEST['action'] value.
		 * - If the value matches our autobind rule, create wp_ajax_(nopriv_)${action} callback dynamically.
		 *
		 * Rule:
		 * - {nbpc}:{module_notation}@{callback}?foo&bar
		 * - {nbpc}:{module_notation}@{callback}!
		 *
		 * NONCE:
		 * - NONCE is automatically checked unless following '!' is found in the value.
		 * - NONCE action string can be configured by following '?' string.
		 * - '*' at the end also add wp_ajax_nopriv_ action.
		 *
		 * @return array{
		 *     action: string,
		 *     allow_nopriv: bool,
		 *     callback: callable|array|string,
		 *     exempt_nonce: bool,
		 *     nonce_action: string,
		 *     params: string[],
		 * }|false
		 */
		public function parse_autobind(): array|false {
			$action = wp_unslash( $_REQUEST['action'] ?? '' );
			$regex  = '/^nbpc:(([A-Za-z0-9_.]+)@)?([A-Za-z0-9_]+)(!|\?([A-Za-z0-9\-_&]*))?(\*?)$/';

			if ( ! preg_match( $regex, $action, $matches ) ) {
				return false;
			}

			// $matches:
			// 2: module name.
			// 3: method or function name.
			// 4: exclamation mark.
			// 5: nonce extra parameters.
			// 6: allow nopriv.
			try {
				$module   = $matches[2] ?? '';
				$func     = $matches[3] ?? '';
				$callback = nbpc_parse_callback( $module ? "$module@$func" : $func );
			} catch ( NBPC_Callback_Exception $e ) {
				wp_die( $e->getMessage() );
			}

			$nonce_action = '_nbpc_nonce';
			$exempt_nonce = ( $matches[4] ?? '' ) === '!';
			$params       = $exempt_nonce ? [] : array_unique( array_filter( array_map( 'trim', explode( '&', $matches[5] ) ) ) );
			$allow_nopriv = '*' === ( $matches[6] ?? '' );

			if ( ! $exempt_nonce && $params ) {
				foreach ( $params as $param ) {
					if ( ! isset( $_REQUEST[ $param ] ) ) {
						wp_die( "Autobind error: '$param' is requested, but it is not set." );
					}
					$nonce_action .= $_REQUEST[ $param ];
				}
			}

			return compact( 'action', 'allow_nopriv', 'exempt_nonce', 'nonce_action', 'params', 'callback' );
		}
	}
}
