<?php
/**
 * Asset
 *
 * @package WpApp\UserAtlas\Service\Asset
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Service\Asset;

use WpApp\UserAtlas\Service\Option\Option;
use WpApp\UserAtlas\Service\Rest\UsersRoute;

defined( 'ABSPATH' ) || die();

/**
 * Class Asset
 *
 * @package WpApp\UserAtlas\Service\Asset
 */
class Asset {
	/**
	 * Script and style handle.
	 *
	 * @var string
	 */
	public const HANDLE = 'user-atlas';

	/**
	 * Build directory relative to the plugin root.
	 *
	 * @var string
	 */
	private const BUILD_DIR = 'assets/build/';

	/**
	 * Options instance.
	 *
	 * @var Option
	 */
	private Option $option;

	/**
	 * ScriptsManager constructor.
	 *
	 * @param Option|null $option Option instance.
	 *
	 * @return void
	 */
	public function __construct( ?Option $option = null ) {
		$this->option = $option ?? Option::instance();
	}

	/**
	 * Add a global args to the page
	 *
	 * @return array
	 */
	public function globalArgs(): array {
		$title      = $this->option->get( 'title', 'User Atlas' );
		$title      = apply_filters( 'user_atlas__title', $title );
		$globalArgs = [
			'title'       => $title,
			'isUserAdmin' => current_user_can( 'manage_options' ),
			'restUrl'     => esc_url_raw( rest_url( UsersRoute::REST_NAMESPACE . '/' ) ),
		];

		// Logged-in users need the REST nonce to be recognized; anonymous requests work without it,
		// so a cached page never depends on a nonce that can expire.
		if ( is_user_logged_in() ) {
			$globalArgs['restNonce'] = wp_create_nonce( 'wp_rest' );
		}

		return apply_filters( 'user_atlas__global_args', $globalArgs );
	}

	/**
	 * Register the scripts and stylesheets
	 *
	 * @return void
	 */
	public function enqueueScripts(): void {
		$buildPath = plugin_dir_path( WP_APP_USER_ATLAS_FILE ) . self::BUILD_DIR;
		$assetFile = $buildPath . 'index.asset.php';
		if ( ! is_readable( $assetFile ) ) {
			return;
		}

		$asset    = require $assetFile;
		$buildUrl = plugin_dir_url( WP_APP_USER_ATLAS_FILE ) . self::BUILD_DIR;

		wp_enqueue_script(
			self::HANDLE,
			$buildUrl . 'index.js',
			$asset['dependencies'],
			$asset['version'],
			[ 'in_footer' => true ]
		);

		wp_add_inline_script(
			self::HANDLE,
			'var global_user_atlas = ' . wp_json_encode( $this->globalArgs() ) . ';',
			'before'
		);

		if ( is_readable( $buildPath . 'index.css' ) ) {
			wp_enqueue_style(
				self::HANDLE,
				$buildUrl . 'index.css',
				[],
				$asset['version']
			);
			wp_style_add_data( self::HANDLE, 'rtl', 'replace' );
		}
	}
}
