<?php
/**
 * Comments Api
 *
 * @package WpApp\UserAtlas\Api
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Repository\Api;

defined( 'ABSPATH' ) || die();

/**
 * Comments Api
 *
 * @package WpApp\UserAtlas\Api
 */
trait CommentsApi {
	/**
	 * Returns all comments
	 *
	 * @return array
	 */
	public function comments(): array {
		$url = 'comments';

		return $this->send( $url );
	}

	/**
	 * Returns a single comment
	 *
	 * @param int $id Comment ID.
	 */
	public function commentByPostId( int $id ): array {
		$url = 'comments/?postId=' . $id;

		return $this->send( $url );
	}
}
