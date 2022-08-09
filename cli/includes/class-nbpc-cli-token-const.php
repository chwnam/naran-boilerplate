<?php

if ( ! class_exists( 'NBPC_CLI_Token_Const' ) ) {
	class NBPC_CLI_Token_Const {
		public function __construct(
			public NBPC_CLI_Token $name,
			public NBPC_CLI_Token $value,
		) {
		}
	}
}