<?php
/**
 * NBPC: Shortcode register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Shortcode' ) ) {
	abstract class NBPC_Register_Base_Shortcode implements NBPC_Register {
		use NBPC_Hook_Impl;

		/**
		 * @var array<string, callable|string>
		 */
		private array $real_callbacks;

		/**
		 * @var array <string, callable|string>
		 */
		private array $heading_actions;

		/**
		 * @var array <string>
		 */
		private array $found_tags;

		public function __construct() {
			$this
				->add_action( 'init', 'register' )
				->add_action( 'wp', 'heading_actions_handler' )
			;

			$this->real_callbacks  = [];
			$this->heading_actions = [];
			$this->found_tags      = [];
		}

		public function register() {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Shortcode ) {
					$item->register( [ $this, 'dispatch' ] );
					$this->real_callbacks[ $item->tag ] = $item->callback;
					if ( $item->heading_action ) {
						$this->heading_actions[ $item->tag ] = $item->heading_action;
					}
				}
			}
		}

		/**
		 * Detect shortcodes and do something before headers are sent.
		 *
		 * @throws NBPC_Callback_Exception
		 */
		public function heading_actions_handler() {
			if ( is_singular() && $this->heading_actions ) {
				$this->find_shortcode( get_post_field( 'post_content', null, 'raw' ) );
				foreach ( array_unique( $this->found_tags ) as $tag ) {
					$callback = nbpc_parse_callback( $this->heading_actions[ $tag ] );
					if ( is_callable( $callback ) ) {
						call_user_func( $callback, $tag );
					}
				}
			}
		}

		/**
		 * Shortcode callback.
		 *
		 * It invokes real callbacks by collected tags.
		 *
		 * @param array|string $atts
		 * @param string       $enclosed
		 * @param string       $tag
		 *
		 * @return string
		 * @throws NBPC_Callback_Exception
		 */
		public function dispatch( $atts, string $enclosed, string $tag ): string {
			$callback = nbpc_parse_callback( $this->real_callbacks[ $tag ] ?? '__return_empty_string' );

			if ( is_callable( $callback ) ) {
				return call_user_func_array( $callback, [ $atts, $enclosed, $tag ] );
			} else {
				return '';

			}
		}

		/**
		 * Find and collect shortcode tags in the content.
		 *
		 * @param string $content
		 */
		protected function find_shortcode( string $content ) {
			if ( false === strpos( $content, '[' ) ) {
				return;
			}

			/**
			 * @var array $matches idx 2: shortocde name. (tag)
			 *                     idx 5: enclosed text.
			 *
			 * @see get_shortcode_regex()
			 */
			preg_match_all(
				'/' . get_shortcode_regex( array_keys( $this->heading_actions ) ) . '/',
				$content,
				$matches,
				PREG_SET_ORDER
			);

			if ( empty( $matches ) ) {
				return;
			}

			foreach ( $matches as $shortcode ) {
				if ( isset( $this->heading_actions[ $shortcode[2] ] ) ) {
					$this->found_tags[] = $shortcode[2];
				} elseif ( ! empty( $shortcode[5] ) ) {
					$this->find_shortcode( $shortcode[5] );
				}
			}
		}
	}
}
