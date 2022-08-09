<?php
/**
 * Naran Boilerplate Core
 *
 * regs/class-nbpc-reg-menu.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Menu' ) ) {
	class NBPC_Reg_Menu implements NBPC_Reg {
		/**
		 * Constructor method
		 */
		public function __construct(
			public string $page_title,
			public string $menu_title,
			public string $capability,
			public string $menu_slug,
			public Closure|array|string $callback,
			public string $icon_url = '',
			public ?int $position = null,
			public bool $remove_submenu = false
		) {
		}

		/**
		 * @param callable|null $dispatch
		 *
		 * @return string
		 */
		public function register( $dispatch = null ): string {
			return add_menu_page(
				$this->page_title,
				$this->menu_title,
				$this->capability,
				$this->menu_slug,
				$dispatch,
				$this->icon_url,
				$this->position
			);
		}
	}
}
