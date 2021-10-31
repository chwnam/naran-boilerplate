<?php
/**
 * NBPC: functions.php
 */

/* ABSPATH check skipped because of phpunit */

if ( ! function_exists( 'nbpc' ) ) {
	/**
	 * NBPC_Main alias.
	 *
	 * @return NBPC_Main
	 */
	function nbpc(): NBPC_Main {
		return NBPC_Main::get_instance();
	}
}


if ( ! function_exists( 'nbpc_parse_module' ) ) {
	/**
	 * Retrieve submodule by given string notaion.
	 *
	 * @param string $module_notation
	 *
	 * @return object|false;
	 */
	function nbpc_parse_module( string $module_notation ) {
		return nbpc()->get_module_by_notation( $module_notation );
	}
}


if ( ! function_exists( 'nbpc_parse_callback' ) ) {
	/**
	 * Return submodule's callback method by given string notation.
	 *
	 * @param Closure|array|string $maybe_callback
	 *
	 * @return callable|array|string
	 * @throws NBPC_Callback_Exception
	 * @example foo.bar@baz ---> array( nbpc()->foo->bar, 'baz )
	 */
	function nbpc_parse_callback( $maybe_callback ) {
		return nbpc()->parse_callback( $maybe_callback );
	}
}


if ( ! function_exists( 'nbpc_option' ) ) {
	/**
	 * Alias function for option.
	 *
	 * @return NBPC_Register_Option|null
	 */
	function nbpc_option(): ?NBPC_Register_Option {
		return nbpc()->registers->option;
	}
}


if ( ! function_exists( 'nbpc_comment_meta' ) ) {
	/**
	 * Alias function for comment meta.
	 *
	 * @return NBPC_Register_Comment_Meta|null
	 */
	function nbpc_comment_meta(): ?NBPC_Register_Comment_Meta {
		return nbpc()->registers->comment_meta;
	}
}


if ( ! function_exists( 'nbpc_post_meta' ) ) {
	/**
	 * Alias function for post meta.
	 *
	 * @return NBPC_Register_Post_Meta|null
	 */
	function nbpc_post_meta(): ?NBPC_Register_Post_Meta {
		return nbpc()->registers->post_meta;
	}
}


if ( ! function_exists( 'nbpc_term_meta' ) ) {
	/**
	 * Alias function for term meta.
	 *
	 * @return NBPC_Register_Term_Meta|null
	 */
	function nbpc_term_meta(): ?NBPC_Register_Term_Meta {
		return nbpc()->registers->term_meta;
	}
}


if ( ! function_exists( 'nbpc_user_meta' ) ) {
	/**
	 * Alias function for user meta.
	 *
	 * @return NBPC_Register_User_Meta|null
	 */
	function nbpc_user_meta(): ?NBPC_Register_User_Meta {
		return nbpc()->registers->user_meta;
	}
}


if ( ! function_exists( 'nbpc_script_debug' ) ) {
	/**
	 * Return SCRIPT_DEBUG.
	 *
	 * @return bool
	 */
	function nbpc_script_debug(): bool {
		return apply_filters( 'nbpc_script_debug', defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
	}
}


if ( ! function_exists( 'nbpc_format_callback' ) ) {
	/**
	 * Format callback method or function.
	 *
	 * This method does not care about $callable is actually callable.
	 *
	 * @param Closure|array|string $callback
	 *
	 * @return string
	 */
	function nbpc_format_callback( $callback ): string {
		if ( is_string( $callback ) ) {
			return $callback;
		} elseif (
			( is_array( $callback ) && 2 === count( $callback ) ) &&
			( is_object( $callback[0] ) || is_string( $callback[0] ) ) &&
			is_string( $callback[1] )
		) {
			if ( method_exists( $callback[0], $callback[1] ) ) {
				try {
					$ref = new ReflectionClass( $callback[0] );
					if ( $ref->isAnonymous() ) {
						return "{AnonymousClass}::{$callback[1]}";
					}
				} catch ( ReflectionException $e ) {
				}
			}

			if ( is_string( $callback[0] ) ) {
				return "{$callback[0]}::{$callback[1]}";
			} elseif ( is_object( $callback[0] ) ) {
				return get_class( $callback[0] ) . '::' . $callback[1];
			}
		} elseif ( $callback instanceof Closure ) {
			return '{Closure}';
		}

		return '{Unknown}';
	}
}
