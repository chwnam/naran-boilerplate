<?php
/**
 * NBPC: Rewrite rule reg
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Rewrite_Rule' ) ) {
	class NBPC_Reg_Rewrite_Rule implements NBPC_Reg {
		public string $regex;

		public string $query;

		public string $after;

		/**
		 * @var callable|string|null
		 */
		public $binding;

		/**
		 * @var string[]
		 */
		public array $query_vars;

		/**
		 * Constructor method
		 *
		 * @param string               $regex      Regular expression for URL matching.
		 * @param string               $query      Rewrite query string.
		 * @param string               $after      'top', 'bottom'.
		 * @param callable|string|null $binding    Callback method for 'template_redirect' action.
		 * @param string|array         $query_vars Public query variables to append.
		 */
		public function __construct(
			string $regex,
			string $query,
			string $after = 'bottom',
			$binding = null,
			$query_vars = ''
		) {
			$this->regex      = $regex;
			$this->query      = $query;
			$this->after      = $after;
			$this->binding    = $binding;
			$this->query_vars = (array) $query_vars;
		}

		public function register( $dispatch = null ): void {
			if ( $this->regex && $this->query ) {
				add_rewrite_rule( $this->regex, $this->query, $this->after );
			}
		}
	}
}
