<?php

if ( ! class_exists( 'NBPC_CLI_Regex_Replace' ) ) {
	class NBPC_CLI_Regex_Replace implements NBPC_CLI_Replace {
		private string $regex;

		private string $old_slug_lower;

		private string $old_slug_upper;

		private string $new_slug_lower;

		private string $new_slug_upper;

		public function __construct( string $old_slug, string $new_slug ) {
			$this->old_slug_lower = nbpc_cli_lower_slug( $old_slug );
			$this->new_slug_lower = nbpc_cli_lower_slug( $new_slug );
			$this->old_slug_upper = nbpc_cli_upper_slug( $old_slug );
			$this->new_slug_upper = nbpc_cli_upper_slug( $new_slug );

			$slug        = preg_quote( $this->old_slug_lower ) . '|' . preg_quote( $this->old_slug_upper );
			$this->regex = "/(?<=^|\s|[[:punct:]])(?<slug>($slug))(?=[[:punct:]]|\s|$)/";
		}

		public function replace( string $content ): string {
			return (string) preg_replace_callback( $this->regex, [ $this, 'match_replace' ], $content );
		}

		public function match_replace( array $matches ): string {
			return match ( $matches['slug'] ?? null ) {
				$this->old_slug_lower => $this->new_slug_lower,
				$this->old_slug_upper => $this->new_slug_upper,
			};
		}
	}
}
