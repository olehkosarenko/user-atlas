<?php
/**
 * Tests of AdminSetting
 *
 * @package WpApp\UserAtlas\Tests
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Tests\Unit\Service\Admin;

use Brain\Monkey\Functions;
use Mockery;
use RuntimeException;
use WpApp\UserAtlas\Service\Admin\AdminSetting;
use WpApp\UserAtlas\Service\Cache\ApiCache;
use WpApp\UserAtlas\Service\Core\Plugin;
use WpApp\UserAtlas\Service\Option\Option;
use WpApp\UserAtlas\Service\Option\PrepareOption;
use WpApp\UserAtlas\Service\Page\CheckPage;
use WpApp\UserAtlas\Tests\Unit\TestCase;

/**
 * @covers \WpApp\UserAtlas\Service\Admin\AdminSetting
 */
final class AdminSettingTest extends TestCase {
	/**
	 * Plugin double with a fixed slug.
	 *
	 * @return Plugin|Mockery\MockInterface
	 */
	private function plugin() {
		$plugin = Mockery::mock( Plugin::class );
		$plugin->shouldReceive( 'init' );
		$plugin->shouldReceive( 'getSlug' )->andReturn( 'user-atlas' );
		$plugin->shouldReceive( 'getBasename' )->andReturn( 'user-atlas.php' );

		return $plugin;
	}

	public function test_the_endpoint_label_links_to_the_page_url(): void {
		$fields = [];
		Functions\when( 'register_setting' )->justReturn( true );
		Functions\when( 'add_settings_section' )->justReturn( null );
		Functions\when( 'add_settings_field' )->alias(
			static function ( string $id, string $title ) use ( &$fields ): void {
				$fields[ $id ] = $title;
			}
		);
		Functions\when( 'esc_html__' )->returnArg();
		Functions\when( 'esc_url' )->returnArg();
		$checkPage = Mockery::mock( CheckPage::class );
		$checkPage->shouldReceive( 'getUrl' )->once()->andReturn( 'https://example.com/?user_atlas_page=1' );

		( new AdminSetting( $this->plugin(), Mockery::mock( Option::class ), new PrepareOption(), new ApiCache(), $checkPage ) )->fields();

		$this->assertSame( '<a href="https://example.com/?user_atlas_page=1">Endpoint</a>', $fields['endpoint'] );
		$this->assertSame( [ 'endpoint', 'title', 'last_update' ], array_keys( $fields ) );
	}

	public function test_clearing_the_cache_needs_the_manage_options_capability(): void {
		Functions\when( 'current_user_can' )->justReturn( false );
		Functions\when( 'esc_html__' )->returnArg();
		Functions\expect( 'wp_die' )->once()->andThrow( new RuntimeException( 'wp_die' ) );
		Functions\expect( 'check_admin_referer' )->never();
		$cache = new ApiCache();
		$cache->pause( 'Timeout' );

		try {
			( new AdminSetting( $this->plugin(), Mockery::mock( Option::class ), new PrepareOption(), $cache, Mockery::mock( CheckPage::class ) ) )->clearCache();
			$this->fail( 'clearCache() should stop the request.' );
		} catch ( RuntimeException $exception ) {
			$this->assertSame( 'wp_die', $exception->getMessage() );
		}

		$this->assertSame( 1, $cache->version() );
		$this->assertTrue( $cache->isPaused() );
	}
}
