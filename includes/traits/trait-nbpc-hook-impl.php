<?php
/**
 * NBPC: Hook trait
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! trait_exists( 'NBPC_Hook_Impl' ) ) {
	trait NBPC_Hook_Impl {
		/**
		 * Add action
		 *
		 * @param string                $tag
		 * @param callable|array|string $function_to_add
		 * @param int|null              $priority
		 * @param int                   $accepted_args
		 *
		 * @return self
		 */
		protected function add_action(
			string $tag,
			$function_to_add,
			?int $priority = null,
			int $accepted_args = 1
		): self {
			add_action(
				$tag,
				$this->parse_callback( $function_to_add ),
				$this->get_priority( $priority ),
				$accepted_args
			);

			return $this;
		}

		/**
		 * Add filter
		 *
		 * @param string                $tag
		 * @param callable|array|string $function_to_add
		 * @param int|null              $priority
		 * @param int                   $accepted_args
		 *
		 * @return self
		 */
		protected function add_filter(
			string $tag,
			$function_to_add,
			?int $priority = null,
			int $accepted_args = 1
		): self {
			add_action(
				$tag,
				$this->parse_callback( $function_to_add ),
				$this->get_priority( $priority ),
				$accepted_args
			);

			return $this;
		}

		/**
		 * Remove action
		 *
		 * @param string                $tag
		 * @param callable|array|string $function_to_remove
		 * @param int|null              $priority
		 *
		 * @return $this
		 */
		protected function remove_action(
			string $tag,
			$function_to_remove,
			?int $priority = null
		): self {
			remove_action(
				$tag,
				$this->parse_callback( $function_to_remove ),
				$this->get_priority( $priority )
			);

			return $this;
		}

		/**
		 * Remove filter
		 *
		 * @param string                $tag
		 * @param callable|array|string $function_to_remove
		 * @param int|null              $priority
		 *
		 * @return $this
		 */
		protected function remove_filter(
			string $tag,
			$function_to_remove,
			?int $priority = null
		): self {
			remove_filter(
				$tag,
				$this->parse_callback( $function_to_remove ),
				$this->get_priority( $priority )
			);

			return $this;
		}

		/**
		 * Parse callback function for actions and filters.
		 *
		 * @param $item
		 *
		 * @return callable|null
		 */
		protected function parse_callback( $item ): ?callable {
			if ( is_string( $item ) && method_exists( $this, $item ) ) {
				return [ $this, $item ];
			} elseif ( is_callable( $item ) ) {
				return $item;
			}

			return null;
		}

		/**
		 * Get the priority
		 *
		 * @param $priority
		 *
		 * @return int
		 */
		protected function get_priority( $priority ): int {
			return is_null( $priority ) ? nbpc()->get_priority() : intval( $priority );
		}
	}
}
