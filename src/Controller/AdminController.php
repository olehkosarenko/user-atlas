<?php
/**
 * Admin Controller
 *
 * @package WpApp\UserAtlas\Controller
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Controller;

use WpApp\UserAtlas\Service\Admin\AdminSetting;

defined( 'ABSPATH' ) || die();

/**
 * Class AdminController
 *
 * @package WpApp\UserAtlas\Controller
 */
class AdminController {
	/**
	 * Settings instance.
	 *
	 * @var object AdminSettings
	 */
	private object $setting;

	/**
	 * Cache Manager instance.
	 *
	 * @var object CacheManager
	 */
	private object $cacheManager;

	/**
	 * Controller constructor.
	 *
	 * @param AdminSetting|null $setting Settings instance.
	 */
	public function __construct( AdminSetting $setting = null ) {
		$this->setting = $setting ?? new AdminSetting();
	}

	/**
	 * Setup hooks
	 *
	 * @return void
	 */
	public function init(): void {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_action( 'admin_menu', [ $this->setting, 'menu' ] );
		add_action( 'admin_init', [ $this->setting, 'fields' ] );
	}
}
