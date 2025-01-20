<?php
/**
 * ConfigValidator
 *
 * @package WpApp\UserAtlas\Service\Ajax
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Service\Ajax;

use WpApp\UserAtlas\Helper\ErrorManagerTrait;

defined( 'ABSPATH' ) || die();

/**
 * Class ConfigValidator
 *
 * @package WpApp\UserAtlas\Service\Ajax
 */
class ConfigValidator {
	use ErrorManagerTrait;

	/**
	 * Expect queries
	 *
	 * @var array $expectQueries Expect queries.
	 */
	private array $expectQueries;

	/**
	 * Define arguments
	 *
	 * @param array|null $expectQueries Expect queries.
	 */
	public function defineArgs( array $expectQueries = null ): void {
		$this->expectQueries = $expectQueries ?? [];
	}

	/**
	 * Start validation
	 *
	 * @return bool
	 */
	public function validate(): bool {
		$this->validateCmd();
		$this->validateTypes();
		$this->validateDependencies();

		return $this->status;
	}

	/**
	 * Validate cmd
	 *
	 * @return void
	 */
	public function validateCmd(): void {
		if ( empty( $this->expectQueries['cmd'] ) ) {
			$this->addError( 'Missing cmd' );
		}
	}

	/**
	 * Validate types
	 *
	 * @return void
	 */
	public function validateTypes(): void {
		foreach ( $this->expectQueries as $paramName => $paramValue ) {
			if ( empty( $paramValue['type'] ) ) {
				$this->addError( 'Missing type for ' . $paramName );
			} elseif ( ! in_array( $paramValue['type'], [ 'string', 'int' ], true ) ) {
				$this->addError( 'Invalid type for ' . $paramName );
			}
		}
	}

	/**
	 * Validate dependencies
	 *
	 * @return void
	 */
	public function validateDependencies(): void {
		foreach ( $this->expectQueries as $param ) {
			if ( empty( $param['variables'] ) ) {
				continue;
			}

			$this->forEachVariables( $param['variables'] );
		}
	}

	/**
	 * This method is used to iterate over variables
	 * and check if they have dependencies
	 *
	 * @param array $variables Variables.
	 *
	 * @return void
	 */
	public function forEachVariables( array $variables ): void {
		foreach ( $variables as $variable ) {
			if ( empty( $variable['dependencies'] ) ) {
				continue;
			}

			$this->forEachDependencies( $variable['dependencies'] );
		}
	}

	/**
	 * This method is used to iterate over dependencies
	 * and check if they are present in args
	 *
	 * @param array $dependencies Dependencies.
	 *
	 * @return void
	 */
	public function forEachDependencies( array $dependencies ): void {
		foreach ( $dependencies as $dependency ) {
			if ( empty( $this->expectQueries[ $dependency ] ) ) {
				$this->addError( 'Missing dependency ' . $dependency );
			}
		}
	}
}
