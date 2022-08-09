<?php
use PhpParser\NodeVisitor;

if ( ! interface_exists( 'NBPC_CLI_Node_Visitor' ) ) {
	interface NBPC_CLI_Node_Visitor extends NodeVisitor {
		public function get_tokens(): array;
	}
}
