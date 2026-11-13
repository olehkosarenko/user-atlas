<?php
/**
 * Remove plugin data when the plugin is deleted
 *
 * @package WpApp\UserAtlas
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'user_atlas__options' );
delete_option( 'user_atlas__cache_version' );
delete_transient( 'user_atlas_api_paused' );

// Cached API responses are stored under hashed transient keys and expire on their own,
// so they are not enumerated here.
