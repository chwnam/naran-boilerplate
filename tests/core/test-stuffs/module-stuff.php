<?php

/**
 * @property-read NBPC_Submodule_Stuff $sub
 */
class NBPC_Module_Stuff implements NBPC_Module {
	use NBPC_Submodule_Impl;

	public function __construct() {
		$this->assign_modules(
			[
				'sub' => NBPC_Submodule_Stuff::class,
			]
		);
	}
}
