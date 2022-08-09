<?php
/**
 * Naran Boilerplate Core
 *
 * traits/trait-nbpc-template-impl.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! trait_exists( 'NBPC_Template_Impl' ) ) {
	trait NBPC_Template_Impl {
		/**
		 * Current block key.
		 */
		protected string $current_key = '';

		/**
		 * Check if template is started.
		 */
		protected bool $template_started = false;

		/**
		 * Template parents. Relative paths to template.
		 */
		protected array $template_parents = [];

		/**
		 * Assigned values in templates.
		 */
		protected array $template_assigned = [];

		/**
		 * @param string $tmpl_type Template type.
		 * @param string $relpath   Relative path to template.
		 * @param string $variant   Varaition.
		 * @param string $ext       Extension.
		 *
		 * @return string|false
		 */
		protected function locate_file(
			string $tmpl_type,
			string $relpath,
			string $variant = '',
			string $ext = 'php'
		): string|false {
			$tmpl_type = trim( $tmpl_type, '\\/' );
			$relpath   = trim( $relpath, '\\/' );
			$variant   = sanitize_key( $variant );
			$ext       = ltrim( $ext, '.' );

			$cache_name = "$tmpl_type:$relpath:$variant:$ext";
			$cache      = NBPC_Main_Base::get_instance()->get( 'nbpc:locate_file', [] );

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
				$plug = nbpc_is_plugin() ? dirname( nbpc_main_file() ) : false;

				if ( nbpc_is_theme() ) {
					$paths = [
						$variant ? "$styl/includes/templates/$dir/$file_name-$variant.$ext" : false,
						"$styl/includes/templates/$dir/$file_name.$ext",
						$variant ? "$tmpl/includes/templates/$dir/$file_name-$variant.$ext" : false,
						"$tmpl/includes/templates/$dir/$file_name.$ext",
					];
				} else {
					$paths = [
						$variant ? "$styl/nbpc/$dir/$file_name-$variant.$ext" : false,
						"$styl/nbpc/$dir/$file_name.$ext",
						$variant ? "$tmpl/nbpc/$dir/$file_name-$variant.$ext" : false,
						"$tmpl/nbpc/$dir}/$file_name.$ext",
						$plug && $variant ? "$plug/includes/templates/$dir/$file_name-$variant.$ext" : false,
						$plug ? "$plug/includes/templates/$dir/$file_name.$ext" : false,
					];
				}

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

				NBPC_Main_Base::get_instance()->set( 'nbpc:locate_file', $cache );
			}

			return $located;
		}

		protected function render_file( string $___file_name___, array $context = [], bool $echo = true ): string {
			if ( ! file_exists( $___file_name___ ) || ! is_readable( $___file_name___ ) || $this->template_started ) {
				return '';
			}

			$this->template_started  = true; // Lock.
			$this->template_parents  = [];
			$this->template_assigned = [];

			if ( ! empty( $context ) ) {
				// phpcs:ignore WordPress.PHP.DontExtract
				extract( $context, EXTR_SKIP );
			}
			unset( $context );

			// Include and get the bottomost template content.
			ob_start();
			include $___file_name___;
			$this->assign( 'content', ob_get_clean() );

			// Process enqueued parent templates.
			$template_parents       = array_reverse( $this->template_parents );
			$this->template_parents = [];
			$buffered               = [];

			for ( $i = 0; $i < count( $template_parents ); ++ $i ) {
				$file = $this->locate_file( 'template', $template_parents[ $i ] );

				if ( file_exists( $file ) && is_readable( $file ) ) {
					// Process template file.
					ob_start();
					include $file;
					$buffered[] = ob_get_clean();

					// If extend() is called in the $file, then $template_parents should have one or more template.
					if ( $this->template_parents ) {
						$template_parents       = [ ...$template_parents, ...array_reverse( $this->template_parents ) ];
						$this->template_parents = [];
					}
				}
			}

			$this->template_started = false; // Release.

			// $output is generated from file inclusion.
			$output = implode( "\n", array_filter( array_map( 'trim', array_reverse( $buffered ) ) ) ) .
			          "\n" . $this->fetch( 'content' );

			if ( $echo ) {
				echo $output;
				return '';
			}

			return $output;
		}

		protected function enqueue_ejs( string $relpath, array $context = [], string $variant = '' ): self {
			$ejs_queue = NBPC_Main_Base::get_instance()->get( 'nbpc:ejs_queue' );

			if ( ! $ejs_queue ) {
				$ejs_queue = new NBPC_EJS_Queue();
				NBPC_Main_Base::get_instance()->set( 'nbpc:ejs_queue', $ejs_queue );
			}

			$ejs_queue->enqueue( $relpath . ( $variant ? "-$variant" : '' ), compact( 'context', 'variant' ) );

			return $this;
		}

		/**
		 * Render a template file.
		 *
		 * @param string      $relpath Relative path to the theme. Do not append file extension.
		 * @param array       $context Context array.
		 * @param string|bool $variant Variant slug. If explicitly boolean, regarded as $echo.
		 * @param bool        $echo
		 * @param string      $ext
		 *
		 * @return string
		 */
		protected function render(
			string $relpath,
			array $context = [],
			string|bool $variant = '',
			bool $echo = true,
			string $ext = 'php'
		): string {
			if ( is_bool( $variant ) ) {
				$echo    = $variant;
				$variant = '';
			}
			return $this->render_file( $this->locate_file( 'template', $relpath, $variant, $ext ), $context, $echo );
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

		/**
		 * Extend parent template.
		 *
		 * @param string $parent Relative path to template directory.
		 *
		 * @return $this
		 */
		protected function extend( string $parent ): self {
			$this->template_parents[] = $parent;

			return $this;
		}

		/**
		 * Assign a value.
		 *
		 * @param string $key
		 * @param mixed  $value
		 *
		 * @return $this
		 */
		protected function assign( string $key, mixed $value ): self {
			$this->template_assigned[ $key ] = $value;

			return $this;
		}

		/**
		 * Fetch a value.
		 *
		 * @param string     $key
		 * @param mixed|null $default
		 *
		 * @return mixed
		 */
		protected function fetch( string $key, mixed $default = null ): mixed {
			return $this->template_assigned[ $key ] ?? $default;
		}

		/**
		 * Assign a value by block.
		 *
		 * @param string $key
		 *
		 * @return void
		 * @throws RuntimeException
		 */
		protected function block_start( string $key ): void {
			if ( $this->current_key ) {
				throw new RuntimeException( 'Assign block cannot be nested.' );
			}
			$this->current_key = $key;
			ob_start();
		}

		/**
		 * Finish assign block.
		 *
		 * @throws RuntimeException
		 */
		protected function block_end(): void {
			if ( ! $this->current_key ) {
				throw new RuntimeException( 'No assign block started.' );
			}
			$this->assign( $this->current_key, ob_get_clean() );
			$this->current_key = '';
		}
	}
}
