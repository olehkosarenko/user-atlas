<?php
/**
 * Class UserAtlas
 *
 * @package WpApp\UserAtlas
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas;

use WpApp\UserAtlas\Helper\SingletonTrait;
use WpApp\UserAtlas\Controller\AdminController;
use WpApp\UserAtlas\Controller\RestController;
use WpApp\UserAtlas\Controller\ViewController;

/**
 * Class UserAtlas
 *
 * @package WpApp\UserAtlas
 */
final class UserAtlas {
	use SingletonTrait;

	/**
	 * Init the plugin
	 *
	 * SingletonTrait::instance() calls this only when WordPress is not installing.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'init', [ new RestController(), 'init' ] );
		add_action( 'init', [ new AdminController(), 'init' ] );
		add_action( 'init', [ new ViewController(), 'init' ] );
	}
}
