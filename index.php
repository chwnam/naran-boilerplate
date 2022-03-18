<?php

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

const NBPC_MAIN_FILE = __FILE__;
const NBPC_VERSION   = '1.3.4';
const NBPC_PRIORITY  = 100;

nbpc();
