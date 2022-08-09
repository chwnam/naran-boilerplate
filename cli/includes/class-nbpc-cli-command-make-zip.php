<?php
if ( ! class_exists( 'NBPC_CLI_Command_Make_Zip' ) ) {
	final class NBPC_CLI_Command_Make_Zip implements NBPC_CLI_Command {
		public static function get_command_name(): string {
			return 'make-zip';
		}

		public static function add_command( Console_CommandLine $parser ): void {
			$cmd = $parser->addCommand(
				self::get_command_name(),
				[
					'description' => 'Create .zip file for publishing.',
				]
			);

			$cmd->addOption(
				'output',
				[
					'short_name'  => '-o',
					'long_name'   => '--output',
					'description' => 'path to output zip file. ' .
					                 'Defaults to the parent directory to the project, ' .
					                 'and the file is named after the directory name.',
					'action'      => 'StoreString',
					'help_name'   => 'OUTPUT',
					'default'     => '',
				]
			);

			$default_excludes = __CLASS__ . '::default_exclude_patterns';

			$cmd->addOPtion(
				'exclude',
				[
					'short_name'  => '-e',
					'long_name'   => '--exclude',
					'description' => 'use gitignore pattern to exclude files.' .
					                 'Use pipes to enter multple patterns. ' .
					                 "Refer to `$default_excludes()` for the default value.",
					'default'     => self::default_exclude_patterns(),
				]
			);
		}

		public static function default_exclude_patterns(): string {
			return implode(
				'|',
				[
					'!/README.md',
					'!/vendor/autoload.php',
					'!/vendor/composer',
					'**/README.md',
					'**/tests/',
					'/.phpcs.xml',
					'/.phpcs.xml.dist',
					'/.travis.yml',
					'/bin',
					'/cli',
					'/custom.dic',
					'/vendor/*',
					'node_modules',
					'phpunit.xml',
					'phpunit.xml.dist',
				]
			);
		}

		public function run( Console_CommandLine_Result $parsed ): void {
			$project_dir = NBPC_ROOT;
			$rel_len     = strlen( $project_dir );
			$zip_len     = strlen( dirname( $project_dir ) );

			// Parse .gitignore and exclude option.
			$parser = new NBPC_CLI_Gitignore_Parse();
			$parser->parse( $project_dir . '/.gitignore' );

			$excludes = explode( '|', $parsed->options['exclude'] );
			foreach ( $excludes as $exclude ) {
				$parser->add_to_pattern( $exclude );
			}

			$output = $parsed->options['output'];
			if ( empty( $output ) ) {
				$output = dirname( $project_dir ) . '/' . nbpc_cli_basename( $project_dir ) . '.zip';
			}

			if ( file_exists( $output ) ) {
				unlink( $output );
			}

			$zip = new ZipArchive();
			$zip->open( $output, ZipArchive::CREATE );
			$zip->addEmptyDir( nbpc_cli_basename( $project_dir ) );

			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $project_dir, FilesystemIterator::SKIP_DOTS )
			);

			foreach ( $iterator as $info ) {
				/** @var SplFileInfo $info */
				if ( $info->isFile() ) {
					$path     = $info->getPathname();
					$rel_path = substr( $info->getPathname(), $rel_len );
					if ( ! $parser->match( $rel_path ) ) {
						$zip_path = substr( $path, $zip_len + 1 );
						echo $zip_path . PHP_EOL;
						$zip->addFile( $path, $zip_path );
					}
				}
			}

			$zip->close();
		}
	}
}
