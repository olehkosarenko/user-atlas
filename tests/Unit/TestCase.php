<?php
/**
 * Base test case
 *
 * @package WpApp\UserAtlas\Tests
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Yoast\PHPUnitPolyfills\TestCases\TestCase as PolyfillTestCase;

/**
 * Sets up Brain Monkey and keeps WordPress transients and options in memory.
 */
abstract class TestCase extends PolyfillTestCase {
	/**
	 * Transients stored by the code under test.
	 *
	 * @var array
	 */
	protected array $transients = [];

	/**
	 * Options stored by the code under test.
	 *
	 * @var array
	 */
	protected array $options = [];

	/**
	 * Set up Brain Monkey and the in-memory storage.
	 *
	 * @return void
	 */
	protected function set_up(): void {
		parent::set_up();
		Monkey\setUp();

		$this->transients = [];
		$this->options    = [];

		Functions\when( 'get_transient' )->alias(
			function ( string $key ) {
				return $this->transients[ $key ]['value'] ?? false;
			}
		);
		Functions\when( 'set_transient' )->alias(
			function ( string $key, $value, int $expiration = 0 ): bool {
				$this->transients[ $key ] = [
					'value'      => $value,
					'expiration' => $expiration,
				];

				return true;
			}
		);
		Functions\when( 'delete_transient' )->alias(
			function ( string $key ): bool {
				unset( $this->transients[ $key ] );

				return true;
			}
		);
		Functions\when( 'get_option' )->alias(
			function ( string $name, $default = false ) {
				return array_key_exists( $name, $this->options ) ? $this->options[ $name ] : $default;
			}
		);
		Functions\when( 'update_option' )->alias(
			function ( string $name, $value ): bool {
				$this->options[ $name ] = $value;

				return true;
			}
		);
		Functions\when( '__' )->returnArg();
		Functions\when( 'is_wp_error' )->alias(
			static function ( $thing ): bool {
				return $thing instanceof \WP_Error;
			}
		);
	}

	/**
	 * Tear down Brain Monkey.
	 *
	 * @return void
	 */
	protected function tear_down(): void {
		Monkey\tearDown();
		parent::tear_down();
	}
}
