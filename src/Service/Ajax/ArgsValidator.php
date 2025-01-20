<?php
/**
 * ArgsValidator
 *
 * @package WpApp\UserAtlas\Service\Ajax
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Service\Ajax;

use WpApp\UserAtlas\Helper\ErrorManagerTrait;

defined( 'ABSPATH' ) || die();

/**
 * Class ArgsValidator
 *
 * @package WpApp\UserAtlas\Service\Ajax
 */
class ArgsValidator {
	use ErrorManagerTrait;

	/**
	 * Expect queries
	 *
	 * @var array $expectQueries Expect queries.
	 */
	private array $expectQueries;

	/**
	 * Request
	 *
	 * @var array $request Request.
	 */
	private array $request;

	/**
	 * Validate arguments
	 *
	 * @var array $args Arguments.
	 */
	private array $args;

	/**
	 * Define arguments
	 *
	 * @param array|null $expectQueries Expect queries.
	 * @param array|null $args Arguments.
	 * @param array|null $request Request.
	 */
	public function defineArgs(
		array $expectQueries = null,
		array $args = null,
		array $request = null
	) {
		//phpcs:disable WordPress.Security.NonceVerification.Recommended
		$this->expectQueries = $expectQueries ?? [];
		$this->args          = $args ?? [];
		if ( ! empty( $request ) ) {
			$this->request = $request;
		} elseif ( ! empty( $_GET ) ) {
			$this->request = $_GET;
		}
	}

	/**
	 * Start validation
	 *
	 * @return bool
	 */
	public function validate(): bool {
		$this->validateNonce();
		$this->validateParams();

		return $this->status;
	}

	/**
	 * Validate nonce
	 *
	 * @return void
	 */
	public function validateNonce(): void {
		$nonce = ! empty( $this->request['nonce'] ) ?
			sanitize_key( wp_unslash( $this->request['nonce'] ) ) : '';
		if ( empty( $nonce ) ) {
			$this->addError( 'Empty nonce' );
		}
		if ( ! wp_verify_nonce( $nonce, 'user_atlas-nonce' ) ) {
			$this->addError( 'Invalid nonce' );
		}
	}

	/**
	 * Validate prepared args using expectQueries
	 *
	 * @return void
	 */
	public function validateParams(): void {
		foreach ( $this->expectQueries as $paramName => $paramValue ) {
			if ( ! in_array( $paramName, array_keys( $this->args ), true ) ) {
				continue;
			}

			if ( 'string' === $paramValue['type'] ) {
				$this->paramString( $paramName );
			}

			if ( 'int' === $paramValue['type'] ) {
				$this->paramInt( $paramName );
			}
		}
	}

	/**
	 * Validate string param
	 *
	 * @param string $paramName Param name.
	 */
	public function paramString( string $paramName ): void {
		if ( empty( $this->args[ $paramName ] ) ) {
			$this->addError( 'Empty string param ' . $paramName );
		} elseif (
			! empty( $this->expectQueries[ $paramName ]['variables'] )
			&& ! in_array(
				$this->args[ $paramName ],
				array_keys( $this->expectQueries[ $paramName ]['variables'] ),
				true
			)
		) {
			$keys         = array_keys( $this->expectQueries[ $paramName ]['variables'] );
			$expectValues = implode( ', ', $keys );
			$error        = 'Invalid string value for variable ' . $paramName;
			$error       .= ' (must be one of ' . $expectValues . ')';
			$this->addError( $error );
		}
	}

	/**
	 * Validate integer param
	 *
	 * @param string $paramName Param name.
	 *
	 * @return void
	 */
	public function paramInt( string $paramName ): void {
		if ( empty( $this->request[ $paramName ] ) && empty( $this->args[ $paramName ] ) ) {
			$this->addError( 'Empty int param ' . $paramName );
		} elseif ( ! is_int( $this->args[ $paramName ] ) || ! is_numeric( $this->request[ $paramName ] ) ) {
			$this->addError( 'Invalid int param ' . $paramName . ' (must be an integer)' );
		} elseif ( $this->args[ $paramName ] < 1 ) {
			$this->addError( 'Invalid int param ' . $paramName . ' (must be greater than 0)' );
		}
	}
}
