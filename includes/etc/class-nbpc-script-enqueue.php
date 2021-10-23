<?php
/**
 * NBPC: Script enqueue
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Sciprt_Enqueue' ) ) {
	class NBPC_Sciprt_Enqueue {
		/** @var NBPC_Module|object */
		private $parent;

		private string $handle;

		public function __construct( $parent, string $handle, bool $once = false ) {
			$this->parent = $parent;
			$this->handle = $handle;

			if ( wp_script_is( $handle, 'registered' ) && ! ( $once && wp_script_is( $handle ) ) ) {
				wp_enqueue_script( $handle );
			}

			return $this;
		}

		public function localize( $l18n = [] ): self {
			wp_localize_script( $this->handle, $this->__get_script_object_name(), $l18n );

			return $this;
		}

		public function render(
			string $relpath,
			array $context = [],
			string $variant = '',
			bool $echo = true,
			string $ext = 'php'
		): string {
			return $this->parent->render( $relpath, $context, $variant, $echo, $ext );
		}

		private function __get_script_object_name(): string {
			$split = preg_split( '/[-_]/', $this->handle );

			if ( $split ) {
				return $split[0] . implode( '', array_map( 'ucfirst', array_slice( $split, 1 ) ) );
			} else {
				return '';
			}
		}
	}
}
