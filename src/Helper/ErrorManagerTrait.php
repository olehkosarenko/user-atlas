<?php
/**
 * Error Manager Trait
 *
 * @package WpApp\UserAtlas\Helper
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Helper;

defined( 'ABSPATH' ) || die();

/**
 * Error Manager Trait
 */
trait ErrorManagerTrait {
	/**
	 * Errors
	 *
	 * @var array
	 */
	protected array $errors = [];

	/**
	 * Status
	 *
	 * @var bool
	 */
	protected bool $status = true;

	/**
	 * Check if there are errors
	 *
	 * @param string $error Error message.
	 *
	 * @return void
	 */
	public function addError( string $error ): void {
		$this->status   = false;
		$this->errors[] = $error;
	}

	/**
	 * Check if there are errors
	 *
	 * @return bool
	 */
	public function errors(): string {
		if ( ! empty( $this->errors ) ) {
			return implode( ', ', $this->errors );
		}

		return '';
	}
}
