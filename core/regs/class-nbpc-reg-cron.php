<?php
/**
 * NBPC: Cron reg.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Cron' ) ) {
	class NBPC_Reg_Cron implements NBPC_Reg {
		/** @var int */
		public int $timestamp;

		/** @var string */
		public string $schedule;

		/** @var string */
		public string $hook;

		public array $args;

		public bool $wp_error;

		public int $is_single_event;

		public function __construct(
			int $timestamp,
			string $schedule,
			string $hook,
			array $args = [],
			bool $wp_error = false,
			bool $is_single_event = false
		) {
			$this->timestamp       = $timestamp;
			$this->schedule        = $schedule;
			$this->hook            = $hook;
			$this->args            = $args;
			$this->wp_error        = $wp_error;
			$this->is_single_event = $is_single_event;
		}

		public function register( $dispatch = null ) {
			if ( $this->is_single_event ) {
				wp_schedule_single_event( $this->timestamp, $this->hook, $this->args, $this->wp_error );
			} else {
				wp_schedule_event( $this->timestamp, $this->schedule, $this->hook, $this->args, $this->wp_error );
			}
		}

		public function unregister() {
			if ( wp_next_scheduled( $this->hook, $this->args ) ) {
				wp_clear_scheduled_hook( $this->hook, $this->args );
			}
		}
	}
}
