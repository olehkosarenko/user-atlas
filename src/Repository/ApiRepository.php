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

defined( 'ABSPATH' ) || die();

/**
 * Api class
 *
 * @package WpApp\UserAtlas\Repository
 * @since   1.0.0
 */
class ApiRepository extends AbstractApi {
	use UsersApi;
}
