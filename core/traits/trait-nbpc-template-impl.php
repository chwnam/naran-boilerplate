<?php
/**
 * NBPC: template trait
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! trait_exists( 'NBPC_Template_Impl' ) ) {
	trait NBPC_Template_Impl {
		protected function locate_file( string $tmpl_type, string $relpath, string $variant = '', $ext = 'php' ) {
			$tmpl_type = trim( $tmpl_type, '\\/' );
			$relpath   = trim( $relpath, '\\/' );
			$variant   = sanitize_key( $variant );
			$ext       = ltrim( $ext, '.' );

			$cache_name = "$tmpl_type:$relpath:$variant:$ext";
			$cache      = nbpc()->get( 'nbpc:locate_file', [] );

			if ( isset( $cache[ $cache_name ] ) ) {
				$located = $cache[ $cache_name ];
			} else {
				$dir       = dirname( $relpath );
				$file_name = wp_basename( $relpath );

				if ( empty( $dir ) ) {
					$dir = '.';
				}

				$styl = get_stylesheet_directory();
				$tmpl = get_template_directory();
				$plug = nbpc_is_plugin() ? dirname( nbpc()->get_main_file() ) : false;

				$paths = [
					$variant ? "$styl/nbpc/$dir/$file_name-$variant.$ext" : false,
					"$styl/nbpc/$dir/$file_name.$ext",
					$variant ? "$tmpl/nbpc/$dir/$file_name-$variant.$ext" : false,
					"$tmpl/nbpc/$dir}/$file_name.$ext",
					$plug && $variant ? "$plug/includes/templates/$dir/$file_name-$variant.$ext" : false,
					$plug ? "$plug/includes/templates/$dir/$file_name.$ext" : false,
				];

				$paths   = apply_filters( 'nbpc_locate_file_paths', array_filter( $paths ), $cache_name );
				$located = false;

				foreach ( (array) $paths as $path ) {
					if ( file_exists( $path ) && is_readable( $path ) ) {
						$located = $path;
						break;
					}
				}

				$located = apply_filters( 'nbpc_located_path', $located, $tmpl_type, $relpath, $variant, $ext );

				$cache[ $cache_name ] = $located;

				nbpc()->set( 'nbpc:locate_file', $cache );
			}

			return $located;
		}

		protected function render_file( string $___file_name___, array $context = [], bool $echo = true ): string {
			if ( file_exists( $___file_name___ ) && is_readable( $___file_name___ ) ) {
				if ( ! $echo ) {
					ob_start();
				}

				// static lambda immediately invoked.
				// This prevents from accessing from the template inside.
				( static function () use ( $context, $___file_name___ ) {
					if ( ! empty( $context ) ) {
						// phpcs:ignore WordPress.PHP.DontExtract
						extract( $context, EXTR_SKIP );
					}
					unset( $context );
					include $___file_name___;
				} )();

				if ( ! $echo ) {
					return ob_get_clean();
				}
			}

			return '';
		}

		protected function enqueue_ejs( string $relpath, array $context = [], string $variant = '' ): self {
			$ejs_queue = nbpc()->get( 'nbpc:ejs_queue' );

			if ( ! $ejs_queue ) {
				$ejs_queue = new NBPC_EJS_Queue();
				nbpc()->set( 'nbpc:ejs_queue', $ejs_queue );
			}

			$ejs_queue->enqueue( $relpath . ( $variant ? "-$variant" : '' ), compact( 'context', 'variant' ) );

			return $this;
		}

		/**
		 * Render a template file.
		 *
		 * @param string $relpath Relative path to the theme. Do not append file extension.
		 * @param array  $context Context array.
		 * @param string $variant Variant slug.
		 * @param bool   $echo
		 * @param string $ext
		 *
		 * @return string
		 */
		protected function render(
			string $relpath,
			array $context = [],
			string $variant = '',
			bool $echo = true,
			string $ext = 'php'
		): string {
			return $this->render_file(
				$this->locate_file( 'template', $relpath, $variant, $ext ),
				$context,
				$echo
			);
		}

		protected function enqueue_script( string $handle ): self {
			if ( wp_script_is( $handle, 'registered' ) ) {
				wp_enqueue_script( $handle );
			}

			return $this;
		}

		protected function enqueue_style( string $handle ): self {
			if ( wp_style_is( $handle, 'registered' ) ) {
				wp_enqueue_style( $handle );
			}

			return $this;
		}

		/**
		 * Return a script helper.
		 *
		 * @param string $handle
		 *
		 * @return NBPC_Script_Helper
		 */
		protected function script( string $handle ): NBPC_Script_Helper {
			return new NBPC_Script_Helper( $this, $handle );
		}

		/**
		 * Return a style helper.
		 *
		 * @param string $handle
		 *
		 * @return NBPC_Style_Helper
		 */
		protected function style( string $handle ): NBPC_Style_Helper {
			return new NBPC_Style_Helper( $this, $handle );
		}
	}
}
