<?php
/**
 * REST Controller
 *
 * @package WpApp\UserAtlas\Controller
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Controller;

use WpApp\UserAtlas\Service\Rest\UsersRoute;

defined( 'ABSPATH' ) || die();

/**
 * Class RestController
 *
 * @package WpApp\UserAtlas\Controller
 */
class RestController {
	/**
	 * Users route.
	 *
	 * @var UsersRoute
	 */
	private UsersRoute $usersRoute;

	/**
	 * RestController constructor.
	 *
	 * @param UsersRoute|null $usersRoute Users route.
	 */
	public function __construct( ?UsersRoute $usersRoute = null ) {
		$this->usersRoute = $usersRoute ?? new UsersRoute();
	}

	/**
	 * Setup hooks
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'rest_api_init', [ $this->usersRoute, 'register' ] );
	}
}
