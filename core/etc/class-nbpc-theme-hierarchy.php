<?php
/**
 * NBPC: Theme hierarchy
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Theme_Hierarchy' ) ) {
	final class NBPC_Theme_Hierarchy {
		private string $post_type = '';

		private string $taxonomy = '';

		private string $page_template = '';

		private bool $is_archive = false;

		private bool $is_singular = false;

		/**
		 * @var NBPC_Front_Module|string|null
		 */
		private $front_module = null;

		private static ?NBPC_Theme_Hierarchy $instance = null;

		/**
		 * Get the instance.
		 *
		 * @return NBPC_Theme_Hierarchy
		 */
		public static function get_instance(): NBPC_Theme_Hierarchy {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Private constructor.
		 *
		 * Use get_instalce().
		 */
		private function __construct() {
			global $wp_the_query;

			if ( ! $wp_the_query ) {
				return;
			}

			$object = $wp_the_query->get_queried_object();

			if ( $object instanceof WP_Term ) {
				$this->is_archive = true;
				$this->post_type  = $wp_the_query->get( 'post_type' );
				$this->taxonomy   = $object->taxonomy;
			} elseif ( $object instanceof WP_Post_Type ) {
				$this->is_archive = true;
				$this->post_type  = $object->name;
				$this->taxonomy   = $wp_the_query->get( 'taxonomy' );
			} elseif ( $object instanceof WP_Post ) {
				$this->is_singular = true;
				$this->post_type   = $object->post_type;
				$this->taxonomy    = $wp_the_query->get( 'taxonomy' );
				$page_template     = get_page_template_slug( $object );
				if ( $page_template ) {
					$this->page_template = $page_template;
				}
			} elseif (
				empty( $wp_the_query->get( 'post_type' ) ) &&
				empty( $wp_the_query->get( 'cat' ) ) &&
				empty( $wp_the_query->get( 'tag' ) ) &&
				empty( $wp_the_query->get( 'p' ) )
			) {
				$this->is_singular = false;
				$this->is_archive  = true;
				$this->post_type   = 'post';
				$this->taxonomy    = '';
			}
		}

		/**
		 * Get front module.
		 *
		 * @return NBPC_Front_Module|string|null
		 */
		public function get_front_module() {
			return $this->front_module;
		}

		/**
		 * Set front module.
		 *
		 * @param NBPC_Front_Module|string $module Front module instance, or class name
		 *
		 * @return void
		 */
		public function set_front_module( $module ) {
			$this->front_module = $module;
		}

		public function is_archive(): bool {
			return $this->is_archive;
		}

		public function is_singular(): bool {
			return $this->is_singular;
		}

		public function get_post_type(): string {
			return $this->post_type;
		}

		public function get_page_template(): string {
			return $this->page_template;
		}

		public function get_taxonomy(): string {
			return $this->taxonomy;
		}

		/**
		 * @throws Exception
		 */
		public function __clone() {
			throw new Exception( 'This object does not suppoert __clone().' );
		}

		/**
		 * @throws Exception
		 */
		public function __sleep() {
			throw new Exception( 'This object does not suppoert __sleep().' );
		}

		/**
		 * @throws Exception
		 */
		public function __wakeup() {
			throw new Exception( 'This object does not suppoert __wakeup().' );
		}
	}
}
