<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */


/**
 * Class Test_Uninstall_Functions
 *
 * @package nbpc
 */
class Test_Uninstall_Functions extends WP_UnitTestCase {
	public static function setUpBeforeClass() {
		require_once dirname( NBPC_MAIN_FILE ) . '/core/uninstall-functions.php';
	}

	/**
	 * @throws ReflectionException
	 */
	public function test_nbpc_cleanup_meta() {
		global $wpdb;

		$registers = nbpc()->registers;

		// change accessibility of nbpc()->registers->modules
		$ref     = new ReflectionClass( get_class( $registers ) );
		$modules = $ref->getProperty( 'modules' );
		$modules->setAccessible( true );

		$m = $modules->getValue( $registers );

		$m['comment_meta'] = new class() extends NBPC_Register_Base_Meta {
			public function get_items(): Generator {
				yield 'cm1' => new NBPC_Reg_Meta( 'comment', '_comment_meta_1' );
				yield 'cm2' => new NBPC_Reg_Meta( 'comment', '_comment_meta_2' );
			}
		};

		$m['post_meta'] = new class() extends NBPC_Register_Base_Meta {
			public function get_items(): Generator {
				yield 'pm1' => new NBPC_Reg_Meta( 'post', '_post_meta_1' );
				yield 'pm2' => new NBPC_Reg_Meta( 'post', '_post_meta_2' );
			}
		};

		$m['term_meta'] = new class() extends NBPC_Register_Base_Meta {
			public function get_items(): Generator {
				yield 'tm1' => new NBPC_Reg_Meta( 'term', '_term_meta_1' );
				yield 'tm2' => new NBPC_Reg_Meta( 'term', '_term_meta_2' );
			}
		};

		$m['user_meta'] = new class() extends NBPC_Register_Base_Meta {
			public function get_items(): Generator {
				yield 'um1' => new NBPC_Reg_Meta( 'user', '_user_meta_1' );
				yield 'um2' => new NBPC_Reg_Meta( 'user', '_user_meta_2' );
			}
		};

		$modules->setValue( $registers, $m );

		// Expected queries.
		$comment_sql = "DELETE FROM $wpdb->commentmeta WHERE meta_key IN ('_comment_meta_1', '_comment_meta_2')";
		$post_sql    = "DELETE FROM $wpdb->postmeta WHERE meta_key IN ('_post_meta_1', '_post_meta_2')";
		$term_sql    = "DELETE FROM $wpdb->termmeta WHERE meta_key IN ('_term_meta_1', '_term_meta_2')";
		$user_sql    = "DELETE FROM $wpdb->usermeta WHERE meta_key IN ('_user_meta_1', '_user_meta_2')";

		$queries = [ $comment_sql, $post_sql, $term_sql, $user_sql ];

		$callback = function ( $sql ) use ( &$queries ) {
			$expected = array_shift( $queries );
			$this->assertEquals( $expected, $sql );
			return $sql;
		};

		add_filter( 'query', $callback );

		// test and verify sql
		nbpc_cleanup_meta();

		remove_filter( 'query', $callback );
	}

//	public function test_nbpc_cleanup_option() {} TODO: implement this test.

//	public function test_nbpc_cleanup_terms() {} TODO: implement this test.

//	public function test_nbpc_cleanup_posts() {} TODO: implement this test.
}
