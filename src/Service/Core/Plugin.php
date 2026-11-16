<?php
/**
 * Plugin
 *
 * @package WpApp\UserAtlas\Service\Core
 */

namespace WpApp\UserAtlas\Service\Core;

defined( 'ABSPATH' ) || die();

/**
 * Plugin
 */
class Plugin {
	/**
	 * The basename of the plugin.
	 *
	 * @var string
	 */
	private string $basename = '';

	/**
	 * The name of the plugin.
	 *
	 * @var string
	 */
	private string $name = '';

	/**
	 * The slug of the plugin.
	 *
	 * @var string
	 */
	private string $slug = '';

	/**
	 * Initialize the plugin
	 *
	 * @return void
	 */
	public function init(): void {
		$this->basename = basename( WP_APP_USER_ATLAS_FILE );
		$plugin         = get_plugin_data( WP_APP_USER_ATLAS_FILE );
		$this->name     = $plugin['Name'];
		$this->slug     = $plugin['TextDomain'];
	}

	/**
	 * Get the name of the plugin.
	 *
	 * @return string
	 */
	public function getBasename(): string {
		return $this->basename;
	}

	/**
	 * Get the file name of the plugin, used as the settings page slug.
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Get the slug of the plugin.
	 *
	 * @return string
	 */
	public function getSlug(): string {
		return $this->slug;
	}
}
