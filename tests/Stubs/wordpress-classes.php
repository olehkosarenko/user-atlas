<?php
/**
 * Minimal versions of the WordPress classes the plugin uses
 *
 * They keep only the behaviour the plugin relies on, so the code can be
 * tested without loading WordPress.
 *
 * @package WpApp\UserAtlas\Tests
 */

// phpcs:ignoreFile -- Test doubles of WordPress core classes.

class WP_Error {
	private string $code;
	private string $message;
	private $data;

	public function __construct( string $code = '', string $message = '', $data = '' ) {
		$this->code    = $code;
		$this->message = $message;
		$this->data    = $data;
	}

	public function get_error_code(): string {
		return $this->code;
	}

	public function get_error_message(): string {
		return $this->message;
	}

	public function get_error_data() {
		return $this->data;
	}
}

class WP_Query {
	public bool $is_home     = true;
	public bool $is_404      = false;
	public bool $is_singular = false;
	private array $vars;
	private bool $main;

	public function __construct( array $vars = [], bool $main = true ) {
		$this->vars = $vars;
		$this->main = $main;
	}

	public function get( string $key ) {
		return $this->vars[ $key ] ?? '';
	}

	public function is_main_query(): bool {
		return $this->main;
	}
}

class WP_REST_Server {
	public const READABLE = 'GET';
}

class WP_REST_Request implements ArrayAccess {
	private array $params;

	public function __construct( array $params = [] ) {
		$this->params = $params;
	}

	public function offsetExists( $offset ): bool {
		return isset( $this->params[ $offset ] );
	}

	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return $this->params[ $offset ] ?? null;
	}

	public function offsetSet( $offset, $value ): void {
		$this->params[ $offset ] = $value;
	}

	public function offsetUnset( $offset ): void {
		unset( $this->params[ $offset ] );
	}
}

class WP_REST_Response {
	private $data;

	public function __construct( $data = null ) {
		$this->data = $data;
	}

	public function get_data() {
		return $this->data;
	}
}
