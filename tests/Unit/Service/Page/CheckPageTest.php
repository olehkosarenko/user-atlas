<?php
/**
 * Tests of CheckPage
 *
 * @package WpApp\UserAtlas\Tests
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Tests\Unit\Service\Page;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WpApp\UserAtlas\Service\Option\Option;
use WpApp\UserAtlas\Service\Page\CheckPage;
use WpApp\UserAtlas\Tests\Unit\TestCase;

/**
 * @covers \WpApp\UserAtlas\Service\Page\CheckPage
 */
final class CheckPageTest extends TestCase {
	/**
	 * Set up URL and sanitizing helpers.
	 *
	 * @return void
	 */
	protected function set_up(): void {
		parent::set_up();

		Functions\when( 'sanitize_title' )->alias(
			static function ( string $title ): string {
				return trim( (string) preg_replace( '/[^a-z0-9]+/', '-', strtolower( $title ) ), '-' );
			}
		);
		Functions\when( 'home_url' )->alias(
			static function ( string $path = '' ): string {
				return 'https://example.com/' . ltrim( $path, '/' );
			}
		);
		Functions\when( 'user_trailingslashit' )->alias(
			static function ( string $path ): string {
				return rtrim( $path, '/' ) . '/';
			}
		);
		Functions\when( 'add_query_arg' )->alias(
			static function ( string $key, string $value, string $url ): string {
				return $url . '?' . $key . '=' . $value;
			}
		);
		$this->options['permalink_structure'] = '/%postname%/';
	}

	/**
	 * Build a CheckPage with the given saved endpoint.
	 *
	 * @param string $endpoint Saved endpoint, empty for none.
	 *
	 * @return CheckPage
	 */
	private function checkPage( string $endpoint = '' ): CheckPage {
		$option = Mockery::mock( Option::class );
		$option->shouldReceive( 'get' )->with( 'endpoint', 'user-atlas' )->andReturn( '' !== $endpoint ? $endpoint : 'user-atlas' );

		return new CheckPage( $option );
	}

	/**
	 * Set the request path parsed by WordPress.
	 *
	 * @param string $request Path relative to the site home.
	 *
	 * @return void
	 */
	private function request( string $request ): void {
		$GLOBALS['wp'] = (object) [ 'request' => $request ];
	}

	/**
	 * Remove the global request.
	 *
	 * @return void
	 */
	protected function tear_down(): void {
		unset( $GLOBALS['wp'] );
		parent::tear_down();
	}

	public function test_the_slug_comes_from_the_settings_and_is_sanitized(): void {
		$this->assertSame( 'our-team', $this->checkPage( 'Our Team' )->getSlug() );
	}

	public function test_the_slug_filter_is_applied_and_its_result_sanitized(): void {
		Filters\expectApplied( 'user_atlas__endpoint' )->once()->with( 'user-atlas' )->andReturn( 'Team Atlas' );

		$this->assertSame( 'team-atlas', $this->checkPage()->getSlug() );
	}

	public function test_an_empty_slug_falls_back_to_the_default(): void {
		Filters\expectApplied( 'user_atlas__endpoint' )->andReturn( '!!!' );

		$this->assertSame( 'user-atlas', $this->checkPage()->getSlug() );
	}

	public function test_the_url_uses_the_slug_with_pretty_permalinks(): void {
		$this->assertSame( 'https://example.com/team/', $this->checkPage( 'team' )->getUrl() );
	}

	public function test_the_url_uses_the_query_var_with_plain_permalinks(): void {
		$this->options['permalink_structure'] = '';

		$this->assertSame( 'https://example.com/?user_atlas_page=1', $this->checkPage( 'team' )->getUrl() );
	}

	public function test_the_query_var_is_registered(): void {
		$this->assertSame( [ 'p', CheckPage::QUERY_VAR ], $this->checkPage()->addQueryVar( [ 'p' ] ) );
	}

	/**
	 * @dataProvider provide_page_requests
	 *
	 * @param string $request Request path.
	 */
	public function test_the_page_address_is_matched( string $request ): void {
		$this->request( $request );

		$this->assertSame( [ CheckPage::QUERY_VAR => '1' ], $this->checkPage()->matchRequest( [ 'pagename' => 'user-atlas' ] ) );
	}

	/**
	 * Requests of the page.
	 *
	 * @return array
	 */
	public function provide_page_requests(): array {
		return [
			'without slash' => [ 'user-atlas' ],
			'with slashes'  => [ '/user-atlas/' ],
		];
	}

	/**
	 * @dataProvider provide_other_requests
	 *
	 * @param string $request Request path.
	 */
	public function test_other_addresses_are_left_alone( string $request ): void {
		$this->request( $request );
		$vars = [ 'pagename' => 'about' ];

		$this->assertSame( $vars, $this->checkPage()->matchRequest( $vars ) );
	}

	/**
	 * Requests of other pages.
	 *
	 * @return array
	 */
	public function provide_other_requests(): array {
		return [
			'home'         => [ '' ],
			'other page'   => [ 'about' ],
			'deeper path'  => [ 'user-atlas/extra' ],
			'longer slug'  => [ 'user-atlas-2' ],
		];
	}

	public function test_is_page_reads_the_query_var(): void {
		Functions\when( 'get_query_var' )->justReturn( '1' );
		$this->assertTrue( $this->checkPage()->isPage() );

		Functions\when( 'get_query_var' )->justReturn( '' );
		$this->assertFalse( $this->checkPage()->isPage() );
	}
}
