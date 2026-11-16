<?php
/**
 * Tests of PrepareOption
 *
 * @package WpApp\UserAtlas\Tests
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Tests\Unit\Service\Option;

use Brain\Monkey\Functions;
use WpApp\UserAtlas\Service\Option\PrepareOption;
use WpApp\UserAtlas\Tests\Unit\TestCase;

/**
 * @covers \WpApp\UserAtlas\Service\Option\PrepareOption
 */
final class PrepareOptionTest extends TestCase {
	/**
	 * Use simple versions of the WordPress sanitizers.
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
		Functions\when( 'sanitize_text_field' )->alias(
			static function ( string $text ): string {
				return trim( strip_tags( $text ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags
			}
		);
	}

	public function test_only_known_keys_are_kept_and_sanitized(): void {
		$result = ( new PrepareOption() )->sanitize(
			[
				'endpoint' => 'Team Atlas!',
				'title'    => ' <b>Our team</b> ',
				'injected' => 'value',
			]
		);

		$this->assertSame( [ 'endpoint', 'title', 'last_update' ], array_keys( $result ) );
		$this->assertSame( 'team-atlas', $result['endpoint'] );
		$this->assertSame( 'Our team', $result['title'] );
	}

	public function test_the_last_update_is_the_time_of_saving_not_a_submitted_value(): void {
		$before = time();
		$result = ( new PrepareOption() )->sanitize( [ 'last_update' => '1700000000' ] );
		$after  = time();

		$this->assertMatchesRegularExpression( '/^\d+$/', $result['last_update'] );
		$this->assertGreaterThanOrEqual( $before, (int) $result['last_update'] );
		$this->assertLessThanOrEqual( $after, (int) $result['last_update'] );
	}

	/**
	 * @dataProvider provide_invalid_input
	 *
	 * @param mixed $input Submitted value.
	 */
	public function test_invalid_input_gives_empty_values( $input ): void {
		$result = ( new PrepareOption() )->sanitize( $input );

		$this->assertSame( '', $result['endpoint'] );
		$this->assertSame( '', $result['title'] );
	}

	/**
	 * Values that are not a settings array.
	 *
	 * @return array
	 */
	public function provide_invalid_input(): array {
		return [
			'null'           => [ null ],
			'string'         => [ 'endpoint=user-atlas' ],
			'nested arrays'  => [
				[
					'endpoint'    => [ 'a' ],
					'title'       => [ 'b' ],
					'last_update' => [ 1 ],
				],
			],
		];
	}
}
