<?php
/**
 * Asset
 *
 * @package WpApp\UserAtlas\Service\Asset
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Service\Asset;

use WpApp\UserAtlas\Service\Option\Option;

defined( 'ABSPATH' ) || die();

/**
 * Class Asset
 *
 * @package WpApp\UserAtlas\Service\Asset
 */
class Asset {
	/**
	 * Pathes.
	 *
	 * @var array $pathes Pathes.
	 */
	private array $pathes;

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
	public function __construct( Option $option = null ) {
		$this->option = $option ?? Option::instance();
		$this->pathes = [];
	}

	/**
	 * Add a global args to the page
	 *
	 * @return array
	 */
	public function globalArgs(): array {
		$title      = $this->option->option( 'title', 'User Atlas' );
		$title      = apply_filters( 'user_atlas__title', $title );
		$globalArgs = [
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'user_atlas-nonce' ),
			'title'       => $title,
			'isUserAdmin' => current_user_can( 'manage_options' ),
		];

		return apply_filters( 'user_atlas__global_args', $globalArgs );
	}

	/**
	 * Add patches
	 *
	 * @return void
	 */
	public function addPatches(): void {
		$this->pathes = [
			'pluginDirUrl'    => plugin_dir_url( WP_APP_USER_ATLAS_FILE ),
			'entryPointsFile' => plugin_dir_path( WP_APP_USER_ATLAS_FILE ) . '/assets/build/asset-manifest.json',
		];
	}

	/**
	 * Check if manifest file exists and is readable
	 *
	 * @return bool
	 */
	public function isManifestReadable(): bool {
		$status = true;

		if ( ! isset( $this->pathes['entryPointsFile'] ) ) {
			$status = false;
		}

		if ( ! file_exists( $this->pathes['entryPointsFile'] ) ) {
			$status = false;
		}

		if ( ! is_readable( $this->pathes['entryPointsFile'] ) ) {
			$status = false;
		}

		return $status;
	}

	/**
	 * Register the scripts and stylesheets
	 *
	 * @return void
	 *
	 * phpcs:disable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	 */
	public function enqueueScripts(): void {
		$this->addPatches();
		if ( ! $this->isManifestReadable() ) {
			return;
		}

		$globalArgs  = $this->globalArgs();
		$entryPoints = json_decode( file_get_contents( $this->pathes['entryPointsFile'] ), true );
		foreach ( $entryPoints['entrypoints'] as $entryPoint ) {
			if ( str_contains( $entryPoint, '.js' ) ) {
				wp_enqueue_script(
					'user_atlas-' . sanitize_title( $entryPoint ),
					$this->pathes['pluginDirUrl'] . '/assets/build/' . $entryPoint,
					[],
					time(),
					[ 'in_footer' => true ]
				);

				wp_add_inline_script(
					'user_atlas-' . sanitize_title( $entryPoint ),
					'var global_user_atlas = ' . wp_json_encode( $globalArgs ),
					'before'
				);
			}

			if ( str_contains( $entryPoint, '.css' ) ) {
				wp_enqueue_style(
					'user_atlas-' . sanitize_title( $entryPoint ),
					$this->pathes['pluginDirUrl'] . '/assets/build/' . $entryPoint,
					[],
					time()
				);
			}
		}
	}
}
