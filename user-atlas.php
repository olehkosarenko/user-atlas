<?php
/**
 * Plugin Name: User Atlas
 * Plugin URI: https://github.com/olehkosarenko/user-atlas
 * Description: This plugin displays users in a dynamic table.
 * Version: 1.0
 * Author: Oleh Kosarenko
 * Author URI: https://github.com/olehkosarenko
 * License: GPL-3.0+
 */

namespace WpApp\UserAtlas;

defined( 'ABSPATH' ) || exit;

define( 'WP_APP_USER_ATLAS_FILE', __FILE__ );

if ( ! class_exists( UserAtlas::class ) && is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

class_exists( UserAtlas::class ) && UserAtlas::instance();
