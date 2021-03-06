<?php
/**
 * NBPC: functions.php
 */

/* Skip ABSPATH check for unit testing. */

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


if ( ! function_exists( 'nbpc_is_theme' ) ) {
	/**
	 * Check if it is used as theme.
	 *
	 * @return bool
	 */
	function nbpc_is_theme(): bool {
		return defined( 'NBPC_THEME' ) && NBPC_THEME;
	}
}


if ( ! function_exists( 'nbpc_is_plugin' ) ) {
	/**
	 * Check if it is used as plugin. (default)
	 *
	 * @return bool
	 */
	function nbpc_is_plugin(): bool {
		return ! nbpc_is_theme();
	}
}


if ( ! function_exists( 'nbpc_parse_module' ) ) {
	/**
	 * Retrieve submodule by given string notation.
	 *
	 * @param string $module_notation Module notation string.
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
	 * @param Closure|array|string $maybe_callback Maybe something can be callback function.
	 *
	 * @return callable|array|string
	 * @throws NBPC_Callback_Exception Thrown if callback is invalid.
	 * @example foo.bar@baz ---> array( nbpc()->foo->bar, 'baz' )
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
	 * @param Closure|array|string $callback Callback method to be formatted.
	 *
	 * @return string
	 */
	function nbpc_format_callback( $callback ): string {
		if ( is_string( $callback ) ) {
			return $callback;
		}

		if (
			( is_array( $callback ) && 2 === count( $callback ) ) &&
			( is_object( $callback[0] ) || is_string( $callback[0] ) ) &&
			is_string( $callback[1] )
		) {
			if ( method_exists( $callback[0], $callback[1] ) ) {
				try {
					$ref = new ReflectionClass( $callback[0] );
					if ( $ref->isAnonymous() ) {
						return "{AnonymousClass}::$callback[1]";
					}
				} catch ( ReflectionException $e ) {
					return "Error while reflecting $callback[0].";
				}
			}

			if ( is_string( $callback[0] ) ) {
				return "$callback[0]::$callback[1]";
			}

			if ( is_object( $callback[0] ) && 'stdClass' !== get_class( (object) $callback[0] ) ) {
				return get_class( (object) $callback[0] ) . '::' . $callback[1];
			}
		} elseif ( $callback instanceof Closure ) {
			return '{Closure}';
		}

		return '{Unknown}';
	}
}


if ( ! function_exists( 'nbpc_get_front_module' ) ) {
	/**
	 * Get front module.
	 *
	 * The module is chosen in NBPC_Register_Theme_Support::map_front_modules().
	 *
	 * @return NBPC_Front_Module
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


if ( ! function_exists( 'nbpc_react_refresh_runtime' ) ) {
	/**
	 * Helper function for properly enqueueing 'wp-react-refresh-runtime'.
	 *
	 * Gutenberg plugin must be installed, but its activation is optional.
	 *
	 * @return Generator
	 */
	function nbpc_react_refresh_runtime(): Generator {
		if ( ! wp_script_is( 'wp-react-refresh-runtime', 'registered' ) ) {
			$path = WP_PLUGIN_DIR . '/gutenberg/build/react-refresh-runtime/index.min.asset.php';

			if ( file_exists( $path ) && is_readable( $path ) ) {
				$asset = include $path;

				if ( is_array( $asset ) && isset( $asset['dependencies'], $asset['version'] ) ) {
					yield new NBPC_Reg_Script(
						'wp-react-refresh-runtime',
						WP_PLUGIN_URL . '/gutenberg/build/react-refresh-runtime/index.min.js',
						$asset['dependencies'],
						$asset['version'],
						true
					);

					return;
				}
			}
		}

		yield;
	}
}
