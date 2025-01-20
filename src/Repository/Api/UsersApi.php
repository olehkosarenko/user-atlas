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

	/**
	 * Returns all posts from a single user
	 *
	 * @param int $id User ID.
	 */
	public function userPosts( int $id ): array {
		$url = 'users/' . $id . '/posts';

		return $this->send( $url );
	}

	/**
	 * Returns all albums from a single user
	 *
	 * @param int $id User ID.
	 */
	public function userAlbums( int $id ): array {
		$url = 'users/' . $id . '/albums';

		return $this->send( $url );
	}

	/**
	 * Returns all todos from a single user
	 *
	 * @param int $id User ID.
	 */
	public function userTodos( int $id ): array {
		$url = 'users/' . $id . '/todos';

		return $this->send( $url );
	}
}
