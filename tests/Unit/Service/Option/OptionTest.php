<?php
/**
 * Tests of Option
 *
 * @package WpApp\UserAtlas\Tests
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Tests\Unit\Service\Option;

use WpApp\UserAtlas\Service\Option\Option;
use WpApp\UserAtlas\Tests\Unit\TestCase;

/**
 * @covers \WpApp\UserAtlas\Service\Option\Option
 */
final class OptionTest extends TestCase {
	public function test_saved_values_are_returned_and_missing_ones_fall_back_to_the_default(): void {
		$this->options[ Option::OPTION_NAME ] = [
			'endpoint' => 'team',
			'title'    => '',
		];
		$option                               = new Option();
		$option->init();

		$this->assertSame( 'team', $option->get( 'endpoint', 'user-atlas' ) );
		$this->assertSame( 'User Atlas', $option->get( 'title', 'User Atlas' ) );
		$this->assertSame( '', $option->get( 'missing' ) );
	}

	public function test_no_saved_options_give_an_empty_list(): void {
		$option = new Option();
		$option->init();

		$this->assertSame( [], $option->all() );
	}
}
