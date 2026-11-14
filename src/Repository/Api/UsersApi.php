<?php
/**
 * Users Api
 *
 * @package WpApp\UserAtlas\Api
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Repository\Api;

defined( 'ABSPATH' ) || die();

trait UsersApi {
	/**
	 * Returns all users
	 *
	 * @return array
	 */
	public function users(): array {
		$url = 'users';

		return $this->send( $url );
	}

	/**
	 * Returns a single user
	 *
	 * @param int $id User ID.
	 */
	public function user( int $id ): array {
		$url = 'users/' . $id;

		return $this->send( $url );
	}
}
