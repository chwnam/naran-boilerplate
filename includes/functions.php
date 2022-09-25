<?php
/**
 * NBPC
 *
 * functions.php
 */

// PHP_SAPI check make you run unit tests safely.
if ( ! defined( 'ABSPATH' ) && 'cli' !== PHP_SAPI ) {
	exit;
}

if ( ! function_exists( 'nbpc' ) ) {
	/**
	 * NBPC_Main alias.
	 */
	function nbpc(): NBPC_Main {
		return NBPC_Main::get_instance();
	}
}


if ( ! function_exists( 'nbpc_parse_module' ) ) {
	/**
	 * Retrieve submodule by given string notation.
	 */
	function nbpc_parse_module( string $module_notation ): object|false {
		return nbpc()->get_module_by_notation( $module_notation );
	}
}


if ( ! function_exists( 'nbpc_parse_callback' ) ) {
	/**
	 * Return submodule's callback method by given string notation.
	 *
	 * @throws NBPC_Callback_Exception Thrown if callback is invalid.
	 *
	 * @example foo.bar@baz ---> array( nbpc()->foo->bar, 'baz' )
	 */
	function nbpc_parse_callback( callable|array|string $maybe_callback ): callable|array|string {
		return nbpc()->parse_callback( $maybe_callback );
	}
}


if ( ! function_exists( 'nbpc_option' ) ) {
	/**
	 * Alias function for option.
	 */
	function nbpc_option(): ?NBPC_Register_Option {
		return nbpc()->registers->option;
	}
}


if ( ! function_exists( 'nbpc_comment_meta' ) ) {
	/**
	 * Alias function for comment meta.
	 */
	function nbpc_comment_meta(): ?NBPC_Register_Comment_Meta {
		return nbpc()->registers->comment_meta;
	}
}


if ( ! function_exists( 'nbpc_post_meta' ) ) {
	/**
	 * Alias function for post meta.
	 */
	function nbpc_post_meta(): ?NBPC_Register_Post_Meta {
		return nbpc()->registers->post_meta;
	}
}


if ( ! function_exists( 'nbpc_term_meta' ) ) {
	/**
	 * Alias function for term meta.
	 */
	function nbpc_term_meta(): ?NBPC_Register_Term_Meta {
		return nbpc()->registers->term_meta;
	}
}


if ( ! function_exists( 'nbpc_user_meta' ) ) {
	/**
	 * Alias function for user meta.
	 */
	function nbpc_user_meta(): ?NBPC_Register_User_Meta {
		return nbpc()->registers->user_meta;
	}
}


if ( ! function_exists( 'nbpc_get_front_module' ) ) {
	/**
	 * Get front module.
	 *
	 * The module is chosen in NBPC_Register_Theme_Support::map_front_modules().
	 *
	 * @see NBPC_Register_Theme_Support::map_front_modules()
	 */
	function nbpc_get_front_module(): NBPC_Front_Module {
		$hierarchy    = NBPC_Theme_Hierarchy::get_instance();
		$front_module = $hierarchy->get_front_module();

		if ( ! $front_module ) {
			$front_module = $hierarchy->get_fallback();
		}

		if ( ! $front_module instanceof NBPC_Front_Module ) {
			throw new RuntimeException( __( '$instance should be a front module instance.', 'nbpc' ) );
		}

		return $front_module;
	}
}


if ( ! function_exists( 'nbpc_doing_submit' ) ) {
	/**
	 * Chekc if request is from 'admin-post.php'
	 *
	 * @return bool
	 */
	function nbpc_doing_submit(): bool {
		return apply_filters( 'nbpc_doing_submit', is_admin() && str_ends_with( $_SERVER['SCRIPT_NAME'] ?? '', '/wp-admin/admin-post.php' ) );
	}
}
