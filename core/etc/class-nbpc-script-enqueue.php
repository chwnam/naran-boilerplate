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
		}

		public function localize( $l18n = [] ) {
			wp_localize_script( $this->handle, $this->__get_script_object_name(), $l18n );

			return $this->parent;
		}

		/**
		 * @param string $handle
		 *
		 * @return object|NBPC_Module
		 * @uses NBPC_Template_Impl::enqueue_ejs()
		 */
		public function enqueue_style( string $handle ) {
			return $this->parent->enqueue_style( $handle );
		}

		/**
		 * @param string $relpath
		 * @param array  $context
		 * @param string $variant
		 *
		 * @return object|NBPC_Module
		 * @uses NBPC_Template_Impl::enqueue_ejs()
		 */
		public function enqueue_ejs( string $relpath, array $context = [], string $variant = '' ) {
			return $this->parent->enqueue_ejs( $relpath, $context, $variant );
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
