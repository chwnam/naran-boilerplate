<?php
/**
 * NBPC: Sub Menu reg.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Submenu' ) ) {
	class NBPC_Reg_Submenu implements NBPC_Reg {
		public string $parent_slug;

		public string $page_title;

		public string $menu_title;

		public string $capability;

		public string $menu_slug;

		public $callback;

		public ?int $position;

		/**
		 * Constructor method
		 *
		 * @param string          $parent_slug
		 * @param string          $page_title
		 * @param string          $menu_title
		 * @param string          $capability
		 * @param string          $menu_slug
		 * @param callable|string $callback
		 * @param int|null        $position
		 */
		public function __construct(
			string $parent_slug,
			string $page_title,
			string $menu_title,
			string $capability,
			string $menu_slug,
			$callback,
			?int $position = null
		) {
			$this->parent_slug = $parent_slug;
			$this->page_title  = $page_title;
			$this->menu_title  = $menu_title;
			$this->capability  = $capability;
			$this->menu_slug   = $menu_slug;
			$this->callback    = $callback;
			$this->position    = $position;
		}

		/**
		 * @param callable|null $dispatch
		 *
		 * @return string
		 */
		public function register( $dispatch = null ): string {
			return add_submenu_page(
				$this->parent_slug,
				$this->page_title,
				$this->menu_title,
				$this->capability,
				$this->menu_slug,
				$dispatch,
				$this->position
			);
		}
	}
}
