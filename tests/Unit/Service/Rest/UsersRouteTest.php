<?php
/**
 * Tests of UsersRoute
 *
 * @package WpApp\UserAtlas\Tests
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Tests\Unit\Service\Rest;

use Brain\Monkey\Functions;
use Mockery;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WpApp\UserAtlas\Repository\ApiRepository;
use WpApp\UserAtlas\Service\Rest\UsersRoute;
use WpApp\UserAtlas\Tests\Unit\TestCase;

/**
 * @covers \WpApp\UserAtlas\Service\Rest\UsersRoute
 */
final class UsersRouteTest extends TestCase {
	/**
	 * Set up REST helpers.
	 *
	 * @return void
	 */
	protected function set_up(): void {
		parent::set_up();

		Functions\when( 'rest_ensure_response' )->alias(
			static function ( $data ) {
				return new WP_REST_Response( $data );
			}
		);
		Functions\when( 'wp_date' )->alias(
			static function ( string $format, int $timestamp ): string {
				return gmdate( $format, $timestamp );
			}
		);
		Functions\when( 'current_user_can' )->justReturn( false );
	}

	/**
	 * API repository double; the external API itself is tested in ApiRepositoryTest.
	 *
	 * @return ApiRepository|Mockery\MockInterface
	 */
	private function api() {
		return Mockery::mock( ApiRepository::class );
	}

	public function test_both_routes_are_public_and_read_only(): void {
		$routes = [];
		Functions\when( 'register_rest_route' )->alias(
			static function ( string $namespace, string $route, array $args ) use ( &$routes ): void {
				$routes[ $namespace . $route ] = $args;
			}
		);

		( new UsersRoute( $this->api() ) )->register();

		$this->assertSame( [ 'user-atlas/v1/users', 'user-atlas/v1/users/(?P<id>[\d]+)' ], array_keys( $routes ) );
		foreach ( $routes as $args ) {
			$this->assertSame( 'GET', $args['methods'] );
			$this->assertSame( '__return_true', $args['permission_callback'] );
		}

		$id = $routes['user-atlas/v1/users/(?P<id>[\d]+)']['args']['id'];
		$this->assertSame( 'integer', $id['type'] );
		$this->assertSame( 1, $id['minimum'] );
		$this->assertTrue( $id['required'] );
	}

	public function test_users_returns_the_list_without_the_expiration_for_visitors(): void {
		$api = $this->api();
		$api->shouldReceive( 'users' )->once()->andReturn( [ [ 'id' => 1 ] ] );
		$api->shouldReceive( 'expiration' )->andReturn( 1700000000 );

		$response = ( new UsersRoute( $api ) )->users();

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( [ 'content' => [ [ 'id' => 1 ] ] ], $response->get_data() );
	}

	public function test_administrators_also_get_the_cache_expiration(): void {
		Functions\when( 'current_user_can' )->alias(
			static function ( string $capability ): bool {
				return 'manage_options' === $capability;
			}
		);
		$api = $this->api();
		$api->shouldReceive( 'users' )->andReturn( [] );
		$api->shouldReceive( 'expiration' )->andReturn( 1700000000 );

		$data = ( new UsersRoute( $api ) )->users()->get_data();

		$this->assertSame( '2023-11-14 22:13:20', $data['expiration'] );
	}

	public function test_an_uncached_response_has_no_expiration_even_for_administrators(): void {
		Functions\when( 'current_user_can' )->justReturn( true );
		$api = $this->api();
		$api->shouldReceive( 'users' )->andReturn( [] );
		$api->shouldReceive( 'expiration' )->andReturn( 0 );

		$this->assertSame( [ 'content' => [] ], ( new UsersRoute( $api ) )->users()->get_data() );
	}

	public function test_api_errors_are_returned_unchanged(): void {
		$error = new WP_Error( 'user_atlas_api_unavailable', 'Unavailable', [ 'status' => 503 ] );
		$api   = $this->api();
		$api->shouldReceive( 'users' )->andReturn( $error );

		$this->assertSame( $error, ( new UsersRoute( $api ) )->users() );
	}

	public function test_a_known_user_is_returned(): void {
		$api = $this->api();
		$api->shouldReceive( 'users' )->andReturn( [ [ 'id' => 1 ], [ 'id' => 2 ] ] );
		$api->shouldReceive( 'user' )->once()->with( 2 )->andReturn( [ 'id' => 2 ] );
		$api->shouldReceive( 'expiration' )->andReturn( 0 );

		$response = ( new UsersRoute( $api ) )->user( new WP_REST_Request( [ 'id' => '2' ] ) );

		$this->assertSame( [ 'content' => [ 'id' => 2 ] ], $response->get_data() );
	}

	public function test_an_unknown_user_answers_404_without_requesting_it(): void {
		$api = $this->api();
		$api->shouldReceive( 'users' )->andReturn( [ [ 'id' => 1 ] ] );
		$api->shouldNotReceive( 'user' );

		$result = ( new UsersRoute( $api ) )->user( new WP_REST_Request( [ 'id' => 99 ] ) );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'user_atlas_not_found', $result->get_error_code() );
		$this->assertSame( [ 'status' => 404 ], $result->get_error_data() );
	}

	public function test_a_failing_users_list_is_returned_for_a_single_user_request(): void {
		$error = new WP_Error( 'user_atlas_api_error', 'Failed', [ 'status' => 502 ] );
		$api   = $this->api();
		$api->shouldReceive( 'users' )->andReturn( $error );
		$api->shouldNotReceive( 'user' );

		$this->assertSame( $error, ( new UsersRoute( $api ) )->user( new WP_REST_Request( [ 'id' => 1 ] ) ) );
	}
}
