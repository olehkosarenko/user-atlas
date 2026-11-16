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
	 * Send request to API
	 *
	 * Visitors only get generic messages; the technical reason of a failure is kept with the pause.
	 *
	 * @param string $url        URL.
	 * @param bool   $can_be_404 Whether a 404 is a valid answer for this URL. Only a single resource
	 *                           can be missing: a 404 for a fixed list URL means the API itself changed.
	 *
	 * @return array|WP_Error Decoded response, or an error with the HTTP status to answer with.
	 */
	public function send( string $url, bool $can_be_404 = false ) {
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
		if ( 404 === $code && $can_be_404 ) {
			return new WP_Error(
				'user_atlas_not_found',
				__( 'The requested data was not found.', 'user-atlas' ),
				[ 'status' => 404 ]
			);
		}

		if ( 200 !== $code ) {
			return $this->fail( 'Invalid response code: ' . $code );
		}

		$content = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $content ) ) {
			return $this->fail( 'Invalid response body' );
		}

		$this->expiration = $this->cache->set( $url, $content );

		return $content;
	}

	/**
	 * Build the error for a failed request and pause the API
	 *
	 * The request URLs are fixed, so a failed request would fail the same way for every
	 * visitor; the pause keeps the site from calling the API until it has had time to recover.
	 *
	 * @param string $reason Technical reason, never shown to visitors.
	 *
	 * @return WP_Error
	 */
	private function fail( string $reason ): WP_Error {
		$this->cache->pause( $reason );

		return new WP_Error(
			'user_atlas_api_error',
			__( 'Could not load the data. Please try again later.', 'user-atlas' ),
			[ 'status' => 502 ]
		);
	}
}
