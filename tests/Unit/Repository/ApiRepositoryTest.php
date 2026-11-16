<?php
/**
 * Tests of ApiRepository and AbstractApi
 *
 * @package WpApp\UserAtlas\Tests
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Tests\Unit\Repository;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Error;
use WpApp\UserAtlas\Repository\ApiRepository;
use WpApp\UserAtlas\Service\Cache\ApiCache;
use WpApp\UserAtlas\Tests\Unit\TestCase;

/**
 * @covers \WpApp\UserAtlas\Repository\Api\AbstractApi
 * @covers \WpApp\UserAtlas\Repository\ApiRepository
 */
final class ApiRepositoryTest extends TestCase {
	/**
	 * URLs requested from the API.
	 *
	 * @var array
	 */
	private array $requests = [];

	/**
	 * Arguments of the last API request.
	 *
	 * @var array
	 */
	private array $lastArgs = [];

	/**
	 * Stub the WordPress HTTP API with the given response.
	 *
	 * @param array|WP_Error $response Response of wp_remote_get().
	 *
	 * @return void
	 */
	private function respondWith( $response ): void {
		Functions\when( 'wp_remote_get' )->alias(
			function ( string $url, array $args ) use ( $response ) {
				$this->requests[] = $url;
				$this->lastArgs   = $args;

				return $response;
			}
		);
		Functions\when( 'wp_remote_retrieve_response_code' )->alias(
			static function ( $response ) {
				return $response['response']['code'] ?? '';
			}
		);
		Functions\when( 'wp_remote_retrieve_body' )->alias(
			static function ( $response ) {
				return $response['body'] ?? '';
			}
		);
	}

	/**
	 * Build an HTTP API response.
	 *
	 * @param int    $code HTTP status code.
	 * @param string $body Response body.
	 *
	 * @return array
	 */
	private function response( int $code, string $body = '' ): array {
		return [
			'response' => [ 'code' => $code ],
			'body'     => $body,
		];
	}

	public function test_users_are_requested_decoded_and_cached(): void {
		$this->respondWith( $this->response( 200, '[{"id":1},{"id":2}]' ) );
		$api = new ApiRepository();

		$users = $api->users();

		$this->assertSame( [ [ 'id' => 1 ], [ 'id' => 2 ] ], $users );
		$this->assertSame( [ 'https://jsonplaceholder.typicode.com/users' ], $this->requests );
		$this->assertGreaterThan( 0, $api->expiration() );
		$this->assertSame( $users, ( new ApiCache() )->get( 'users' )['content'] );
	}

	public function test_a_single_user_is_requested_by_id(): void {
		$this->respondWith( $this->response( 200, '{"id":3}' ) );

		$this->assertSame( [ 'id' => 3 ], ( new ApiRepository() )->user( 3 ) );
		$this->assertSame( [ 'https://jsonplaceholder.typicode.com/users/3' ], $this->requests );
	}

	public function test_a_cached_response_is_returned_without_a_request(): void {
		$expires = ( new ApiCache() )->set( 'users', [ [ 'id' => 1 ] ] );
		$this->respondWith( $this->response( 500 ) );
		$api = new ApiRepository();

		$this->assertSame( [ [ 'id' => 1 ] ], $api->users() );
		$this->assertSame( [], $this->requests );
		$this->assertSame( $expires, $api->expiration() );
	}

	public function test_the_request_uses_the_filtered_timeout(): void {
		Filters\expectApplied( 'user_atlas__request_timeout' )->once()->with( 3 )->andReturn( 0 );
		$this->respondWith( $this->response( 200, '[]' ) );

		( new ApiRepository() )->users();

		$this->assertSame( [ 'timeout' => 1 ], $this->lastArgs );
	}

	public function test_an_empty_list_is_a_valid_response(): void {
		$this->respondWith( $this->response( 200, '[]' ) );

		$this->assertSame( [], ( new ApiRepository() )->users() );
	}

	public function test_a_connection_error_answers_502_and_pauses_the_api(): void {
		$this->respondWith( new WP_Error( 'http_request_failed', 'cURL error 28: Operation timed out' ) );

		$this->assertError( ( new ApiRepository() )->users(), 'user_atlas_api_error', 502 );
		$this->assertTrue( ( new ApiCache() )->isPaused() );
	}

	public function test_a_404_answers_404_and_does_not_pause_the_api(): void {
		$this->respondWith( $this->response( 404 ) );

		$this->assertError( ( new ApiRepository() )->user( 99 ), 'user_atlas_not_found', 404 );
		$this->assertFalse( ( new ApiCache() )->isPaused() );
	}

	public function test_a_404_for_the_user_list_answers_502_and_pauses_the_api(): void {
		$this->respondWith( $this->response( 404 ) );

		$this->assertError( ( new ApiRepository() )->users(), 'user_atlas_api_error', 502 );
		$this->assertTrue( ( new ApiCache() )->isPaused() );
	}

	/**
	 * @dataProvider provide_server_failures
	 *
	 * @param int $code HTTP status code.
	 */
	public function test_rate_limits_and_server_errors_answer_502_and_pause_the_api( int $code ): void {
		$this->respondWith( $this->response( $code ) );

		$this->assertError( ( new ApiRepository() )->users(), 'user_atlas_api_error', 502 );
		$this->assertTrue( ( new ApiCache() )->isPaused() );
	}

	/**
	 * Server side failures.
	 *
	 * @return array
	 */
	public function provide_server_failures(): array {
		return [
			'429 Too Many Requests'   => [ 429 ],
			'500 Internal Server'     => [ 500 ],
			'503 Service Unavailable' => [ 503 ],
		];
	}

	/**
	 * @dataProvider provide_client_errors
	 *
	 * @param int $code HTTP status code.
	 */
	public function test_client_errors_answer_502_and_pause_the_api( int $code ): void {
		$this->respondWith( $this->response( $code ) );

		$this->assertError( ( new ApiRepository() )->users(), 'user_atlas_api_error', 502 );
		$this->assertTrue( ( new ApiCache() )->isPaused() );
	}

	/**
	 * Client errors other than 404 and 429. The request URL is fixed, so they
	 * repeat on every request until the API changes.
	 *
	 * @return array
	 */
	public function provide_client_errors(): array {
		return [
			'400 Bad Request'  => [ 400 ],
			'401 Unauthorized' => [ 401 ],
			'403 Forbidden'    => [ 403 ],
		];
	}

	public function test_the_api_is_requested_again_only_after_the_pause(): void {
		$this->respondWith( $this->response( 403 ) );
		$api = new ApiRepository();

		$api->users();
		$this->assertError( $api->users(), 'user_atlas_api_unavailable', 503 );
		$this->assertSame( [ 'https://jsonplaceholder.typicode.com/users' ], $this->requests );
		$this->assertSame( 'Invalid response code: 403', $this->transients[ ApiCache::PAUSE_KEY ]['value'] );
	}

	public function test_an_invalid_body_answers_502_and_pauses_the_api(): void {
		$this->respondWith( $this->response( 200, 'not json' ) );

		$this->assertError( ( new ApiRepository() )->users(), 'user_atlas_api_error', 502 );
		$this->assertTrue( ( new ApiCache() )->isPaused() );
	}

	public function test_a_paused_api_answers_503_without_a_request(): void {
		( new ApiCache() )->pause( 'Timeout' );
		$this->respondWith( $this->response( 200, '[]' ) );

		$this->assertError( ( new ApiRepository() )->users(), 'user_atlas_api_unavailable', 503 );
		$this->assertSame( [], $this->requests );
	}

	public function test_errors_are_not_cached(): void {
		$this->respondWith( $this->response( 500 ) );

		( new ApiRepository() )->users();

		$this->assertNull( ( new ApiCache() )->get( 'users' ) );
	}

	/**
	 * Assert that a result is an error with the given code and status.
	 *
	 * @param mixed  $result Result.
	 * @param string $code   Error code.
	 * @param int    $status HTTP status.
	 *
	 * @return void
	 */
	private function assertError( $result, string $code, int $status ): void {
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( $code, $result->get_error_code() );
		$this->assertSame( [ 'status' => $status ], $result->get_error_data() );
	}
}
