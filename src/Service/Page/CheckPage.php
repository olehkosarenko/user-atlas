<?php
/**
 * PageChecker
 *
 * @package WpApp\UserAtlas\Service\Page
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Service\Page;

use WpApp\UserAtlas\Service\Option\Option;

defined( 'ABSPATH' ) || die();

/**
 * Class CheckPage
 *
 * @package WpApp\UserAtlas\Service\Page
 */
class CheckPage {
	/**
	 * Option instance.
	 *
	 * @var Option
	 */
	private Option $option;
	/**
	 * Page slug.
	 *
	 * @var string
	 */
	private string $slug;

	/**
	 * Request URI.
	 *
	 * @var string
	 */
	private string $request;

	/**
	 * PageChecker constructor.
	 *
	 * @param Option|null $option Option instance.
	 *
	 * @return void
	 */
	public function __construct( ?Option $option = null ) {
		$this->option = $option ?? Option::instance();
		$this->setSlug( $this->option->get( 'endpoint', 'user-atlas' ) );
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Only compared with the page slug, never stored or output.
		$requestUri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$this->changeRequest( (string) $requestUri );
	}

	/**
	 * Set slug
	 *
	 * @param string $slug Slug.
	 *
	 * @return string
	 */
	public function setSlug( string $slug ): string {
		$this->slug = sanitize_title( $slug );
		$this->slug = apply_filters( 'user_atlas__endpoint', $this->slug );

		return $this->slug;
	}

	/**
	 * Set the request path to compare with the page slug
	 *
	 * Keeps the path relative to the site home, so the page also works when
	 * WordPress is installed in a subdirectory.
	 *
	 * @param string $request Request URI.
	 *
	 * @return string Path without leading and trailing slashes, empty if it is not inside the site.
	 */
	public function changeRequest( string $request ): string {
		$this->request = '';

		$path = wp_parse_url( $request, PHP_URL_PATH );
		if ( ! is_string( $path ) ) {
			return $this->request;
		}

		$path = trim( $path, '/' );
		$home = trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
		if ( '' !== $home ) {
			if ( 0 !== strpos( $path . '/', $home . '/' ) ) {
				return $this->request;
			}

			$path = trim( substr( $path, strlen( $home ) ), '/' );
		}

		$this->request = $path;

		return $this->request;
	}

	/**
	 * Check if the current page is the one we want to render
	 *
	 * @return bool
	 */
	public function isPage(): bool {
		return '' !== $this->request && $this->request === $this->slug;
	}
}
