<?php
/**
 * ApiRepository class
 *
 * @package WpApp\UserAtlas\Repository
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Repository;

use WpApp\UserAtlas\Repository\Api\AbstractApi;
use WpApp\UserAtlas\Repository\Api\UsersApi;
use WpApp\UserAtlas\Repository\Api\PostsApi;
use WpApp\UserAtlas\Repository\Api\CommentsApi;
use WpApp\UserAtlas\Repository\Api\AlbumsApi;

defined( 'ABSPATH' ) || die();

/**
 * Api class
 *
 * @package WpApp\UserAtlas\Repository
 * @since   1.0.0
 */
class ApiRepository extends AbstractApi {
	/**
	 * UsersApi - contains methods for users endpoint
	 *
	 * @method users()
	 * @method user( int $id )
	 * @method userPosts( int $id )
	 * @method userAlbums( int $id )
	 * @method userTodos( int $id )
	 */
	use UsersApi;

	/**
	 * PostsApi - contains methods for posts endpoint
	 *
	 * @method posts()
	 * @method post( int $id )
	 * @method postsComments( int $id )
	 */
	use PostsApi;

	/**
	 * CommentsApi - contains methods for comments endpoint
	 *
	 * @method comments()
	 * @method commentByPostId( int $id )
	 */
	use CommentsApi;

	/**
	 * AlbumsApi - contains methods for albums endpoint
	 *
	 * @method albums()
	 * @method album( int $id )
	 * @method albumsPhotos( int $id )
	 */
	use AlbumsApi;
}
