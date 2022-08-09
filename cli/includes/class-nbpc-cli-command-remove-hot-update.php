<?php

if ( ! class_exists( 'NBPC_CLI_Command_Remove_Hot_Update' ) ) {
	final class NBPC_CLI_Command_Remove_Hot_Update implements NBPC_CLI_Command {
		public static function get_command_name(): string {
			return 'remove-hot-update';
		}

		public static function add_command( Console_CommandLine $parser ): void {
			$cmd = $parser->addCommand(
				self::get_command_name(),
				[ 'description' => 'Remove hot-update temporary files.' ]
			);

			$cmd->addOption(
				'target_dir',
				[
					'short_name'  => '-t',
					'long_name'   => '--target-dir',
					'description' => 'target directory. Defaults to `assets/js/dist`.',
					'action'      => 'StoreString',
					'help_name'   => 'TARGET_DIR',
					'default'     => 'assets/js/dist',
				]
			);
		}

		public function run( Console_CommandLine_Result $parsed ): void {
			$target_dir = trim( $parsed->options['target_dir'], '/\\' );
			$path       = NBPC_ROOT . '/' . $target_dir;

			if ( file_exists( $path ) && is_dir ( $path ) ) {
				foreach ( glob( "$target_dir/*.hot-update.{js,js.map,json}", GLOB_BRACE ) as $file ) {
					unlink( $file );
				}
			}
		}
	}
}
