<?php

if ( ! class_exists( 'NBPC_CLI_Header_Detect' ) ) {
	class NBPC_CLI_Header_Detect {
		public const TYPE_THEME  = 'theme';
		public const TYPE_PLUGIN = 'plugin';

		public static function detect( string $rood_dir ): array|false {
			$files    = [];
			$open_dir = @opendir( $rood_dir );

			if ( $open_dir ) {
				while ( false !== ( $file = readdir( $open_dir ) ) ) {
					if ( str_starts_with( $file, '.' ) ) {
						continue;
					}
					if ( is_dir( $rood_dir . '/' . $file ) ) {
						// Themes can have the main .css file under a subdirectory.
						$sub_open_dir = @opendir( $rood_dir . '/' . $file );
						if ( $sub_open_dir ) {
							while ( false !== ( $sub_file = readdir( $sub_open_dir ) ) ) {
								if ( ! str_starts_with( $sub_file, '.' ) && str_ends_with( $sub_file, '.css' ) ) {
									$files[] = $rood_dir . '/' . $file . '/' . $sub_file;
								}
							}
							closedir( $sub_open_dir );
						}
					}
					if ( str_ends_with( $file, '.php' ) || str_ends_with( $file, '.css' ) ) {
						$files[] = "$rood_dir/$file";
					}
				}
				closedir( $open_dir );
			}

			foreach ( $files as $file ) {
				if ( is_readable( $file ) ) {
					$result = self::get_header_data( $file );
					if ( $result['name'] ) {
						return $result;
					}
				}
			}

			return false;
		}

		public static function get_header_data( string $file ): array {
			$result = [
				'name'    => '',
				'path'    => '',
				'type'    => '',
				'version' => '',
			];

			if ( str_ends_with( $file, '.css' ) ) {
				$data = self::get_header_fields(
					$file,
					[
						'name'    => 'Theme Name',
						'version' => 'Version',
					]
				);
				if ( $data['name'] ) {
					$result['name']    = $data['name'];
					$result['path']    = $file;
					$result['type']    = self::TYPE_THEME;
					$result['version'] = $data['version'];
				}
			} elseif ( str_ends_with( $file, '.php' ) ) {
				$data = self::get_header_fields(
					$file,
					[
						'name'    => 'Plugin Name',
						'version' => 'Version',
					]
				);
				if ( $data['name'] ) {
					$result['name']    = $data['name'];
					$result['path']    = $file;
					$result['type']    = self::TYPE_PLUGIN;
					$result['version'] = $data['version'];
				}
			}

			return $result;
		}

		public static function get_header_fields( string $file, array $fields ): array {
			$content = file_get_contents( $file, false, null, 0, 8192 ) ?: '';
			$content = str_replace( "\r", "\n", $content );
			$headers = [];

			foreach ( $fields as $field => $regex ) {
				$extracted = self::extract_header( $content, $regex );

				$headers[ $field ] = $extracted[0];
			}

			return $headers;
		}

		public static function extract_header( string $content, string $regex ): array {
			$value  = '';
			$offset = 0;

			if (
				preg_match(
					'/^(?:[ \t]*<\?php)?[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi',
					$content,
					$match,
					PREG_OFFSET_CAPTURE
				)
				&& $match[1][0]
			) {
				$value  = self::cleanup_header_comment( $match[1][0] );
				$offset = $match[1][1];

				// Preserve whitespace between colon and version string.
				$offset = strpos( $content, $value, $offset );
			}

			return [ $value, $offset ];
		}

		/**
		 * @see _cleanup_header_comment()
		 */
		public static function cleanup_header_comment( string $input ): string {
			return trim( preg_replace( '/\s*(?:\*\/|\?>).*/', '', $input ) );
		}
	}
}