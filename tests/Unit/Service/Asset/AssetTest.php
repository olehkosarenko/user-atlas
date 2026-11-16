<?php
/**
 * Tests of Asset
 *
 * @package WpApp\UserAtlas\Tests
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Tests\Unit\Service\Asset;

use Brain\Monkey\Functions;
use Mockery;
use WpApp\UserAtlas\Service\Asset\Asset;
use WpApp\UserAtlas\Service\Option\Option;
use WpApp\UserAtlas\Tests\Unit\TestCase;

/**
 * @covers \WpApp\UserAtlas\Service\Asset\Asset
 */
final class AssetTest extends TestCase {
	/**
	 * Temporary plugin folder with a built asset file.
	 *
	 * @var string
	 */
	private string $pluginDir = '';

	/**
	 * Create a plugin folder with the asset file the build would write.
	 *
	 * @return void
	 */
	protected function set_up(): void {
		parent::set_up();

		$this->pluginDir = sys_get_temp_dir() . '/user-atlas-test-' . uniqid() . '/';
		mkdir( $this->pluginDir . 'assets/build', 0777, true );
		file_put_contents( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			$this->pluginDir . 'assets/build/index.asset.php',
			"<?php return [ 'dependencies' => [], 'version' => '1' ];"
		);
	}

	/**
	 * Remove the temporary plugin folder.
	 *
	 * @return void
	 */
	protected function tear_down(): void {
		unlink( $this->pluginDir . 'assets/build/index.asset.php' );
		rmdir( $this->pluginDir . 'assets/build' );
		rmdir( $this->pluginDir . 'assets' );
		rmdir( $this->pluginDir );

		parent::tear_down();
	}

	public function test_markup_in_the_settings_is_escaped_in_the_inline_script(): void {
		$inline = '';
		Functions\when( 'plugin_dir_path' )->justReturn( $this->pluginDir );
		Functions\when( 'plugin_dir_url' )->justReturn( 'https://example.com/wp-content/plugins/user-atlas/' );
		Functions\when( 'wp_enqueue_script' )->justReturn( null );
		Functions\when( 'current_user_can' )->justReturn( false );
		Functions\when( 'is_user_logged_in' )->justReturn( false );
		Functions\when( 'rest_url' )->justReturn( 'https://example.com/wp-json/user-atlas/v1/' );
		Functions\when( 'esc_url_raw' )->returnArg();
		Functions\when( 'wp_json_encode' )->alias(
			static function ( $data, int $flags = 0 ) {
				return json_encode( $data, $flags ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
			}
		);
		Functions\when( 'wp_add_inline_script' )->alias(
			static function ( string $handle, string $data ) use ( &$inline ): bool {
				$inline = $data;

				return true;
			}
		);
		$option = Mockery::mock( Option::class );
		$option->shouldReceive( 'get' )->with( 'title', 'User Atlas' )->andReturn( '<!-- <script></script> & co' );

		( new Asset( $option ) )->enqueueScripts();

		$this->assertStringNotContainsString( '<', $inline );
		$this->assertStringNotContainsString( '&', $inline );
		$this->assertStringContainsString( '"title":"\u003C!-- \u003Cscript\u003E\u003C/script\u003E \u0026 co"', $inline );
		$this->assertStringContainsString( '"restUrl":"https://example.com/wp-json/user-atlas/v1/"', $inline );
	}
}
