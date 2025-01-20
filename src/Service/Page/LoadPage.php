<?php
/**
 * Load Page
 *
 * @package WpApp\UserAtlas\Service\Page
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Service\Page;

use WpApp\UserAtlas\Service\Option\Option;

defined( 'ABSPATH' ) || die();

/**
 * Class LoadPage
 *
 * @package WpApp\UserAtlas\View\Page
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
	public function __construct( Option $option = null ) {
		$this->option = $option ?? Option::instance();
	}

	/**
	 * Change the wp_query
	 *
	 * @return void
	 */
	public function changeWpQuery(): void {
		global $wp_query;
		$wp_query->is_404 = false;
	}

	/**
	 * Change the header
	 *
	 * @return void
	 */
	public function changeHeader(): void {
		status_header( 200 );
	}

	/**
	 * Setup hooks
	 *
	 * @return void
	 */
	public function enqueueScripts(): void {
		do_action( 'wp_enqueue_scripts' );
	}

	/**
	 * Add a title to the page
	 *
	 * @param ?string $title Title.
	 *
	 * @return string
	 *
	 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.Found
	 */
	public function changeTitle( ?string $title = '' ): string {
		return $this->option->option( 'title', 'User Atlas' );
	}

	/**
	 * Load the template
	 *
	 * @return void
	 */
	public function loadTemplate(): void {
		get_header( 'embed' );
		echo '<div id="root"></div>';
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
