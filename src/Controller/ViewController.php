<?php
/**
 * ViewController
 *
 * @package WpApp\UserAtlas\Controller
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Controller;

use WpApp\UserAtlas\Service\Page\CheckPage;
use WpApp\UserAtlas\Service\Page\LoadPage;
use WpApp\UserAtlas\Service\Asset\Asset;

defined( 'ABSPATH' ) || die();

/**
 * Class ViewController
 *
 * @package WpApp\UserAtlas\Controller
 */
class ViewController {
	/**
	 * Check if page is correct.
	 *
	 * @var CheckPage $checkPage Check page.
	 */
	private CheckPage $checkPage;

	/**
	 * Allow to load page.
	 *
	 * @var LoadPage $loadPage Load page.
	 */
	private LoadPage $loadPage;

	/**
	 * Allow to enqueue scripts.
	 *
	 * @var Asset $asset Asset.
	 */
	private Asset $asset;

	/**
	 * ViewController constructor.
	 *
	 * @param CheckPage|null $checkPage Check page.
	 * @param LoadPage|null  $loadPage Load page.
	 * @param Asset|null     $asset Asset.
	 *
	 * @return void
	 */
	public function __construct( ?CheckPage $checkPage = null, ?LoadPage $loadPage = null, ?Asset $asset = null ) {
		$this->checkPage = $checkPage ?? new CheckPage();
		$this->loadPage  = $loadPage ?? new LoadPage();
		$this->asset     = $asset ?? new Asset();
	}

	/**
	 * Setup hooks
	 *
	 * @return void
	 */
	public function init(): void {
		add_filter( 'query_vars', [ $this->checkPage, 'addQueryVar' ] );
		add_filter( 'request', [ $this->checkPage, 'matchRequest' ] );
		add_action( 'template_redirect', [ $this, 'setupPage' ], 0 );
	}

	/**
	 * Setup the hooks of the page once the request is parsed
	 *
	 * @return void
	 */
	public function setupPage(): void {
		if ( ! $this->checkPage->isPage() ) {
			return;
		}
		add_action( 'enqueue_embed_scripts', [ $this->asset, 'enqueueScripts' ] );
		add_filter( 'pre_get_document_title', [ $this->loadPage, 'changeTitle' ] );
		$this->loadPage->changeHeader();
		$this->loadPage->changeWpQuery();
		$this->loadPage->loadTemplate();
	}
}
