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

/**
 * Print an admin notice about files that have to be generated before the plugin can work
 *
 * @param string $type    Notice type: error or warning.
 * @param string $message Notice text.
 * @param string $command Command that generates the missing files.
 *
 * @return void
 */
function buildNotice( string $type, string $message, string $command ): void {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-%1$s"><p>%2$s <code>%3$s</code></p></div>',
		esc_attr( $type ),
		esc_html( $message ),
		esc_html( $command )
	);
}

if ( ! class_exists( UserAtlas::class ) && is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

if ( ! class_exists( UserAtlas::class ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			buildNotice(
				'error',
				__( 'User Atlas cannot load because its Composer dependencies are missing. Run this command in the plugin folder:', 'user-atlas' ),
				'composer install --no-dev'
			);
		}
	);

	return;
}

if ( ! is_readable( __DIR__ . '/assets/build/index.asset.php' ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			buildNotice(
				'warning',
				__( 'The User Atlas page stays empty because the frontend is not built. Run this command in the plugin folder:', 'user-atlas' ),
				'cd assets && npm ci && npm run build'
			);
		}
	);
}

UserAtlas::instance();
