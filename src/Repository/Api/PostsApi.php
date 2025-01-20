<?php
/**
 * Trait PostsApi
 *
 * @package WpApp\UserAtlas\Api
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Repository\Api;

defined( 'ABSPATH' ) || die();

trait PostsApi {
	/**
	 * Returns all posts
	 *
	 * @return array
	 */
	public function posts(): array {
		$url = 'posts';

		return $this->send( $url );
	}

	/**
	 * Returns a single post
	 *
	 * @param int $id Post ID.
	 */
	public function post( int $id ): array {
		$url = 'posts/' . $id;

		return $this->send( $url );
	}

	/**
	 * Returns all comments from a single post
	 *
	 * @param int $id Post ID.
	 */
	public function postComments( int $id ): array {
		$url = 'posts/' . $id . '/comments';

		return $this->send( $url );
	}
}
