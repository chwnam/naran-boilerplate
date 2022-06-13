<?php
/**
 * NBPC: Custom table register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Custom_Table' ) ) {
	abstract class NBPC_Register_Base_Custom_Table implements NBPC_Register {
		protected string $delta_result = '';

		/**
		 * Register each custom table.
		 *
		 * @return void
		 */
		public function register(): void {
			if ( ! function_exists( 'dbDelta' ) ) {
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			}
			$this->delta_result = '';
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Custom_Table ) {
					$this->keep_delta_result( $item->register() );
				}
			}
		}

		/**
		 * Unregister each custom table.
		 *
		 * NOTE: This method drop tables. Call this method when uninstalling.
		 *
		 * @return void
		 */
		public function unregister(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Custom_Table ) {
					$item->unregister();
				}
			}
		}

		/**
		 * Perform table creation and initial data insertion.
		 * You can skip this callback if you do not have initial data to insert.
		 *
		 * @return void
		 */
		public function initial_setup() {
			global $wpdb;

			$this->register();
			// You can activate more than once, and then your data should be duplicated.
			$suppress = $wpdb->suppress_errors();
			foreach ( $this->get_initial_data() as $table => $datum ) {
				foreach ( $datum as $row ) {
					$wpdb->insert( $table, $row );
				}
			}
			$wpdb->suppress_errors( $suppress );
			$this->update_version( $this->get_current_version() );
			$this->log_delta_result();
		}

		/**
		 * Perform table update.
		 *
		 * @return void
		 */
		public function update_table() {
			if ( $this->get_current_version() !== $this->get_installed_version() ) {
				$this->register();
				$this->update_version( $this->get_current_version() );
				$this->log_delta_result();
			}
		}

		/**
		 * Current version of DB table.
		 *
		 * @return string
		 */
		protected function get_current_version(): string {
			return static::DB_VERSION;
		}

		/**
		 * Currently installed DB table version, read from option table.
		 * Probably this version is equal or behind to the current version.
		 *
		 * @return string
		 */
		protected function get_installed_version(): string {
			return get_option( 'nbpc_db_version', '' );
		}

		/**
		 * Update the db version.
		 *
		 * @param string $version
		 *
		 * @return void
		 */
		protected function update_version( string $version ): void {
			update_option( 'nbpc_db_version', $version );
		}

		/**
		 * Keep dbDelta() result.
		 *
		 * @param array $result
		 *
		 * @return void
		 */
		protected function keep_delta_result( array $result ) {
			$lines = [];

			foreach ( $result as $table => $message ) {
				$lines[] = sprintf( "\t{%s}: %s.", $table, $message );
			}

			$this->delta_result .= implode( "\n", $lines );
		}

		/**
		 * Log dbDelta() result.
		 *
		 * @return void
		 */
		protected function log_delta_result() {
			if ( $this->delta_result ) {
				$version = $this->get_installed_version();
				error_log( "dbDelta() updated 'nbpc_db_version' to $version.\n" . $this->delta_result );
			}
		}
	}
}
