<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

class Test_Register_Custom_Table extends WP_UnitTestCase {
	public static function setUpBeforeClass() {
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}
	}

	public function test_create_table() {
		global $wpdb;

		$item = new NBPC_Reg_Custom_Table(
			$wpdb->prefix . 'ct_1',
			[
				"id      BIGINT(20)  UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY",
				"user_id BIGINT(20)   UNSIGNED NOT NULL",
				"value   VARCHAR(255)          NOT NULL",
				"code    INT                   NOT NULL",
			],
			[
				"INDEX  idx_user_id (user_id)",
				"UNIQUE uniq_code   (code)",
			]
		);

		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );

		$item->create_table();

		$table = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $item->table ) );
		$this->assertEquals( $item->table, $table );

		$columns = $wpdb->get_results( "SHOW COLUMNS FROM $item->table" );
		$this->assertEquals( [ 'id', 'user_id', 'value', 'code' ], wp_list_pluck( $columns, 'Field' ) );

		$item->drop_table();
		$table = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $item->table ) );
		$this->assertNull( $table );

		add_filter( 'query', array( $this, '_create_temporary_tables' ) );
		add_filter( 'query', array( $this, '_drop_temporary_tables' ) );
	}
}