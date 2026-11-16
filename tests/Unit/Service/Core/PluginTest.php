<?php
/**
 * Tests of Plugin
 *
 * @package WpApp\UserAtlas\Tests
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Tests\Unit\Service\Core;

use Brain\Monkey\Functions;
use WpApp\UserAtlas\Service\Core\Plugin;
use WpApp\UserAtlas\Tests\Unit\TestCase;

/**
 * @covers \WpApp\UserAtlas\Service\Core\Plugin
 */
final class PluginTest extends TestCase {
	public function test_the_plugin_header_is_read_once(): void {
		Functions\expect( 'get_plugin_data' )
			->once()
			->with( WP_APP_USER_ATLAS_FILE )
			->andReturn(
				[
					'Name'       => 'User Atlas',
					'TextDomain' => 'user-atlas',
				]
			);
		$plugin = new Plugin();

		$plugin->init();
		$plugin->init();

		$this->assertSame( 'User Atlas', $plugin->getName() );
		$this->assertSame( 'user-atlas', $plugin->getSlug() );
		$this->assertSame( basename( WP_APP_USER_ATLAS_FILE ), $plugin->getBasename() );
	}
}
