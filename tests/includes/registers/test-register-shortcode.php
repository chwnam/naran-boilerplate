<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

/**
 * Class Test_Register_Shortcode
 *
 * @package nbpc
 */
class Test_Register_Shortcode extends WP_UnitTestCase {
	private $register;

	public function setUp(): void {
		$this->register = new class() extends NBPC_Register_Base_Shortcode {
			public string $ha = '';
			public string $hb = '';

			public function get_items(): Generator {
				yield new NBPC_Reg_Shortcode( 'nbpc_shortcode_a', [ $this, 'callback_a' ], [ $this, 'heading_a' ] );
				yield new NBPC_Reg_Shortcode( 'nbpc_shortcode_b', [ $this, 'callback_b' ], [ $this, 'heading_b' ] );
			}

			public function callback_a($atts, $enclosed, $tag): string {
				$inner = '';
				if ( $enclosed ) {
					$inner = do_shortcode( $enclosed );
				}
				return 'a' . $inner . 'a';
			}

			public function callback_b(): string {
				return 'b';
			}

			public function heading_a() {
				$this->ha = 'okay, a.';
			}

			public function heading_b() {
				$this->hb = 'okay, b.';
			}
		};
	}

	public function test_get_items() {
		// Trigger register method.
		do_action( 'init' );

		foreach ( $this->register->get_items() as $item ) {
			if ( $item instanceof NBPC_Reg_Shortcode ) {
				$exists = shortcode_exists( $item->tag );
				$this->assertTrue( $exists );
			}
		}
	}

	public function test_shortcode_output() {
		global $wp;

		// Trigger register method.
		do_action( 'init' );

		$p = $this->factory()->post->create_and_get(
			[ 'post_content' => '[nbpc_shortcode_a]1[nbpc_shortcode_b]2[/nbpc_shortcode_a]' ]
		);

		// Reset main query.
		$wp->query_vars['p'] = $p->ID;
		$wp->query_posts();
		wp_reset_query();

		$this->assertEquals( '', $this->register->ha );
		$this->assertEquals( '', $this->register->hb );

		do_action( 'wp' );

		$this->assertEquals( 'okay, a.', $this->register->ha );
		$this->assertEquals( 'okay, b.', $this->register->hb );

		$result = do_shortcode( $p->post_content );
		$this->assertEquals( 'a1b2a', $result );
	}
}
