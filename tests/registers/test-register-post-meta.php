<?php

/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
if ( ! class_exists( 'WC_Product' ) ) {
	class WC_Product {
		private int $id;

		public function __construct( int $id ) {
			$this->id = $id;
		}

		public function get_id(): int {
			return $this->id;
		}
	}
}

class Test_Register_Post_Meta extends WP_UnitTestCase {
	private $register;

	private WP_Post $post;

	public function setUp() {
		$this->register = new class() extends NBPC_Register_Post_Meta {
			public bool $sanitize_called = false;
			public bool $auth_called = false;

			public function get_items(): Generator {
				yield new NBPC_Reg_Meta(
					'post',
					'nbpc_meta_red',
					[
						'type'              => 'string',
						'description'       => 'Meta read description',
						'default'           => 'red_default',
						'single'            => true,
						'sanitize_callback' => [ $this, 'sanitize' ],
						'auth_callback'     => [ $this, 'authorize' ],
						'show_in_rest'      => false,
					]
				);

				yield 'blue' => new NBPC_Reg_Meta( 'post', 'nbpc_meta_blue', [ 'single' => false ] );
			}

			public function sanitize( $value ): string {
				$this->sanitize_called = true;

				return sanitize_text_field( $value );
			}

			public function authorize(): bool {
				$this->auth_called = true;

				return true;
			}
		};

		$this->post = $this->factory()->post->create_and_get();

		do_action( 'init' );
	}

	public function test_red() {
		$meta = $this->register->nbpc_meta_red;

		$this->assertInstanceOf( NBPC_Reg_Meta::class, $meta );

		// Check methods.
		$this->assertEquals( 'post', $meta->get_object_type() );
		$this->assertEquals( 'nbpc_meta_red', $meta->get_key() );

		// Check magic properties.
		$this->assertEquals( '', $meta->object_subtype );
		$this->assertEquals( 'string', $meta->type );
		$this->assertEquals( 'Meta read description', $meta->description );
		$this->assertEquals( 'red_default', $meta->default );
		$this->assertTrue( $meta->single );
		$this->assertEquals( [ $this->register, 'sanitize' ], $meta->sanitize_callback );
		$this->assertEquals( [ $this->register, 'authorize' ], $meta->auth_callback );
		$this->assertFalse( $meta->show_in_rest );

		// Check if the default value is retrieved.
		$this->assertEquals( 'red_default', $meta->get_value( $this->post->ID ) );

		// Save meta value, and check if the sanitization callback is called.
		$meta->update( $this->post->ID, ' X ' );
		$this->assertTrue( $this->register->sanitize_called );
		$this->assertEquals( 'X', $meta->get_value( $this->post->ID ) );
		$this->assertEquals( 'X', get_post_meta( $this->post->ID, 'nbpc_meta_red', true ) );

		// ... and also check if the auth callback is called.
		// The result, permission status of the current user is not important.
		current_user_can( 'edit_post_meta', $this->post->ID, $meta->get_key() );
		$this->assertTrue( $this->register->auth_called );

		// Check if the meta is found in the meta DB.
		global $wpdb;

		$value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key=%s",
				$this->post->ID,
				'nbpc_meta_red'
			)
		);
		$this->assertEquals( 'X', $value );

		// Check if the meta is removed.
		$meta->delete( $this->post->ID );

		// Check if the default is returned after removal.
		$this->assertEquals( 'red_default', $meta->get_value( $this->post->ID ) );
	}

	public function test_blue() {
		// Check if alias is okay. Most tests are done in test_red.
		$meta = $this->register->blue;

		$this->assertInstanceOf( NBPC_Reg_Meta::class, $meta );

		$meta->update( $this->post->ID, 'BLUE' );

		// Check if the value is retrived as an array, because single = false.
		$value = $meta->get_value( $this->post->ID );

		$this->assertIsArray( $value );
	}

	public function test_get_id() {
		try {
			$meta       = new NBPC_Reg_Meta( 'post', 'test' );
			$reflection = new ReflectionClass( NBPC_Reg_Meta::class );
			$method     = $reflection->getMethod( '_get_id' );
			$method->setAccessible( true );

			$std     = new stdClass();
			$std->ID = '1';

			$arr = [
				'ID' => '1'
			];

			// Check if NBPC_Reg_Meta::_get_id() can guess ID numbers:
			// from WP_Post, WP_User, WP_Term, WP_Comment.
			// from stdClass->ID, stdClass->id
			// from array key 'ID', 'id'
			// from integer, string.
			$this->assertSame( 1, $method->invoke( $meta, '1' ) );
			$this->assertSame( 1, $method->invoke( $meta, 1 ) );
			$this->assertSame( 1, $method->invoke( $meta, $std ) );
			$this->assertSame( 1, $method->invoke( $meta, $arr ) );
			$this->assertSame( $this->post->ID, $method->invoke( $meta, $this->post ) );

			$user = wp_get_current_user();
			$this->assertSame( $user->ID, $method->invoke( $meta, $user ) );

			$term = $this->factory()->term->create_and_get();
			$this->assertSame( $term->term_id, $method->invoke( $meta, $term ) );

			$comment = $this->factory()->comment->create_and_get();
			$this->assertSame( $comment->comment_ID, $method->invoke( $meta, $comment ) );

			$product = new WC_Product( 55 );
			$this->assertSame( 55, $method->invoke( $meta, $product ) );
		} catch ( ReflectionException $e ) {
		}
	}
}