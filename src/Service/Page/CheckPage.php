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
	public function __construct( Option $option = null ) {
		$this->option = $option ?? Option::instance();
		$this->setSlug( $this->option->option( 'endpoint', 'user-atlas' ) );
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$this->request = ! empty( $_SERVER['REQUEST_URI'] ) ?
			$this->changeRequest( $_SERVER['REQUEST_URI'] ) : '';
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
	 * Add request
	 *
	 * @param string $request Request.
	 *
	 * @return string
	 */
	public function changeRequest( string $request ): string {
		$request       = explode( '?', $request )[0];
		$parsedUrl     = wp_parse_url( $request );
		$this->request = sanitize_key(
			wp_unslash( $parsedUrl['path'] )
		);

		return $this->request;
	}

	/**
	 * Check if the current page is the one we want to render
	 *
	 * @return bool
	 */
	public function isPage(): bool {
		if ( empty( $this->request ) ) {
			return false;
		}

		if ( $this->request === $this->slug ) {
			return true;
		}

		return false;
	}
}
