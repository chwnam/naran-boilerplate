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
		 * @param string                     $tag             Hook name. It is required.
		 * @param callable|array|string|null $function_to_add If it is a type of:
		 *                                                    - callable: directly used.
		 *                                                    - callable: directly used.
		 *                                                    - string:   the current object's method.
		 *                                                    - null:     method name comes from $tag.
		 * @param int|null                   $priority        If null, priority comes from 'NBPC_PRIORITY' constant.
		 * @param int                        $accepted_args   Number of accepted arguments.
		 *
		 * @return self
		 */
		protected function add_action(
			string $tag,
			$function_to_add = null,
			?int $priority = null,
			int $accepted_args = 1
		): self {
			add_action(
				$tag,
				$this->__hook_parse_callback( $function_to_add, $tag ),
				$this->__hook_get_priority( $priority ),
				$accepted_args
			);

			return $this;
		}

		/**
		 * Modified add_filter().
		 *
		 * @param string                     $tag
		 * @param callable|array|string|null $function_to_add
		 * @param int|null                   $priority
		 * @param int                        $accepted_args
		 *
		 * @return self
		 */
		protected function add_filter(
			string $tag,
			$function_to_add = null,
			?int $priority = null,
			int $accepted_args = 1
		): self {
			add_filter(
				$tag,
				$this->__hook_parse_callback( $function_to_add, $tag ),
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
				$this->__hook_parse_callback( $function_to_remove, $tag ),
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
				$this->__hook_parse_callback( $function_to_remove, $tag ),
				$this->__hook_get_priority( $priority )
			);

			return $this;
		}

		/**
		 * Add action only for once.
		 *
		 * @param string                     $tag
		 * @param callable|array|string|null $function_to_add
		 * @param int|null                   $priority
		 * @param int                        $accepted_args
		 *
		 * @return $this
		 */
		protected function add_action_once(
			string $tag,
			$function_to_add = null,
			?int $priority = null,
			int $accepted_args = 1
		): self {
			$callback = $this->__hook_parse_callback( $function_to_add, $tag );
			$priority = $this->__hook_get_priority( $priority );

			if ( $callback ) {
				$wrap = function () use ( $tag, $callback, $priority, &$wrap ) {
					remove_action( $tag, $wrap, $priority );
					call_user_func_array( $callback, func_get_args() );
				};
				add_action( $tag, $wrap, $priority, $accepted_args );
			}

			return $this;
		}

		/**
		 * Add filter only for once.
		 *
		 * @param string                     $tag
		 * @param callable|array|string|null $function_to_add
		 * @param int|null                   $priority
		 * @param int                        $accepted_args
		 *
		 * @return $this
		 */
		protected function add_filter_once(
			string $tag,
			$function_to_add = null,
			?int $priority = null,
			int $accepted_args = 1
		): self {
			$callback = $this->__hook_parse_callback( $function_to_add, $tag );
			$priority = $this->__hook_get_priority( $priority );

			if ( $callback ) {
				$wrap = function () use ( $tag, $callback, $priority, &$wrap ) {
					remove_filter( $tag, $wrap, $priority );
					return call_user_func_array( $callback, func_get_args() );
				};
				add_filter( $tag, $wrap, $priority, $accepted_args );
			}

			return $this;
		}

		/**
		 * Parse callback function for actions and filters.
		 *
		 * @param        $item
		 * @param string $alt_method
		 *
		 * @return callable|null
		 */
		private function __hook_parse_callback( $item, string $alt_method ): ?callable {
			if ( is_string( $item ) && method_exists( $this, $item ) ) {
				return [ $this, $item ];
			} elseif ( is_null( $item ) && method_exists( $this, $alt_method ) ) {
				return [ $this, $alt_method ];
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
