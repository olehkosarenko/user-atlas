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
	public function __construct( CheckPage $checkPage = null, LoadPage $loadPage = null, Asset $asset = null ) {
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
		if ( ! $this->checkPage->isPage() ) {
			return;
		}
		add_action( 'wp_enqueue_scripts', [ $this->asset, 'enqueueScripts' ] );
		add_filter( 'pre_get_document_title', [ $this->loadPage, 'changeTitle' ] );
		add_action( 'template_redirect', [ $this->loadPage, 'changeHeader' ], 0 );
		add_action( 'template_redirect', [ $this->loadPage, 'changeWpQuery' ], 0 );
		add_action( 'template_redirect', [ $this->loadPage, 'enqueueScripts' ], 0 );
		add_action( 'template_redirect', [ $this->loadPage, 'loadTemplate' ], 0 );
	}
}
