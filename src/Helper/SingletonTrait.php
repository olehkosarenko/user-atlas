<?php
/**
 * SingletonTrait trait
 *
 * @package WP_Rocket
 */

namespace WpApp\UserAtlas\Helper;

defined( 'ABSPATH' ) || die();

/**
 * SingletonTrait trait
 */
trait SingletonTrait {
	/**
	 * Instance of the plugin
	 */
	public static function instance(): self {
		static $instance;
		if ( ! $instance ) {
			$instance = new self();
			if ( ! wp_installing() && method_exists( $instance, 'init' ) ) {
				$instance->init();
			}
		}

		return $instance;
	}

	/**
	 * Clone method
	 */
	private function __clone() {
	}

	/**
	 * Wakeup method
	 *
	 * @throws \Exception When trying to unserialize a singleton.
	 */
	private function __wakeup() {
		throw new \Exception( 'Cannot unserialize a singleton.' );
	}
}
