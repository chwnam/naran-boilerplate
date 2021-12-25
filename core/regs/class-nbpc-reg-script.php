<?php
/**
 * NBPC: Script reg.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Script' ) ) {
	class NBPC_Reg_Script implements NBPC_Reg {
		const WP_SCRIPT = 'nbpc-wp-script-generated';

		public string $handle;

		public string $src;

		/** @var array|string */
		public $deps;

		/** @var string|bool */
		public $ver;

		public bool $in_footer;

		/**
		 * NOTE: If a script is built from wp-scripts, check these:
		 * - 'src'  is relative to assets/js.
		 * - 'deps' must be 'WP_SCRIPT' constant.
		 *
		 * @param string           $handle
		 * @param string           $src
		 * @param array|string     $deps
		 * @param null|string|bool $ver null: Use plugin version / true: Use WordPress version / false: No version
		 * @param bool             $in_footer
		 */
		public function __construct(
			string $handle,
			string $src,
			$deps = [],
			$ver = null,
			bool $in_footer = false
		) {
			$this->handle    = $handle;
			$this->src       = $src;
			$this->deps      = $deps;
			$this->ver       = is_null( $ver ) ? nbpc()->get_version() : $ver;
			$this->in_footer = $in_footer;
		}

		public function register( $dispatch = null ) {
			if ( $this->handle && $this->src && ! wp_script_is( $this->handle, 'registered' ) ) {
				if ( self::WP_SCRIPT === $this->deps ) {
					// When WP_SCRIPT is used, $src must be a relative path to assets/js.
					// But why not if it is handled here?
					$root = plugin_dir_url( nbpc()->get_main_file() ) . 'assets/js/';
					if ( 0 === strpos( $this->src, $root ) ) {
						$this->src = substr( $this->src, strlen( $root ) );
					}

					$dir  = trim( dirname( $this->src ), '/\\' );
					$file = pathinfo( $this->src, PATHINFO_FILENAME ) . '.asset.php';
					$path = path_join( dirname( nbpc()->get_main_file() ), "assets/js/$dir/$file" );

					if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
						return;
					}

					$info = include $path;

					$this->src       = plugins_url( "assets/js/$this->src", nbpc()->get_main_file() );
					$this->deps      = $info['dependencies'] ?? [];
					$this->ver       = $info['version'] ?? nbpc()->get_version();
					$this->in_footer = true;
				}

				wp_register_script(
					$this->handle,
					$this->src,
					$this->deps,
					// Three cases.
					// 1. string:     As-is.
					// 2. true:       Use WordPress version string.
					// 3. null/false: Converted to null. An empty version string.
					$this->ver ?: null,
					$this->in_footer
				);
			}
		}
	}
}
