<?php
/**
 * NBPC: Style method chain helper
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Style_Helper' ) ) {
	class NBPC_Style_Helper {
		/**
		 * Parent module object
		 *
		 * @var NBPC_Template_Impl|NBPC_Module
		 */
		private $parent;

		/**
		 * Script handle
		 *
		 * @var string
		 */
		private string $handle;

		/**
		 * Constructor method
		 *
		 * @param NBPC_Template_Impl|NBPC_Module $parent Parent module object.
		 * @param string                         $handle Script handle.
		 */
		public function __construct( $parent, string $handle ) {
			$this->parent = $parent;
			$this->handle = $handle;
		}

		/**
		 * Return another script helper.
		 *
		 * @param string $handle Handle string.
		 *
		 * @return NBPC_Script_Helper
		 */
		public function script( string $handle ): NBPC_Script_Helper {
			return new NBPC_Script_Helper( $this->parent, $handle );
		}

		/**
		 * Return another style helper.
		 *
		 * @param string $handle Handle string.
		 *
		 * @return NBPC_Style_Helper
		 */
		public function style( string $handle ): NBPC_Style_Helper {
			return new NBPC_Style_Helper( $this->parent, $handle );
		}

		/**
		 * Enqueue the style.
		 *
		 * @return self
		 */
		public function enqueue(): self {
			wp_enqueue_style( $this->handle );
			return $this;
		}

		/**
		 * Finish call chain
		 *
		 * @return NBPC_Module|NBPC_Template_Impl
		 */
		public function then() {
			return $this->parent;
		}
	}
}
