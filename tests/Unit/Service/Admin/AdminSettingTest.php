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

	public function test_the_fields_are_labelled_for_their_inputs(): void {
		$fields = [];
		Functions\when( 'register_setting' )->justReturn( true );
		Functions\when( 'add_settings_section' )->justReturn( null );
		Functions\when( 'add_settings_field' )->alias(
			static function ( string $id, string $title, $callback, string $page, string $section, array $args = [] ) use ( &$fields ): void {
				$fields[ $id ] = [ $title, $args ];
			}
		);
		Functions\when( 'esc_html__' )->returnArg();

		( new AdminSetting( $this->plugin(), Mockery::mock( Option::class ), new PrepareOption(), new ApiCache(), Mockery::mock( CheckPage::class ) ) )->fields();

		$this->assertSame( [ 'endpoint', 'title', 'last_update' ], array_keys( $fields ) );
		$this->assertSame( [ 'Endpoint', [ 'label_for' => 'user_atlas_endpoint' ] ], $fields['endpoint'] );
		$this->assertSame( [ 'Title', [ 'label_for' => 'user_atlas_title' ] ], $fields['title'] );
		$this->assertSame( [ 'Last Update', [] ], $fields['last_update'] );
	}

	public function test_the_endpoint_description_links_to_the_page_url(): void {
		Functions\when( 'esc_attr' )->returnArg();
		Functions\when( 'esc_url' )->returnArg();
		Functions\when( 'esc_html' )->returnArg();
		Functions\when( 'esc_html_e' )->echoArg();
		$option = Mockery::mock( Option::class );
		$option->shouldReceive( 'get' )->with( 'endpoint' )->andReturn( 'team' );
		$checkPage = Mockery::mock( CheckPage::class );
		$checkPage->shouldReceive( 'getUrl' )->once()->andReturn( 'https://example.com/team/' );

		ob_start();
		( new AdminSetting( $this->plugin(), $option, new PrepareOption(), new ApiCache(), $checkPage ) )->fieldInputEndpoint();
		$output = (string) ob_get_clean();

		$this->assertStringContainsString( 'id="user_atlas_endpoint"', $output );
		$this->assertStringContainsString( 'aria-describedby="user_atlas_endpoint_description"', $output );
		$this->assertStringContainsString( '<p class="description" id="user_atlas_endpoint_description">', $output );
		$this->assertStringContainsString( '<a href="https://example.com/team/">https://example.com/team/</a>', $output );
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

	public function test_the_last_update_is_shown_without_a_form_field(): void {
		Functions\when( 'wp_date' )->alias(
			static function ( string $format, int $timestamp ): string {
				return gmdate( $format, $timestamp );
			}
		);
		Functions\when( 'esc_html' )->returnArg();
		$option = Mockery::mock( Option::class );
		$option->shouldReceive( 'get' )->with( 'last_update' )->andReturn( '1700000000' );

		ob_start();
		( new AdminSetting( $this->plugin(), $option, new PrepareOption(), new ApiCache(), Mockery::mock( CheckPage::class ) ) )->fieldLabelLastUpdate();
		$output = (string) ob_get_clean();

		$this->assertSame( '2023-11-14 22:13:20', trim( $output ) );
	}

	public function test_the_inputs_post_into_the_saved_option(): void {
		Functions\when( 'esc_attr' )->returnArg();
		$option = Mockery::mock( Option::class );
		$option->shouldReceive( 'get' )->with( 'endpoint' )->andReturn( 'team' );
		$option->shouldReceive( 'get' )->with( 'title' )->andReturn( 'Our team' );
		Functions\when( 'esc_url' )->returnArg();
		Functions\when( 'esc_html' )->returnArg();
		Functions\when( 'esc_html_e' )->echoArg();
		$checkPage = Mockery::mock( CheckPage::class );
		$checkPage->shouldReceive( 'getUrl' )->andReturn( 'https://example.com/team/' );
		$setting = new AdminSetting( $this->plugin(), $option, new PrepareOption(), new ApiCache(), $checkPage );

		ob_start();
		$setting->fieldInputEndpoint();
		$setting->fieldInputTitle();
		$output = (string) ob_get_clean();

		$this->assertStringContainsString( 'name="user_atlas__options[endpoint]" value="team"', $output );
		$this->assertStringContainsString( 'name="user_atlas__options[title]" value="Our team"', $output );
		$this->assertStringContainsString( 'id="user_atlas_title"', $output );
	}

	public function test_a_pause_is_shown_with_its_escaped_reason(): void {
		Functions\when( 'esc_html' )->alias(
			static function ( string $text ): string {
				return htmlspecialchars( $text, ENT_QUOTES );
			}
		);
		Functions\when( 'esc_html_e' )->echoArg();
		$cache = new ApiCache();
		$cache->pause( 'Invalid response code: 403 <b>' );
		$setting = new AdminSetting( $this->plugin(), Mockery::mock( Option::class ), new PrepareOption(), $cache, Mockery::mock( CheckPage::class ) );

		ob_start();
		$setting->pauseNotice();
		$output = (string) ob_get_clean();

		$this->assertStringContainsString( 'notice-warning', $output );
		$this->assertStringContainsString( '<code>Invalid response code: 403 &lt;b&gt;</code>', $output );
	}

	public function test_no_pause_notice_is_shown_when_the_api_works(): void {
		$setting = new AdminSetting( $this->plugin(), Mockery::mock( Option::class ), new PrepareOption(), new ApiCache(), Mockery::mock( CheckPage::class ) );

		ob_start();
		$setting->pauseNotice();

		$this->assertSame( '', (string) ob_get_clean() );
	}
}
