<?php
/**
 * Request
 *
 * @package WpApp\UserAtlas\Service\Ajax
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Service\Ajax;

defined( 'ABSPATH' ) || die();

/**
 * Class Request
 *
 * @package WpApp\UserAtlas\Service\Ajax
 */
class Request {
	/**
	 * Expected queries
	 *
	 * @var array $expectQueries Expected queries.
	 */
	private array $expectQueries;

	/**
	 * Arguments
	 *
	 * @var array $args Arguments.
	 */
	private array $args;

	/**
	 * Define arguments
	 *
	 * @param array $expectQueries Expected queries.
	 * @param array $args          Arguments.
	 *
	 * @return void
	 */
	public function defineArgs( array $expectQueries = null, array $args = null ): void {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$this->expectQueries = $expectQueries;
		if ( ! empty( $args ) ) {
			$this->args = $args;
		} elseif ( ! empty( $_GET ) ) {
			$this->args = $_GET;
		}
	}

	/**
	 * Start validation
	 *
	 * @return bool
	 */
	public function result(): array {
		if ( empty( $this->args ) ) {
			return [];
		}

		foreach ( $this->args as $key => $value ) {
			if ( empty( $value ) || empty( $this->expectQueries[ $key ]['type'] ) ) {
				continue;
			}

			if ( 'int' === $this->expectQueries[ $key ]['type'] ) {
				$value = intval( $value );
			}

			if ( 'string' === $this->expectQueries[ $key ]['type'] ) {
				$value = sanitize_text_field( wp_unslash( $value ) );
			}

			$args[ $key ] = $value;
		}

		return $args ?? [];
	}
}
