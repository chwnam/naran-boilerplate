<?php

if ( ! class_exists( 'NBPC_CLI_Gitignore_Parse' ) ) {
	class NBPC_CLI_Gitignore_Parse {
		private const DELIM = '#';

		private array $patterns = [];

		private array $negated_patterns = [];

		/**
		 * Match aginst input path.
		 *
		 * @param string $path Path is relative to .gitignore
		 *
		 * @return bool
		 */
		public function match( string $path ): bool {
			$output = false;
			$d      = self::DELIM;

			foreach ( $this->patterns as $pattern ) {
				if ( preg_match( "$d$pattern$d", $path ) ) {
					$output = true;
					foreach ( $this->negated_patterns as $negated_pattern ) {
						if ( preg_match( "$d$negated_pattern$d", $path ) ) {
							$output = false;
							break 2;
						}
					}
					break;
				}
			}

			return $output;
		}

		public function parse( string $gitignore ): void {
			if ( $gitignore && file_exists( $gitignore ) ) {
				$this->patterns         = [];
				$this->negated_patterns = [];

				$content = file_get_contents( $gitignore ) ?: '';
				$lines   = explode( "\n", $content );

				foreach ( $lines as $line ) {
					$this->add_to_pattern( $line );
				}
			}
		}

		public function add_to_pattern( string $line ): void {
			$line = $this->trim_line( $line );

			if ( $line ) {
				$pattern = $this->line_to_regex_pattern( $line );

				if ( $pattern['negated'] ) {
					$this->negated_patterns[ $pattern['pattern'] ] = $pattern['pattern'];
				} else {
					$this->patterns[ $pattern['pattern'] ] = $pattern['pattern'];
				}
			}
		}

		public function trim_line( string $line ): string {
			$line = ltrim( $line );

			if ( ! $line || str_starts_with( $line, '#' ) ) {
				// Ignore empty lines and comments.
				return '';
			}

			if ( preg_match( '/^\\\[#!?]/', $line ) ) {
				// Strip escaped hash, exclamation, and question mark.
				$line = substr( $line, 1 );
			}

			if ( preg_match( '/(.+\\\ )/', $line, $match ) ) {
				// Preserve escaped trailing space.
				$line = stripslashes( $match[1] );
			} else {
				$line = rtrim( $line );
			}

			return $line;
		}

		public function line_to_regex_pattern( string $line ): array {
			$head = '/';
			$tail = '(?:/|$)';

			// Exclusion
			if ( str_starts_with( $line, '!' ) ) {
				$line    = substr( $line, 1 );
				$negated = true;
			} else {
				$negated = false;
			}

			// Make relative to the root directory.
			$pos = strpos( $line, '/' );
			if ( false !== $pos && $pos < strlen( $line ) - 1 ) {
				$head = '^/';
				if ( 0 === $pos ) {
					$line = substr( $line, 1 );
				}
			}

			// Following slash limits to directory.
			if ( str_ends_with( $line, '/' ) ) {
				$tail = '';
			}

			if ( str_starts_with( $line, '**/' ) ) {
				$head = '^.*/';
				$line = substr( $line, 3 );
			}

			if ( str_ends_with( $line, '/**' ) ) {
				$tail = '/.+';
				$line = substr( $line, 0, - 3 );
			}

			$len = strlen( $line );
			$pos = 0;
			$buf = '';
			$fn  = false;

			while ( $pos < $len ) {
				$char = $line[ $pos ];

				switch ( $char ) {
					case '\\':
						// Escaping character.
						$buf .= '\\' . preg_quote( $line[ $pos + 1 ] ?? '', self::DELIM );
						$pos += 1;
						break;

					case '/':
						// Two asterisks following slash: directories of any depths.
						$peek = substr( $line, $pos + 1, 3 );
						if ( '**/' === $peek ) {
							$buf .= '/(?:|.+/)';
							$pos += 3;
						} else {
							$buf .= preg_quote( $char, self::DELIM );
						}
						break;

					case '?':
						// Wildcard.
						$buf .= '[^/]';
						break;

					case '*':
						// Wildcard.
						$buf .= '[^/]*';
						break;

					case '[':
						// Begin: fnmatch
						if ( ! $fn ) {
							$buf .= '[';
							if ( '!' === $line[ $pos + 1 ] ?? '' ) {
								// Negation.
								$buf .= '^';
								$pos += 1;
							}
							$fn = true;
						} else {
							// Already fnmatch is opened. Escape it.
							$buf .= preg_quote( $char, self::DELIM );
						}
						break;

					case ']';
						if ( '[' === $line[ $pos - 1 ] ?? '' ) {
							// [...] cannot be zero-length.
							$buf .= preg_quote( $char, self::DELIM );
						} else {
							$buf .= $char;
							$fn  = false;
						}
						break;

					case '.':
						// Make sure that a dot is just an escaped, plain dot.
						$buf .= '\.';
						break;

					default:
						$buf .= $fn ? $char : preg_quote( $char, self::DELIM );
						break;
				}

				++ $pos;
			}

			return [
				'pattern' => $head . $buf . $tail,
				'negated' => $negated,
			];
		}

		public function get_patterns(): array {
			return $this->patterns;
		}
	}
}
