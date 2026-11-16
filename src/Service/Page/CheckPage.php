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
 * Finds out whether the current request is the plugin page. The page slug is
 * read when it is needed, after the theme is loaded, so the user_atlas__endpoint
 * filter can be used from a theme too.
 *
 * @package WpApp\UserAtlas\Service\Page
 */
class CheckPage {
	/**
	 * Query var that marks the plugin page.
	 *
	 * @var string
	 */
	public const QUERY_VAR = 'user_atlas_page';

	/**
	 * Option instance.
	 *
	 * @var Option
	 */
	private Option $option;

	/**
	 * PageChecker constructor.
	 *
	 * @param Option|null $option Option instance.
	 *
	 * @return void
	 */
	public function __construct( ?Option $option = null ) {
		$this->option = $option ?? Option::instance();
	}

	/**
	 * Get the page slug
	 *
	 * @return string Slug from the settings, changed by the user_atlas__endpoint filter.
	 */
	public function getSlug(): string {
		$slug = sanitize_title( $this->option->get( 'endpoint', 'user-atlas' ) );

		/**
		 * Filters the page slug.
		 *
		 * @param string $slug Slug from the plugin settings. Default 'user-atlas'.
		 */
		$slug = sanitize_title( (string) apply_filters( 'user_atlas__endpoint', $slug ) );

		return '' !== $slug ? $slug : 'user-atlas';
	}

	/**
	 * Get the page URL
	 *
	 * @return string The pretty URL, or a URL with the query var when the site uses plain permalinks.
	 */
	public function getUrl(): string {
		if ( '' === (string) get_option( 'permalink_structure' ) ) {
			return add_query_arg( self::QUERY_VAR, '1', home_url( '/' ) );
		}

		return home_url( user_trailingslashit( $this->getSlug() ) );
	}

	/**
	 * Register the query var of the page
	 *
	 * @param array $vars Public query vars.
	 *
	 * @return array
	 */
	public function addQueryVar( array $vars ): array {
		$vars[] = self::QUERY_VAR;

		return $vars;
	}

	/**
	 * Mark the request for the page slug as the plugin page
	 *
	 * WordPress keeps the request path relative to the site home, so this also
	 * works when WordPress is installed in a subdirectory.
	 *
	 * @param array $queryVars Query vars parsed from the request.
	 *
	 * @return array
	 */
	public function matchRequest( array $queryVars ): array {
		global $wp;

		$request = isset( $wp->request ) ? trim( (string) $wp->request, '/' ) : '';
		if ( '' !== $request && $request === $this->getSlug() ) {
			return [ self::QUERY_VAR => '1' ];
		}

		return $queryVars;
	}

	/**
	 * Check if the current page is the one we want to render
	 *
	 * @return bool
	 */
	public function isPage(): bool {
		return '' !== (string) get_query_var( self::QUERY_VAR );
	}
}
