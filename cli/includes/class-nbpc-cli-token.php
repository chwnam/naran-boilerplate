<?php
if ( ! class_exists( 'NBPC_CLI_Token' ) ) {
	class NBPC_CLI_Token {
		public const TYPE_COMMENT    = 'comment';
		public const TYPE_IDENTIFIER = 'identifier';
		public const TYPE_NAMESPACE  = 'namespace';
		public const TYPE_STRING     = 'string';
		public const TYPE_TEXTDOMAIN = 'textdomain';
		public const TYPE_VARIABLE   = 'variable';

		public function __construct(
			public string $type,
			public string $value,
			public int $start_pos,
			public int $end_pos
		) {
		}
	}
}
