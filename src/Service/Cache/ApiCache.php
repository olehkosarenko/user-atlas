<?php
/**
 * API cache
 *
 * @package WpApp\UserAtlas\Service\Cache
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Service\Cache;

defined( 'ABSPATH' ) || die();

/**
 * Class ApiCache
 *
 * Stores successful API responses in transients. The cache version is part of
 * every key, so bumping it makes all stored responses unreachable at once.
 *
 * @package WpApp\UserAtlas\Service\Cache
 */
class ApiCache {
	/**
	 * Option that holds the cache version.
	 *
	 * @var string
	 */
	public const VERSION_OPTION = 'user_atlas__cache_version';

	/**
	 * Transient key prefix.
	 *
	 * @var string
	 */
	private const KEY_PREFIX = 'user_atlas_api_';

	/**
	 * Transient that marks the API as temporarily unavailable.
	 *
	 * @var string
	 */
	public const PAUSE_KEY = 'user_atlas_api_paused';

	/**
	 * Get a cached response
	 *
	 * @param string $path API request path.
	 *
	 * @return array|null Array with `content` and `expires` keys, or null if nothing is cached.
	 */
	public function get( string $path ): ?array {
		$data = get_transient( $this->key( $path ) );

		if ( ! is_array( $data ) || ! isset( $data['content'], $data['expires'] ) ) {
			return null;
		}

		return $data;
	}

	/**
	 * Cache a response
	 *
	 * @param string $path    API request path.
	 * @param array  $content Decoded response.
	 *
	 * @return int Unix timestamp when the cached response expires.
	 */
	public function set( string $path, array $content ): int {
		$ttl     = $this->ttl();
		$expires = time() + $ttl;

		set_transient(
			$this->key( $path ),
			[
				'content' => $content,
				'expires' => $expires,
			],
			$ttl
		);

		return $expires;
	}

	/**
	 * Get the cache lifetime in seconds
	 *
	 * @return int
	 */
	public function ttl(): int {
		/**
		 * Filters how long API responses are cached.
		 *
		 * @param int $ttl Lifetime in seconds. Default one hour.
		 */
		$ttl = (int) apply_filters( 'user_atlas__cache_ttl', HOUR_IN_SECONDS );

		return max( 1, $ttl );
	}

	/**
	 * Check whether API requests are paused after a failure
	 *
	 * @return bool
	 */
	public function isPaused(): bool {
		return false !== get_transient( self::PAUSE_KEY );
	}

	/**
	 * Pause API requests after a failure
	 *
	 * @param string $message Error message of the failed request.
	 *
	 * @return void
	 */
	public function pause( string $message ): void {
		set_transient( self::PAUSE_KEY, $message, $this->pauseDuration() );
	}

	/**
	 * Get how long API requests stay paused after a failure
	 *
	 * @return int
	 */
	public function pauseDuration(): int {
		/**
		 * Filters how long API requests are paused after a failed request.
		 *
		 * @param int $duration Duration in seconds. Default one minute.
		 */
		$duration = (int) apply_filters( 'user_atlas__failure_pause', MINUTE_IN_SECONDS );

		return max( 1, $duration );
	}

	/**
	 * Get the current cache version
	 *
	 * @return int
	 */
	public function version(): int {
		return (int) get_option( self::VERSION_OPTION, 1 );
	}

	/**
	 * Build the transient key for a request path
	 *
	 * @param string $path API request path.
	 *
	 * @return string
	 */
	private function key( string $path ): string {
		return self::KEY_PREFIX . md5( $this->version() . '|' . $path );
	}
}
