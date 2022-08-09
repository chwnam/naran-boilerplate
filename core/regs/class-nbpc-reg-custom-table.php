<?php
/**
 * Naran Boilerplate Core
 *
 * regs/class-nbpc-reg-custom-table.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Custom_Table' ) ) {
	class NBPC_Reg_Custom_Table implements NBPC_Reg {
		/**
		 * Constructor method
		 */
		public function __construct(
			public string $table,
			public array $fields,
			public array $index = [],
			public string $engine = 'InnoDB',
			public string $charset = '',
			public string $collate = ''
		) {
		}

		public function register( $dispatch = null ): array {
			return $this->create_table();
		}

		public function unregister(): void {
			$this->drop_table();
		}

		/**
		 * Create table
		 *
		 * This method uses dbDelta() function.
		 *
		 * Please keep in mind that dbDelta() is rather demanding:
		 * - You must put each field on its own line in your SQL statement.
		 * - You must have two spaces between the words PRIMARY KEY and the definition of your primary key.
		 * - You must use the key word KEY rather than its synonym INDEX and you must include at least one KEY.
		 * - KEY must be followed by a SINGLE SPACE then the key name then a space then open parenthesis with the field name then a closed parenthesis.
		 * - You must not use any apostrophes or backticks around field names.
		 * - Field types must be all lowercase.
		 * - SQL keywords, like CREATE TABLE and UPDATE, must be uppercase.
		 * - You must specify the length of all fields that accept a length parameter. int(11), for example.
		 * - Use 'UNIQUE KEY', not just 'UNIQUE'. Likewise, use 'FULLTEXT KEY', and 'SPATIAL KEY'.
		 *
		 * @return array
		 * @link   https://codex.wordpress.org/Creating_Tables_with_Plugins
		 */
		public function create_table(): array {
			global $wpdb;

			$fields  = implode( ",\n  ", $this->fields );
			$index   = implode( ",\n  ", $this->index );
			$charset = $this->charset ?: $wpdb->charset;
			$collate = $this->collate ?: $wpdb->collate;

			if ( $fields ) {
				$sql = "CREATE TABLE $this->table (\n  $fields" . ( $index ? ",\n $index" : '' ) .
				       "\n) ENGINE=$this->engine DEFAULT CHARSET=$charset COLLATE=$collate;";

				return dbDelta( $sql );
			}

			return [];
		}

		public function drop_table(): void {
			global $wpdb;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->query( "DROP TABLE IF EXISTS $this->table" );
		}
	}
}
