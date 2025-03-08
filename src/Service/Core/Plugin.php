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
	 * The file of the plugin.
	 *
	 * @var string
	 */
	private string $file = '';

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
	 * The version of the plugin.
	 *
	 * @var string
	 */
	private string $version = '';

	/**
	 * The slug of the plugin.
	 *
	 * @var string
	 */
	private string $slug = '';

	/**
	 * The url of the plugin.
	 *
	 * @var string
	 */
	private string $url = '';

	/**
	 * The path of the plugin.
	 *
	 * @var string
	 */
	private string $path = '';

	/**
	 * Initialize the plugin
	 *
	 * @return void
	 */
	public function init(): void {
		$this->file     = WP_APP_USER_ATLAS_FILE;
		$this->basename = basename( WP_APP_USER_ATLAS_FILE );
		$plugin         = get_plugin_data( WP_APP_USER_ATLAS_FILE );
		$this->name     = $plugin['Name'];
		$this->slug     = $plugin['TextDomain'];
		$this->version  = $plugin['Version'];
		$this->url      = plugin_dir_url( $this->file );
		$this->path     = plugin_dir_path( $this->file );
	}

	/**
	 * Get the name of the plugin.
	 *
	 * @return string
	 */
	public function getFile(): string {
		return $this->file;
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
	 * Get the name of the plugin.
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Get the version of the plugin.
	 *
	 * @return string
	 */
	public function getVersion(): string {
		return $this->version;
	}

	/**
	 * Get the slug of the plugin.
	 *
	 * @return string
	 */
	public function getSlug(): string {
		return $this->slug;
	}

	/**
	 * Get the url of the plugin.
	 *
	 * @return string
	 */
	public function getUrl(): string {
		return $this->url;
	}

	/**
	 * Get the path of the plugin.
	 *
	 * @return string
	 */
	public function getPath(): string {
		return $this->path;
	}
}
