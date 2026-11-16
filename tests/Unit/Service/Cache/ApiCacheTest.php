<?php
/**
 * Tests of ApiCache
 *
 * @package WpApp\UserAtlas\Tests
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Tests\Unit\Service\Cache;

use Brain\Monkey\Filters;
use WpApp\UserAtlas\Service\Cache\ApiCache;
use WpApp\UserAtlas\Tests\Unit\TestCase;

/**
 * @covers \WpApp\UserAtlas\Service\Cache\ApiCache
 */
final class ApiCacheTest extends TestCase {
	public function test_get_returns_null_when_nothing_is_cached(): void {
		$this->assertNull( ( new ApiCache() )->get( 'users' ) );
	}

	public function test_set_stores_the_response_for_the_cache_lifetime(): void {
		$cache   = new ApiCache();
		$before  = time();
		$expires = $cache->set( 'users', [ [ 'id' => 1 ] ] );

		$this->assertGreaterThanOrEqual( $before + HOUR_IN_SECONDS, $expires );
		$this->assertSame(
			[
				'content' => [ [ 'id' => 1 ] ],
				'expires' => $expires,
			],
			$cache->get( 'users' )
		);

		$stored = array_values( $this->transients );
		$this->assertCount( 1, $stored );
		$this->assertSame( HOUR_IN_SECONDS, $stored[0]['expiration'] );
	}

	public function test_responses_are_cached_per_path(): void {
		$cache = new ApiCache();
		$cache->set( 'users', [ 'list' ] );

		$this->assertNull( $cache->get( 'users/1' ) );
	}

	public function test_get_ignores_malformed_cache_entries(): void {
		$cache = new ApiCache();
		$cache->set( 'users', [ 'list' ] );
		$key = array_key_first( $this->transients );

		$this->transients[ $key ]['value'] = [ 'content' => [ 'list' ] ];

		$this->assertNull( $cache->get( 'users' ) );
	}

	public function test_ttl_can_be_filtered_but_not_below_one_second(): void {
		Filters\expectApplied( 'user_atlas__cache_ttl' )->once()->with( HOUR_IN_SECONDS )->andReturn( 0 );

		$this->assertSame( 1, ( new ApiCache() )->ttl() );
	}

	public function test_pause_marks_the_api_as_unavailable_for_the_pause_duration(): void {
		$cache = new ApiCache();
		$this->assertFalse( $cache->isPaused() );

		$cache->pause( 'Invalid response code: 503' );

		$this->assertTrue( $cache->isPaused() );
		$this->assertSame( 'Invalid response code: 503', $this->transients[ ApiCache::PAUSE_KEY ]['value'] );
		$this->assertSame( MINUTE_IN_SECONDS, $this->transients[ ApiCache::PAUSE_KEY ]['expiration'] );
	}

	public function test_pause_duration_can_be_filtered(): void {
		Filters\expectApplied( 'user_atlas__failure_pause' )->once()->andReturn( 300 );

		$this->assertSame( 300, ( new ApiCache() )->pauseDuration() );
	}

	public function test_clear_makes_cached_responses_unreachable_and_lifts_the_pause(): void {
		$cache = new ApiCache();
		$cache->set( 'users', [ 'list' ] );
		$cache->pause( 'Timeout' );

		$cache->clear();

		$this->assertSame( 2, $cache->version() );
		$this->assertNull( $cache->get( 'users' ) );
		$this->assertFalse( $cache->isPaused() );
	}
}
