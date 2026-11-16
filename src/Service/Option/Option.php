<?php
/**
 * Options
 *
 * @package WpApp\UserAtlas\Service\Option
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Service\Option;

use WpApp\UserAtlas\Helper\SingletonTrait;

defined( 'ABSPATH' ) || die();

/**
 * Class Options
 */
class Option {
	use SingletonTrait;

	const OPTION_NAME = 'user_atlas__options';

	/**
	 * Options
	 *
	 * @var array
	 */
	private array $options = [];

	/**
	 * Init options
	 *
	 * @return void
	 */
	public function init(): void {
		$options       = get_option( self::OPTION_NAME );
		$this->options = is_array( $options ) ? $options : [];
	}

	/**
	 * Get all options
	 *
	 * @return array
	 */
	public function all(): array {
		return $this->options;
	}

	/**
	 * Get a single option
	 *
	 * @param string $key          Option key.
	 * @param string $defaultValue Default value.
	 *
	 * @return string The saved value, or the default if it is empty or not a scalar.
	 */
	public function get( string $key, string $defaultValue = '' ): string {
		$value = $this->options[ $key ] ?? null;
		if ( is_scalar( $value ) && ! empty( $value ) ) {
			return (string) $value;
		}

		return $defaultValue;
	}
}
