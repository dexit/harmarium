<?php
/**
 * Plugin Name:  Harmarium Extensions
 * Plugin URI:   https://harmarium.com
 * Description:  Custom blocks, commission/quote requests, QR codes, portfolio↔product bridges, and REST endpoints for Harmarium.
 * Version:      1.0.0
 * Author:       Harmarium
 * Author URI:   https://harmarium.com
 * License:      GPL-2.0-or-later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  harmarium-ext
 * Requires PHP: 8.0
 * Requires at least: 6.4
 */

defined( 'ABSPATH' ) || exit;

define( 'HARMARIUM_EXT_VERSION', '1.0.0' );
define( 'HARMARIUM_EXT_DIR', plugin_dir_path( __FILE__ ) );
define( 'HARMARIUM_EXT_URI', plugin_dir_url( __FILE__ ) );

require_once HARMARIUM_EXT_DIR . 'lib/qr-svg.php';
require_once HARMARIUM_EXT_DIR . 'inc/meta-fields.php';
require_once HARMARIUM_EXT_DIR . 'inc/commission.php';
require_once HARMARIUM_EXT_DIR . 'inc/bridges.php';
require_once HARMARIUM_EXT_DIR . 'inc/rest-api.php';
require_once HARMARIUM_EXT_DIR . 'inc/blocks.php';
