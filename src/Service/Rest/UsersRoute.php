<?php
/**
 * Users REST route
 *
 * @package WpApp\UserAtlas\Service\Rest
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Service\Rest;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WpApp\UserAtlas\Repository\ApiRepository;

defined( 'ABSPATH' ) || die();

/**
 * Class UsersRoute
 *
 * Public, read-only routes that return users from the external API.
 *
 * @package WpApp\UserAtlas\Service\Rest
 */
class UsersRoute {
	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	public const REST_NAMESPACE = 'user-atlas/v1';

	/**
	 * API repository, created on the first request that needs it.
	 *
	 * @var ApiRepository|null
	 */
	private ?ApiRepository $api;

	/**
	 * UsersRoute constructor.
	 *
	 * @param ApiRepository|null $api API repository.
	 */
	public function __construct( ?ApiRepository $api = null ) {
		$this->api = $api;
	}

	/**
	 * Register the routes
	 *
	 * @return void
	 */
	public function register(): void {
		register_rest_route(
			self::REST_NAMESPACE,
			'/users',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'users' ],
				// The route only reads public data, so it needs no capability and no nonce.
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/users/(?P<id>[\d]+)',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'user' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'id' => [
						'description' => __( 'User ID.', 'user-atlas' ),
						'type'        => 'integer',
						'minimum'     => 1,
						'required'    => true,
					],
				],
			]
		);
	}

	/**
	 * Get all users
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function users() {
		return $this->respond( $this->api()->users() );
	}

	/**
	 * Get a single user
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function user( WP_REST_Request $request ) {
		return $this->respond( $this->api()->user( (int) $request['id'] ) );
	}

	/**
	 * Turn an API result into a REST response
	 *
	 * @param array $content API result.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	private function respond( array $content ) {
		if ( isset( $content['status'], $content['message'] ) && 'error' === $content['status'] ) {
			return new WP_Error( 'user_atlas_api_error', $content['message'], [ 'status' => 502 ] );
		}

		$data       = [ 'content' => $content ];
		$expiration = $this->api()->expiration();
		if ( $expiration > 0 && current_user_can( 'manage_options' ) ) {
			$data['expiration'] = wp_date( 'Y-m-d H:i:s', $expiration );
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Get the API repository
	 *
	 * @return ApiRepository
	 */
	private function api(): ApiRepository {
		if ( null === $this->api ) {
			$this->api = new ApiRepository();
		}

		return $this->api;
	}
}
