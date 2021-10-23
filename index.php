<?php
/**
 * Naran Boilerplate Code
 *
 * @package           nbpc
 * @author            changwoo
 * @copyright         2021 Changwoo Nam
 * @license           GPL-2.0-or-later
 * @version           1.0.0
 * @wordpress-plugin
 *
 * Plugin Name:       Naran Boilerplate Code
 * Plugin URI:        https://github.com/chwnam/naran-boilerplate-code
 * Description:       Naran boilerplate code for WordPress plugins/themes.
 * Version:           1.0.0
 * Requires at least: 5.5.0
 * Requires PHP:      7.4
 * Author:            changwoo
 * Author URI:        https://blog.changwoo.pe.kr/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:
 * Text Domain:       nbpc
 * Domain Path:       /languages
 * CPBN version:      1.0.0
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

const NBPC_MAIN     = __FILE__;
const NBPC_VERSION  = '1.0.0';
const NBPC_PRIORITY = 100;

nbpc();
