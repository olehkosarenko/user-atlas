<?php
/**
 * Plugin Name: User Atlas
 * Plugin URI: https://github.com/olehkosarenko/user-atlas
 * Description: This plugin displays users in a dynamic table.
 * Version: 1.0
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * Author: Oleh Kosarenko
 * Author URI: https://github.com/olehkosarenko
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: user-atlas
 *
 * @package WpApp\UserAtlas
 */

namespace WpApp\UserAtlas;

defined( 'ABSPATH' ) || exit;

define( 'WP_APP_USER_ATLAS_FILE', __FILE__ );

if ( ! class_exists( UserAtlas::class ) && is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

class_exists( UserAtlas::class ) && UserAtlas::instance();
