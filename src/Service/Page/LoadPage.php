<?php
/**
 * Load Page
 *
 * @package WpApp\UserAtlas\Service\Page
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Service\Page;

use WP_Query;
use WpApp\UserAtlas\Service\Option\Option;

defined( 'ABSPATH' ) || die();

/**
 * Class LoadPage
 *
 * @package WpApp\UserAtlas\Service\Page
 */
class LoadPage {
	/**
	 * Options instance.
	 *
	 * @var Option
	 */
	private Option $option;

	/**
	 * PageManager constructor.
	 *
	 * @param Option|null $option Option instance.
	 *
	 * @return void
	 */
	public function __construct( ?Option $option = null ) {
		$this->option = $option ?? Option::instance();
	}

	/**
	 * Check if a query is the main query of the plugin page
	 *
	 * @param WP_Query $query Query.
	 *
	 * @return bool
	 */
	private function isPageQuery( WP_Query $query ): bool {
		return $query->is_main_query() && '' !== (string) $query->get( CheckPage::QUERY_VAR );
	}

	/**
	 * Mark the main query of the page as a page that is neither the home page nor a 404
	 *
	 * @param WP_Query $query Query.
	 *
	 * @return void
	 */
	public function changeWpQuery( WP_Query $query ): void {
		if ( ! $this->isPageQuery( $query ) ) {
			return;
		}

		$query->is_home     = false;
		$query->is_404      = false;
		$query->is_singular = false;
	}

	/**
	 * Skip the posts query of the page, it shows no posts
	 *
	 * @param array|null $posts Posts returned instead of the database query, null to run it.
	 * @param WP_Query   $query Query.
	 *
	 * @return array|null
	 */
	public function skipPostsQuery( ?array $posts, WP_Query $query ): ?array {
		return $this->isPageQuery( $query ) ? [] : $posts;
	}

	/**
	 * Keep WordPress from turning the page into a 404
	 *
	 * Without posts WordPress would send a 404 status with no-cache headers.
	 *
	 * @param bool     $preempt Whether to skip the default 404 handling.
	 * @param WP_Query $query   Main query.
	 *
	 * @return bool
	 */
	public function skip404( bool $preempt, WP_Query $query ): bool {
		return $this->isPageQuery( $query ) ? true : $preempt;
	}

	/**
	 * Keep WordPress from redirecting the page address to the home page
	 *
	 * @param string|false $redirectUrl URL WordPress would redirect to.
	 *
	 * @return string|false
	 */
	public function skipCanonicalRedirect( $redirectUrl ) {
		global $wp_query;

		return $wp_query instanceof WP_Query && $this->isPageQuery( $wp_query ) ? false : $redirectUrl;
	}

	/**
	 * Set the document title of the page
	 *
	 * @param ?string $title Title.
	 *
	 * @return string
	 */
	public function changeTitle( ?string $title = '' ): string {
		$title = $this->option->get( 'title', 'User Atlas' );

		/**
		 * Filters the page title.
		 *
		 * @param string $title Title from the plugin settings. Default 'User Atlas'.
		 */
		return (string) apply_filters( 'user_atlas__title', $title );
	}

	/**
	 * Load the template
	 *
	 * @return void
	 */
	public function loadTemplate(): void {
		get_header( 'embed' );
		echo '<div id="user-atlas-root"></div>';
		get_footer( 'embed' );
		$this->exit();
	}

	/**
	 * Exit method for phpunit test
	 *
	 * @codeCoverageIgnore
	 *
	 * @return void
	 */
	public function exit(): void {
		// @codeCoverageIgnoreStart
		exit;
		// @codeCoverageIgnoreEnd
	}
}
