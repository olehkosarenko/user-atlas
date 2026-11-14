<?php
/**
 * Users Api
 *
 * @package WpApp\UserAtlas\Repository\Api
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Repository\Api;

use WP_Error;

defined( 'ABSPATH' ) || die();

trait UsersApi {
	/**
	 * Returns all users
	 *
	 * @return array|WP_Error
	 */
	public function users() {
		$url = 'users';

		return $this->send( $url );
	}

	/**
	 * Returns a single user
	 *
	 * @param int $id User ID.
	 *
	 * @return array|WP_Error
	 */
	public function user( int $id ) {
		$url = 'users/' . $id;

		return $this->send( $url );
	}
}
