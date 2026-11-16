<?php
/**
 * SingletonTrait trait
 *
 * @package WpApp\UserAtlas\Helper
 */

namespace WpApp\UserAtlas\Helper;

defined( 'ABSPATH' ) || die();

/**
 * SingletonTrait trait
 */
trait SingletonTrait {
	/**
	 * Get the single instance of the class, initialized on first use
	 *
	 * @return self
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
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize a singleton.' );
	}
}
