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

			$cache_name = "{$tmpl_type}:{$relpath}:{$variant}";
			$cache      = nbpc()->get( 'hws:locate_file', [] );

			if ( isset( $cache[ $cache_name ] ) ) {
				$located = $cache[ $cache_name ];
			} else {
				$dir       = dirname( $relpath );
				$file_name = wp_basename( $relpath );

				if ( empty( $dir ) ) {
					$dir = '.';
				}

				$paths = [
					$variant ? STYLESHEETPATH . "/nbpc/{$dir}/{$file_name}-{$variant}.{$ext}" : false,
					STYLESHEETPATH . "/nbpc/{$dir}/{$file_name}.{$ext}",
					$variant ? TEMPLATEPATH . "/nbpc/{$dir}/{$file_name}-{$variant}.{$ext}" : false,
					TEMPLATEPATH . "/nbpc/{$dir}/{$file_name}.{$ext}",
					$variant ? plugin_dir_path( nbpc()->get_main_file() ) . "includes/templates/{$dir}/{$file_name}-{$variant}.{$ext}" : false,
					plugin_dir_path( nbpc()->get_main_file() ) . "includes/templates/{$dir}/{$file_name}.{$ext}",
				];

				$paths   = apply_filters( 'nbpc_locate_file_paths', array_filter( $paths ) );
				$located = false;

				foreach ( (array) $paths as $path ) {
					if ( file_exists( $path ) && is_readable( $path ) ) {
						$located = $path;
						break;
					}
				}

				$cache[ $cache_name ] = $located;
				nbpc()->set( 'nbpc:locate_file', $cache );
			}

			return $located;
		}

		protected function render_file( string $file_name, array $context = [], bool $echo = true ): string {
			if ( file_exists( $file_name ) && is_readable( $file_name ) ) {
				if ( ! empty( $context ) ) {
					extract( $context, EXTR_SKIP );
				}

				if ( ! $echo ) {
					ob_start();
				}

				include $file_name;

				if ( ! $echo ) {
					return ob_get_clean();
				}
			}

			return '';
		}

		protected function enqueue_ejs( string $relpath, array $context = [], string $variant = '' ): void {
			$ejs_queue = nbpc()->get( 'main:ejs_queue' );

			if ( ! $ejs_queue ) {
				$ejs_queue = new NBPC_EJS_Queue();
				nbpc()->set( 'main:ejs_queue', $ejs_queue );
			}

			$ejs_queue->enqueue( $relpath . ( $variant ? "-{$variant}" : '' ), compact( 'context', 'variant' ) );
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

		protected function enqueue_script( string $handle, $once = false ): NBPC_Sciprt_Enqueue {
			return new NBPC_Sciprt_Enqueue( $this, $handle, $once );
		}

		protected function enqueue_style( string $handle ): self {
			if ( wp_style_is( $handle, 'registered' ) ) {
				wp_enqueue_style( $handle );
			}

			return $this;
		}
	}
}
