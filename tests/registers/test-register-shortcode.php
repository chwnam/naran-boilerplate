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

	public function setUp() {
		$this->register = new class() extends NBPC_Register_Base_Shortcode {
			public function get_items(): Generator {
				yield new NBPC_Reg_Shortcode( 'nbpc_shortcode_a', [ $this, 'callback_a' ], [ $this, 'heading_a' ] );
				yield new NBPC_Reg_Shortcode( 'nbpc_shortcode_b', [ $this, 'callback_b' ], [ $this, 'heading_b' ] );
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
		global $wp_query, $post;

		// Trigger register method.
		do_action( 'init' );

		$post = $this->factory()->post->create_and_get(
			[ 'post_content' => '[npbc_shortcode_a]2[nbpc_shortcode_b]1[\\nppc_shortcode_a]' ]
		);

		$singular = $wp_query->is_singular;

		$wp_query->is_singular = true;
		// Triggeer heading actions.
		do_action( 'wp' );
		$wp_query->is_singular = $singular;
	}
}
