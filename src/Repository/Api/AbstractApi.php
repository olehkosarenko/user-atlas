<?php
/**
 * Abstract Api class
 *
 * @package WpApp\UserAtlas\Repository\Api
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Repository\Api;

use WP_Error;
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
	 * Check whether a response code means the API itself is failing
	 *
	 * A 404 or another client error concerns one resource, so it must not pause the whole API.
	 *
	 * @param int $code HTTP response code.
	 *
	 * @return bool
	 */
	protected function isServerFailure( int $code ): bool {
		return 429 === $code || $code >= 500;
	}

	/**
	 * Send request to API
	 *
	 * Visitors only get generic messages; the technical reason of a failure is kept with the pause.
	 *
	 * @param string $url URL.
	 *
	 * @return array|WP_Error Decoded response, or an error with the HTTP status to answer with.
	 */
	public function send( string $url ) {
		$this->expiration = 0;

		$cached = $this->cache->get( $url );
		if ( null !== $cached ) {
			$this->expiration = (int) $cached['expires'];

			return $cached['content'];
		}

		if ( $this->cache->isPaused() ) {
			return new WP_Error(
				'user_atlas_api_unavailable',
				__( 'The users service is temporarily unavailable. Please try again later.', 'user-atlas' ),
				[ 'status' => 503 ]
			);
		}

		$response = wp_remote_get( self::API_URL . $url, [ 'timeout' => $this->timeout() ] );
		if ( is_wp_error( $response ) ) {
			return $this->fail( $response->get_error_message() );
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( 404 === $code ) {
			return new WP_Error(
				'user_atlas_not_found',
				__( 'The requested data was not found.', 'user-atlas' ),
				[ 'status' => 404 ]
			);
		}

		if ( 200 !== $code ) {
			return $this->fail( 'Invalid response code: ' . $code, $this->isServerFailure( $code ) );
		}

		$content = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $content ) ) {
			return $this->fail( 'Invalid response body' );
		}

		$this->expiration = $this->cache->set( $url, $content );

		return $content;
	}

	/**
	 * Build the error for a failed request
	 *
	 * @param string $reason Technical reason, never shown to visitors.
	 * @param bool   $pause  Whether the failure means the API itself is unavailable.
	 *
	 * @return WP_Error
	 */
	private function fail( string $reason, bool $pause = true ): WP_Error {
		if ( $pause ) {
			$this->cache->pause( $reason );
		}

		return new WP_Error(
			'user_atlas_api_error',
			__( 'Could not load the data. Please try again later.', 'user-atlas' ),
			[ 'status' => 502 ]
		);
	}
}
