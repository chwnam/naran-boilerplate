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
		 * @var NBPC_Front_Module|string|false|null
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

			if ( $wp_the_query->is_archive() ) {
				// Archive
				$this->is_archive = true;
				if ( $wp_the_query->is_post_type_archive ) {
					$this->post_type = $wp_the_query->get( 'post_type' );
				} elseif ( $wp_the_query->is_tax ) {
					global $wp_taxonomies;
					foreach ( array_keys( $wp_taxonomies ) as $taxonomy ) {
						if ( isset( $wp_the_query->query_vars[ $taxonomy ] ) ) {
							$this->taxonomy = $taxonomy;
							break;
						}
					}
				} elseif ( $wp_the_query->is_category ) {
					$this->taxonomy = 'category';
				} elseif ( $wp_the_query->is_tag ) {
					$this->taxonomy = 'post_tag';
				}
			} elseif ( $wp_the_query->is_singular() ) {
				// Singular
				$this->is_singular = true;
				$this->post_type   = $wp_the_query->get( 'post_type' );
				if ( ! $this->post_type ) {
					if ( $wp_the_query->is_page ) {
						$this->post_type     = 'page';
						$this->page_template = get_page_template_slug( $wp_the_query->queried_object ) ?: '';
					} else {
						$this->post_type = 'post';
					}
				}
			}

			do_action_ref_array( 'nbpc_theme_hierarchy', [ &$this ] );
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
			if ( is_string( $module ) ) {
				$instance = nbpc_parse_module( $module );
				if ( $instance ) {
					$this->front_module = $instance;
				}
			} else {
				$this->front_module = $module;
			}
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

		public function get_fallback(): NBPC_Front_Fallback {
			return new NBPC_Front_Fallback();
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
