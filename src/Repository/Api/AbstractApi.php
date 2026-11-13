<?php
/**
 * Abstract Api class
 *
 * @package WpApp\UserAtlas\Api
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Repository\Api;

use WpApp\UserAtlas\Service\Cache\ApiCache;

defined( 'ABSPATH' ) || die();

/**
 * Class AbstractApi
 */
abstract class AbstractApi {
	/**
	 * API URL base
	 */
	protected const API_URL = 'https://jsonplaceholder.typicode.com/';

	/**
	 * Default request timeout in seconds
	 */
	protected const REQUEST_TIMEOUT = 3;

	/**
	 * API cache instance.
	 *
	 * @var ApiCache
	 */
	protected ApiCache $cache;

	/**
	 * Unix timestamp when the last returned response expires in the cache, 0 if it is not cached.
	 *
	 * @var int
	 */
	protected int $expiration = 0;

	/**
	 * AbstractApi constructor.
	 *
	 * @param ApiCache|null $cache Cache instance.
	 */
	public function __construct( ?ApiCache $cache = null ) {
		$this->cache = $cache ?? new ApiCache();
	}

	/**
	 * Get when the last returned response expires in the cache
	 *
	 * @return int Unix timestamp, 0 if the response is not cached.
	 */
	public function expiration(): int {
		return $this->expiration;
	}

	/**
	 * Get the request timeout in seconds
	 *
	 * @return int
	 */
	public function timeout(): int {
		/**
		 * Filters the API request timeout.
		 *
		 * @param int $timeout Timeout in seconds. Default 3.
		 */
		$timeout = (int) apply_filters( 'user_atlas__request_timeout', self::REQUEST_TIMEOUT );

		return max( 1, $timeout );
	}

	/**
	 * Send request to API
	 *
	 * @param string $url URL.
	 *
	 * @return array
	 * @throws \Exception Invalid API response.
	 */
	public function send( string $url ): array {
		$this->expiration = 0;

		$cached = $this->cache->get( $url );
		if ( null !== $cached ) {
			$this->expiration = (int) $cached['expires'];

			return $cached['content'];
		}

		$content = [];
		$error   = [];
		try {
			$response = wp_remote_get( self::API_URL . $url, [ 'timeout' => $this->timeout() ] );
			if ( is_wp_error( $response ) ) {
				throw new \Exception( $response->get_error_message() );
			}

			$response_code = wp_remote_retrieve_response_code( $response );
			if ( 200 !== $response_code ) {
				throw new \Exception(
					'Invalid response code: ' . $response_code
				);
			}

			$body = wp_remote_retrieve_body( $response );
			if ( empty( $body ) ) {
				throw new \Exception( 'Empty response body' );
			}

			$content = json_decode( $body, true );
			if ( ! is_array( $content ) ) {
				throw new \Exception( 'Invalid JSON' );
			}
		} catch ( \Exception $err ) {
			$error = [
				'status'  => 'error',
				'message' => $err->getMessage(),
			];
		}

		if ( ! empty( $error ) ) {
			return $error;
		}

		$this->expiration = $this->cache->set( $url, $content );

		return $content;
	}
}
