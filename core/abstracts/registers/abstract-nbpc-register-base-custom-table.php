<?php
/**
 * Naran Boilerplate Core
 *
 * abstracts/registers/abstract-nbpc-register-base-custom-table.php
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
			$this->delete_version();
		}

		/**
		 * Perform table creation and initial data insertion.
		 * You can skip this callback if you do not have initial data to insert.
		 *
		 * @return void
		 */
		public function initial_setup(): void {
			global $wpdb;

			$install_initial_data = apply_filters( 'nbpc_install_initial_data', true, $this->get_current_version(), $this->get_installed_version() );
			if ( $install_initial_data ) {
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
		}

		/**
		 * Perform table update.
		 */
		public function update_table(): void {
			$old_version = $this->get_installed_version();
			$new_version = $this->get_current_version();

			if ( $new_version !== $old_version ) {
				do_action( 'nbpc_before_update_table', $new_version, $old_version );
				$this->register();
				do_action( 'nbpc_after_update_table', $new_version, $old_version, $this->delta_result );
				$this->update_version( $this->get_current_version() );
				$this->log_delta_result();
			}
		}

		/**
		 * Current version of DB table.
		 */
		public function get_current_version(): string {
			return static::DB_VERSION;
		}

		/**
		 * Currently installed DB table version, read from option table.
		 * Probably this version is equal or behind to the current version.
		 */
		public function get_installed_version(): string {
			return get_option( 'nbpc_db_version', '' );
		}

		/**
		 * Update the db version.
		 */
		public function update_version( string $version ): void {
			update_option( 'nbpc_db_version', $version );
		}

		/**
		 * Delete version. Use when uninstalling.
		 */
		public function delete_version(): void {
			delete_option( 'nbpc_db_version' );
		}

		/**
		 * Keep dbDelta() result.
		 */
		protected function keep_delta_result( array $result ): void {
			$lines = [];

			foreach ( $result as $table => $message ) {
				$lines[] = sprintf( "\t{%s}: %s.", $table, $message );
			}

			$this->delta_result .= implode( "\n", $lines );
		}

		/**
		 * Log dbDelta() result.
		 */
		protected function log_delta_result(): void {
			if ( $this->delta_result ) {
				$version = $this->get_installed_version();
				error_log( "dbDelta() updated 'nbpc_db_version' to $version.\n" . $this->delta_result );
			}
		}
	}
}
