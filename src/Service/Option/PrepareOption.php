<?php
/**
 * Prepare Option
 *
 * @package WpApp\UserAtlas\Service\Option
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Service\Option;

defined( 'ABSPATH' ) || die();

/**
 * Class PrepareOption
 */
class PrepareOption {
	/**
	 * Prepare options before save
	 *
	 * Keeps only the known keys, so a crafted form cannot store extra data.
	 * WordPress unslashes the submitted value before it gets here.
	 *
	 * @param mixed $options Submitted options, not always an array.
	 *
	 * @return array
	 */
	public function sanitize( $options ): array {
		$options = is_array( $options ) ? $options : [];

		return [
			'endpoint'    => sanitize_title( $this->value( $options, 'endpoint' ) ),
			'title'       => sanitize_text_field( $this->value( $options, 'title' ) ),
			'last_update' => (string) absint( $this->value( $options, 'last_update' ) ),
		];
	}

	/**
	 * Get a submitted value as a string
	 *
	 * @param array  $options Submitted options.
	 * @param string $key     Option key.
	 *
	 * @return string Empty if the value is missing or not a scalar.
	 */
	private function value( array $options, string $key ): string {
		return isset( $options[ $key ] ) && is_scalar( $options[ $key ] ) ? (string) $options[ $key ] : '';
	}
}
