<?php
/**
 * NBPC: Theme hierarchy
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Theme_Hierarchy' ) ) {
	class NBPC_Theme_Hierarchy {
		public string $post_type = '';

		public string $taxonomy = '';

		public string $page_template = '';

		public bool $is_archive = false;

		public bool $is_singular = false;

		public function __construct() {
			global $wp_the_query;

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
	}
}
