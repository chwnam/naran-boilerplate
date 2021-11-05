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
		 * Modified add_action().
		 * - Method chaining available.
		 * - More convenient callback designation.
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
				$this->__hook_parse_callback( $function_to_add ),
				$this->__hook_get_priority( $priority ),
				$accepted_args
			);

			return $this;
		}

		/**
		 * Modified add_filter().
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
				$this->__hook_parse_callback( $function_to_add ),
				$this->__hook_get_priority( $priority ),
				$accepted_args
			);

			return $this;
		}

		/**
		 * Modified remove_action().
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
				$this->__hook_parse_callback( $function_to_remove ),
				$this->__hook_get_priority( $priority )
			);

			return $this;
		}

		/**
		 * Modified remove_filter()
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
				$this->__hook_parse_callback( $function_to_remove ),
				$this->__hook_get_priority( $priority )
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
		private function __hook_parse_callback( $item ): ?callable {
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
		private function __hook_get_priority( $priority ): int {
			return is_null( $priority ) ? nbpc()->get_priority() : intval( $priority );
		}
	}
}
