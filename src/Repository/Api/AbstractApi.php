<?php
/**
 * Abstract Api class
 *
 * @package WpApp\UserAtlas\Api
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Repository\Api;

defined( 'ABSPATH' ) || die();

/**
 * Class AbstractApi
 */
abstract class AbstractApi {
	/**
	 * API URL base
	 */
	protected const API_URL = 'https://jsonplaceholder.typicode.com/';

	/**
	 * Send request to API
	 *
	 * @param string $url URL.
	 *
	 * @return array
	 * @throws \Exception Invalid API response.
	 */
	public function send( string $url ): array {
		$content = [];
		$error   = [];
		$url     = self::API_URL . $url;
		try {
			$response = wp_remote_get( $url );
			if ( is_wp_error( $response ) ) {
				throw new \Exception( $response->get_error_message() );
			}

			$response_code = wp_remote_retrieve_response_code( $response );
			if ( 200 !== $response_code ) {
				throw new \Exception(
					'Invalid response code: ' . $response_code
				);
			}

			$body = wp_remote_retrieve_body( $response );
			if ( empty( $body ) ) {
				throw new \Exception( 'Empty response body' );
			}

			$content = json_decode( $body, true );
			if ( empty( $content ) ) {
				throw new \Exception( 'Invalid JSON' );
			}
		} catch ( \Exception $err ) {
			$error = [
				'status'  => 'error',
				'message' => $err->getMessage(),
			];
		}

		if ( ! empty( $error ) ) {
			return $error;
		}

		return $content;
	}
}
