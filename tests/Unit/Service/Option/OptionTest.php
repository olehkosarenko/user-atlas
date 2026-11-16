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
	public function test_a_saved_option_that_is_not_an_array_gives_the_defaults(): void {
		$this->options[ Option::OPTION_NAME ] = 'broken';
		$option                               = new Option();
		$option->init();

		$this->assertSame( 'user-atlas', $option->get( 'endpoint', 'user-atlas' ) );
	}

	public function test_scalar_values_are_returned_as_strings(): void {
		$this->options[ Option::OPTION_NAME ] = [ 'last_update' => 1700000000 ];
		$option                               = new Option();
		$option->init();

		$this->assertSame( '1700000000', $option->get( 'last_update' ) );
	}

	public function test_values_that_are_not_scalar_give_the_default(): void {
		$this->options[ Option::OPTION_NAME ] = [ 'title' => [ 'Team' ] ];
		$option                               = new Option();
		$option->init();

		$this->assertSame( 'User Atlas', $option->get( 'title', 'User Atlas' ) );
	}

	public function test_options_that_were_never_loaded_give_the_default(): void {
		$this->assertSame( 'user-atlas', ( new Option() )->get( 'endpoint', 'user-atlas' ) );
	}
}
