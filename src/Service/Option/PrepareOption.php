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
	 * @param array $options Options to sanitize.
	 */
	public function sanitize( array $options ): array {
		foreach ( $options as $key => & $value ) {
			if ( 'endpoint' === $key ) {
				$value = sanitize_title( wp_unslash( $value ) );
				continue;
			}

			$value = sanitize_text_field( wp_unslash( $value ) );
		}

		return $options;
	}
}
