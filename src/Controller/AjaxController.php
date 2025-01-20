<?php
/**
 * Ajax Controller
 *
 * @package WpApp\UserAtlas\Controller
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Controller;

use WpApp\UserAtlas\Service\Ajax\Ajax;

defined( 'ABSPATH' ) || die();

/**
 * Class AjaxController
 *
 * @package WpApp\UserAtlas\Controller
 */
class AjaxController {
	/**
	 * Ajax service.
	 *
	 * @var object|Ajax $ajax Ajax service.
	 */
	private object $ajax;

	/**
	 * Ajax constructor.
	 *
	 * @param Ajax|null $ajax Ajax service.
	 *
	 * @return void
	 */
	public function __construct( Ajax $ajax = null ) {
		$this->ajax = $ajax ?? new Ajax();
	}

	/**
	 * Setup hooks
	 *
	 * @return void
	 */
	public function init(): void {
		if ( ! wp_doing_ajax() ) {
			return;
		}
		add_action( 'wp_ajax_user_atlas', [ $this->ajax, 'handler' ] );
		add_action( 'wp_ajax_nopriv_user_atlas', [ $this->ajax, 'handler' ] );
	}
}
