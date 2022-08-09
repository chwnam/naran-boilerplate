<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */


/**
 * Class Test_Register_Style
 *
 * @package nbpc
 */
class Test_Register_Rewrite_Rule extends WP_UnitTestCase {
	private $register;

	public function setUp(): void {
		$this->register = new class() extends NBPC_Register_Rewrite_Rule {
			public function get_items(): Generator {
				yield new NBPC_Reg_Rewrite_Rule(
					'^nbpc/top/?$',
					'index.php?nbpc-rewrite-rule=top',
					'top',
					function () { echo 'top'; },
					'nbpc-rewrite-rule'
				);

				yield new NBPC_Reg_Rewrite_Rule(
					'^nbpc/bottom/?$',
					'index.php?nbpc-rewrite-rule=bottom',
					'bottom',
					function () { echo 'bottom'; },
					'nbpc-rewrite-rule'
				);
			}
		};
	}

	public function test_get_items() {
		do_action( 'init' );

		global $wp_rewrite, $wp;

		$items = iterator_to_array( $this->register->get_items() );

		// Check if regex is properly registered.
		$this->assertArrayHasKey( $items[0]->regex, $wp_rewrite->extra_rules_top );
		$this->assertEquals( $items[0]->query, $wp_rewrite->extra_rules_top[ $items[0]->regex ] );

		// Check if regex is properly registered.
		$this->assertArrayHasKey( $items[1]->regex, $wp_rewrite->extra_rules );
		$this->assertEquals( $items[1]->query, $wp_rewrite->extra_rules[ $items[1]->regex ] );

		// Check if template_redirect callback is added.
		$this->assertNotFalse( has_action( 'template_redirect', [ $this->register, 'handle_binding' ] ) );

		// Check if query_vars callback is added.
		$this->assertNotFalse( has_filter( 'query_vars', [ $this->register, 'add_query_vars' ] ) );

		add_filter( 'nbpc_register_rewrite_rule_exit', '__return_false', 10, 2 );

		// Check output.
		$wp->matched_rule = $items[0]->regex;
		ob_start();
		$this->register->handle_binding(); // DO not exit here.
		$this->assertEquals( 'top', ob_get_clean() );

		$wp->matched_rule = $items[1]->regex;
		ob_start();
		$this->register->handle_binding();
		$this->assertEquals( 'bottom', ob_get_clean() );

		remove_filter( 'nbpc_register_rewrite_rule_exit', '__return_false', 10 );
	}
}
