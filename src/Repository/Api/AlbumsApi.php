<?php
/**
 * Trait AlbumsApi
 *
 * @package WpApp\UserAtlas\Api
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Repository\Api;

defined( 'ABSPATH' ) || die();

trait AlbumsApi {
	/**
	 * Returns all albums
	 *
	 * @return array
	 */
	public function albums(): array {
		$url = 'albums';

		return $this->send( $url );
	}

	/**
	 * Returns a single album
	 *
	 * @param int $id Album ID.
	 */
	public function album( int $id ): array {
		$url = 'albums/' . $id;

		return $this->send( $url );
	}

	/**
	 * Returns all photos from a single album
	 *
	 * @param int $id Album ID.
	 */
	public function albumsPhotos( int $id ): array {
		$url = 'albums/' . $id . '/photos';

		return $this->send( $url );
	}
}
