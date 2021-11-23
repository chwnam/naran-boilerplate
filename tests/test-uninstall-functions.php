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

	public function test_nbpc_cleanup_meta() {
		global $wpdb;

		$registers = nbpc()->registers;

		// Change accessibility of nbpc()->registers->modules
		$modules_ref = $this->get_accessible_modules( $registers );

		$m = $modules_ref->getValue( $registers );

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

		$modules_ref->setValue( $registers, $m );

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

	public function test_nbpc_cleanup_option() {
		global $wpdb;

		$registers = nbpc()->registers;

		// Change accessibility of nbpc()->registers->modules
		$modules_ref = $this->get_accessible_modules( $registers, 'modules' );

		$m = $modules_ref->getValue( $registers );

		$m['option'] = new class() extends NBPC_Register_Base_Option {
			public function get_items(): Generator {
				yield 'opt1' => new NBPC_Reg_Option( 'opt_grp', 'npbc_option_1' );
				yield 'opt2' => new NBPC_Reg_Option( 'opt_grp', 'npbc_option_2' );
			}
		};

		$modules_ref->setValue( $registers, $m );

		// Expected query.
		$expected = "DELETE FROM $wpdb->options WHERE option_name IN ('npbc_option_1', 'npbc_option_2')";

		$callback = function ( $actual ) use ( $expected ) {
			$this->assertEquals( $expected, $actual );
			return $expected;
		};

		add_filter( 'query', $callback );

		// test and verify sql
		nbpc_cleanup_option();

		remove_filter( 'query', $callback );
	}

	public function test_nbpc_cleanup_terms() {
		global $wpdb;

		$registers = nbpc()->registers;

		// Change accessibility of nbpc()->registers->modules
		$modules_ref = $this->get_accessible_modules( $registers, 'modules' );

		$m = $modules_ref->getValue( $registers );

		$m['taxonomy'] = new class() extends NBPC_Register_Base_Taxonomy {
			public function get_items(): Generator {
				yield new NBPC_Reg_Taxonomy( 'nbpc_tag', 'post', [] );
			}
		};

		$modules_ref->setValue( $registers, $m );

		// Register taxonomy.
		$registers->taxonomy->register();

		// Create a new post.
		$p1 = $this->factory()->post->create_and_get();
		$p2 = $this->factory()->post->create_and_get();

		// Create a new term.
		$t = $this->factory()->term->create_and_get( [ 'taxonomy' => 'nbpc_tag' ] );

		wp_set_object_terms( $p1->ID, $t->term_id, $t->taxonomy );
		wp_set_object_terms( $p2->ID, $t->term_id, $t->taxonomy );

		// test and verify sql
		nbpc_cleanup_terms();

		$obj_cnt = $wpdb->get_var(
			"SELECT COUNT(*) FROM $wpdb->term_relationships" .
			" WHERE term_taxonomy_id IN ({$t->term_taxonomy_id})"
		);
		$this->assertEquals( 0, $obj_cnt );

		$tt_cnt = $wpdb->get_var(
			"SELECT COUNT(*) FROM $wpdb->term_taxonomy" .
			" WHERE term_taxonomy_id IN ({$t->term_taxonomy_id})"
		);
		$this->assertEquals( 0, $tt_cnt );

		$t_cnt = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->terms WHERE term_id IN ({$t->term_id})" );
		$this->assertEquals( 0, $t_cnt );
	}

//	public function test_nbpc_cleanup_posts() {} TODO: implement this test.

	private function get_accessible_modules( NBPC_Module $registers ): ReflectionProperty {
		try {
			$ref     = new ReflectionClass( get_class( $registers ) );
			$modules = $ref->getProperty( 'modules' );
			$modules->setAccessible( true );
		} catch ( ReflectionException $e ) {
			die( $e->getMessage() );
		}

		return $modules;
	}
}
