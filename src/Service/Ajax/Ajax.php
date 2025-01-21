<?php
/**
 * Ajax
 *
 * @package WpApp\UserAtlas\Service\Ajax
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Service\Ajax;

use WpApp\UserAtlas\Helper\JsonTrait;
use WpApp\UserAtlas\Service\Core\Plugin;
use WpApp\UserAtlas\Repository\ApiRepository;

defined( 'ABSPATH' ) || die();

/**
 * Class Ajax
 *
 * @package WpApp\UserAtlas\Service\Ajax
 */
class Ajax {
	use JsonTrait;

	/**
	 * Arguments.
	 *
	 * @var array $args Arguments.
	 */
	private array $args;

	/**
	 * Expected parameters.
	 *
	 * @var array $expectParams Expected parameters.
	 */
	private array $expectParams;

	/**
	 * API instance.
	 *
	 * @var object $api API instance.
	 */
	private object $api;

	/**
	 * Received arguments.
	 *
	 * @var object $receivedArgs Received arguments.
	 */
	private object $receivedArgs;

	/**
	 * ArgsValidator instance.
	 *
	 * @var object $argsValidator ArgsValidator instance.
	 */
	private object $argsValidator;

	/**
	 * ConfigValidator instance.
	 *
	 * @var object $configValidator ConfigValidator instance.
	 */
	private object $configValidator;

	/**
	 * Ajax constructor.
	 *
	 * @param ApiRepository|null   $api ApiRepository instance.
	 * @param Request|null         $receivedArgs Request instance.
	 * @param ArgsValidator|null   $argsValidator ArgsValidator instance.
	 * @param ConfigValidator|null $configValidator ConfigValidator instance.
	 */
	public function __construct(
		ApiRepository $api = null,
		Request $receivedArgs = null,
		ArgsValidator $argsValidator = null,
		ConfigValidator $configValidator = null
	) {
		$path                  = plugin_dir_path( WP_APP_USER_ATLAS_FILE );
		$this->expectParams    = $this->getByKey( $path . 'config.json', 'params' );
		$this->api             = $api ?? new ApiRepository();
		$this->receivedArgs    = $receivedArgs ?? new Request();
		$this->argsValidator   = $argsValidator ?? new ArgsValidator();
		$this->configValidator = $configValidator ?? new ConfigValidator();
	}

	/**
	 * Ajax handler for users list
	 *
	 * @return void
	 */
	public function handler(): void {
		$this->beforeSend();
		$data['content'] = $this->send();
		$this->afterSend( $data );
	}

	/**
	 * Get and validate the data before send it to the API
	 *
	 * @return void
	 */
	public function beforeSend(): void {
		$this->receivedArgs->defineArgs( $this->expectParams );
		$this->args = $this->receivedArgs->result();
		$this->argsValidator->defineArgs( $this->expectParams, $this->args );
		if ( ! $this->argsValidator->validate() ) {
			wp_send_json_error( $this->argsValidator->errors() );
		}

		$this->configValidator->defineArgs( $this->expectParams );
		if ( ! $this->configValidator->validate() ) {
			wp_send_json_error( $this->configValidator->errors() );
		}
	}

	/**
	 * Send data to API
	 *
	 * @throws \Exception Invalid API response.
	 */
	public function send(): array {
		try {
			$cmd         = $this->args['cmd'];
			$params      = array_diff_key( $this->args, [ 'cmd' => '' ] );
			$reflection  = new \ReflectionMethod( $this->api, $cmd );
			$numOfParams = $reflection->getNumberOfParameters();

			if ( empty( $params ) && 0 === $numOfParams ) {
				$data = $this->api->{$cmd}();
			} elseif ( 1 === $numOfParams && count( $params ) === 1 ) {
				$data = $this->api->{$cmd}( array_shift( $params ) );
			} elseif ( count( $params ) !== $numOfParams ) {
				throw new \Exception( 'Invalid number of parameters' );
			}

			if ( empty( $data ) ) {
				throw new \Exception( 'No data found' );
			}
		} catch ( \ReflectionException $error ) {
			$data['status']  = 'error';
			$data['message'] = 'ReflectionException';
		} catch ( \Exception $error ) {
			$data['status']  = 'error';
			$data['message'] = $error->getMessage();
		}

		return $data;
	}

	/**
	 * Handles the response after sending data.
	 *
	 * @param array|null $data Data to be processed.
	 *
	 * @return void
	 */
	public function afterSend( ?array $data ): void {
		if ( empty( $data ) ) {
			wp_send_json_error( 'No data found' );
		}

		if ( ! empty( $data['content']['status'] ) && 'error' === $data['content']['status'] ) {
			$message = $data['content']['message'] ?? 'Error';
			wp_send_json_error( $message );
		}

		wp_send_json_success( $data );
	}
}
