<?php
/**
 * NBPC: Uninstall only functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// This is uninstall script. Raw queries can be allowed.
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

if ( ! function_exists( 'nbpc_cleanup_meta' ) ) {
	/**
	 * Remove plugin's meta records.
	 */
	function nbpc_cleanup_meta() {
		global $wpdb;

		$meta_keys = [
			'comment' => [],
			'post'    => [],
			'term'    => [],
			'user'    => [],
		];

		foreach ( array_keys( $meta_keys ) as $key ) {
			$meta = nbpc()->registers->{$key . '_meta'};
			if ( $meta ) {
				foreach ( $meta->get_items() as $item ) {
					if ( $item instanceof NBPC_Reg_Meta ) {
						$meta_keys[ $key ][] = $item->get_key();
					}
				}
			}
			$meta_keys[ $key ] = array_filter( array_unique( $meta_keys[ $key ] ) );
		}

		foreach ( $meta_keys as $object_type => $keys ) {
			if ( ! empty( $keys ) ) {
				$placeholder = implode( ', ', array_pad( [], count( $keys ), '%s' ) );
				$query       = "DELETE FROM $wpdb->prefix{$object_type}meta WHERE meta_key IN ($placeholder)";
				$wpdb->query( $wpdb->prepare( $query, $keys ) );
			}
		}
	}
}


if ( ! function_exists( 'nbpc_cleanup_option' ) ) {
	/**
	 * Remove plugin's option records.
	 */
	function nbpc_cleanup_option() {
		global $wpdb;

		$option_names = [];

		$option = nbpc()->registers->option;
		if ( $option ) {
			foreach ( $option->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Option ) {
					$option_names[] = $item->get_option_name();
				}
			}
		}

		$option_names = array_filter( array_unique( $option_names ) );

		if ( $option_names ) {
			$placeholder = implode( ', ', array_pad( [], count( $option_names ), '%s' ) );
			$query       = "DELETE FROM $wpdb->options WHERE option_name IN ($placeholder)";
			$wpdb->query( $wpdb->prepare( $query, $option_names ) );
		}
	}
}


if ( ! function_exists( 'nbpc_cleanup_terms' ) ) {
	/**
	 * Remove plugin's terms.
	 */
	function nbpc_cleanup_terms() {
		global $wpdb;

		$taxonomies = [];

		$taxonomy = nbpc()->registers->taxonomy;
		if ( $taxonomy ) {
			foreach ( $taxonomy->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Taxonomy ) {
					$taxonomies[] = $item->taxonomy;
				}
			}
		}

		$taxonomies = array_filter( array_unique( $taxonomies ) );
		if ( $taxonomies ) {
			$placeholder = implode( ', ', array_pad( [], count( $taxonomies ), '%s' ) );

			$sql = "SELECT term_taxonomy_id, term_id FROM $wpdb->term_taxonomy" .
			       " WHERE taxonomy IN ($placeholder)";

			$terms = $wpdb->get_results( $wpdb->prepare( $sql, $taxonomies ) );

			if ( is_array( $terms ) && count( $terms ) ) {
				// delete term_relationships, term_taxonomies, terms.
				$tax_ids         = wp_list_pluck( $terms, 'term_taxonomy_id' );
				$tax_placeholder = implode( ', ', array_pad( [], count( $tax_ids ), '%d' ) );

				$t_ids         = wp_list_pluck( $terms, 'term_id' );
				$t_placeholder = implode( ', ', array_pad( [], count( $t_ids ), '%d' ) );

				$obj_query = "DELETE FROM $wpdb->term_relationships WHERE term_taxonomy_id IN ($tax_placeholder)";
				$tax_query = "DELETE FROM $wpdb->term_taxonomy WHERE term_taxonomy_id IN ($tax_placeholder)";
				$t_query   = "DELETE FROM $wpdb->terms WHERE term_id IN ($t_placeholder)";

				$wpdb->query( $wpdb->prepare( $obj_query, $tax_ids ) );
				$wpdb->query( $wpdb->prepare( $tax_query, $tax_ids ) );
				$wpdb->query( $wpdb->prepare( $t_query, $t_ids ) );
			}
		}
	}
}


if ( ! function_exists( 'nbpc_cleanup_posts' ) ) {
	/**
	 * Remove plugin's posts.
	 */
	function nbpc_cleanup_posts() {
		global $wpdb;

		$post_types = [];

		$post_type = nbpc()->registers->post_type;
		if ( $post_type ) {
			foreach ( $post_type->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Post_Type ) {
					$post_types[] = $item->post_type;
				}
			}
		}

		$post_types = array_filter( array_unique( $post_types ) );
		if ( $post_types ) {
			$placeholder = implode( ', ', array_pad( [], count( $post_types ), '%s' ) );
			$query       = "DELETE FROM $wpdb->posts WHERE post_type IN ($placeholder)";
			$wpdb->query( $wpdb->prepare( $query, $post_types ) );
		}
	}
}

// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
