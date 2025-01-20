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
	private $file;

	/**
	 * The basename of the plugin.
	 *
	 * @var string
	 */
	private $basename;

	/**
	 * The name of the plugin.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * The version of the plugin.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * The slug of the plugin.
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * The url of the plugin.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * The path of the plugin.
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Initialize the plugin
	 *
	 * @return void
	 */
	public function init(): void {
		$this->file     = WP_APP_USER_TABLES_FILE;
		$this->basename = basename( WP_APP_USER_TABLES_FILE );
		$plugin         = get_plugin_data( WP_APP_USER_TABLES_FILE );
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
	public function getFile() {
		return $this->file;
	}

	/**
	 * Get the name of the plugin.
	 *
	 * @return string
	 */
	public function getBasename() {
		return $this->basename;
	}

	/**
	 * Get the name of the plugin.
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get the version of the plugin.
	 *
	 * @return string
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * Get the slug of the plugin.
	 *
	 * @return string
	 */
	public function getSlug() {
		return $this->slug;
	}

	/**
	 * Get the url of the plugin.
	 *
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * Get the path of the plugin.
	 *
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}
}
