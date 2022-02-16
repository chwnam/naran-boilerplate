<?php
/**
 * NBPC: Style reg.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Style' ) ) {
	class NBPC_Reg_Style implements NBPC_Reg {
		public string $handle;

		public string $src;

		public array $deps;

		/** @var string|bool */
		public $ver;

		public string $media;

		/**
		 * Constructor method
		 *
		 * @param string           $handle
		 * @param string           $src
		 * @param array            $deps
		 * @param string|bool|null $ver null: Use plugin version / true: Use WordPress version / false: No version.
		 * @param string           $media
		 */
		public function __construct(
			string $handle,
			string $src,
			array $deps = [],
			$ver = null,
			string $media = 'all'
		) {
			$this->handle = $handle;
			$this->src    = $src;
			$this->deps   = $deps;
			$this->ver    = is_null( $ver ) ? nbpc()->get_version() : $ver;
			$this->media  = $media;
		}

		public function register( $dispatch = null ): void {
			if ( $this->handle && $this->src && ! wp_style_is( $this->handle, 'registered' ) ) {
				wp_register_style(
					$this->handle,
					$this->src,
					$this->deps,
					// Three cases.
					// 1. string:     As-is.
					// 2. true:       Use WordPress version string.
					// 3. null/false: Converted to null. An empty version string.
					$this->ver ?: null,
					$this->media
				);
			}
		}
	}
}
