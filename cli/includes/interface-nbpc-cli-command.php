<?php
if ( ! interface_exists( 'NBPC_CLI_Command' ) ) {
	interface NBPC_CLI_Command {
		public static function add_command( Console_CommandLine $parser ): void;

		public static function get_command_name(): string;

		public function run( Console_CommandLine_Result $parsed ): void;
	}
}
