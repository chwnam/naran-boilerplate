<?php
if ( ! interface_exists( 'NBPC_CLI_Replace' ) ) {
	interface NBPC_CLI_Replace {
		public function replace( string $content ): string;
	}
}
