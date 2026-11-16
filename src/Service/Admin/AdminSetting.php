<?php
/**
 * AdminSetting
 *
 * @package WpApp\UserAtlas\Service\Admin
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Service\Admin;

use WpApp\UserAtlas\Service\Cache\ApiCache;
use WpApp\UserAtlas\Service\Core\Plugin;
use WpApp\UserAtlas\Service\Option\Option;
use WpApp\UserAtlas\Service\Option\PrepareOption;
use WpApp\UserAtlas\Service\Page\CheckPage;

defined( 'ABSPATH' ) || die();

/**
 * AdminSetting
 *
 * @package WpApp\UserAtlas\Service\Admin
 */
class AdminSetting {
	/**
	 * Plugin
	 *
	 * @var Plugin
	 */
	private Plugin $plugin;

	/**
	 * Option
	 *
	 * @var Option
	 */
	private Option $option;

	/**
	 * PrepareOption
	 *
	 * @var PrepareOption
	 */
	private PrepareOption $prepareOption;

	/**
	 * API cache
	 *
	 * @var ApiCache
	 */
	private ApiCache $cache;

	/**
	 * Hook suffix of the settings page
	 *
	 * @var string
	 */
	private string $pageHook = '';

	/**
	 * CheckPage instance, gives the page URL.
	 *
	 * @var CheckPage
	 */
	private CheckPage $checkPage;

	/**
	 * Settings constructor.
	 *
	 * @param Plugin|null        $plugin Plugin instance.
	 * @param Option|null        $option Option instance.
	 * @param PrepareOption|null $prepareOption PrepareOption instance.
	 * @param ApiCache|null      $cache ApiCache instance.
	 * @param CheckPage|null     $checkPage CheckPage instance.
	 *
	 * @return void
	 */
	public function __construct( ?Plugin $plugin = null, ?Option $option = null, ?PrepareOption $prepareOption = null, ?ApiCache $cache = null, ?CheckPage $checkPage = null ) {
		$this->plugin        = $plugin ?? new Plugin();
		$this->option        = $option ?? Option::instance();
		$this->prepareOption = $prepareOption ?? new PrepareOption();
		$this->cache         = $cache ?? new ApiCache();
		$this->checkPage     = $checkPage ?? new CheckPage( $this->option );
	}

	/**
	 * Add a link in the left sidebar of the admin panel
	 *
	 * @return void
	 */
	public function menu(): void {
		$this->plugin->init();
		$this->pageHook = (string) add_options_page(
			$this->plugin->getName(),
			$this->plugin->getName(),
			'manage_options',
			$this->plugin->getBasename(),
			[ $this, 'page' ]
		);
	}

	/**
	 * Show an options page in the admin panel
	 *
	 * @return void
	 */
	public function page(): void {
		$this->plugin->init();
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'user_atlas__group' );
				do_settings_sections( $this->plugin->getSlug() );
				submit_button( __( 'Save', 'user-atlas' ) );
				?>
			</form>

			<h2><?php esc_html_e( 'API cache', 'user-atlas' ); ?></h2>
			<p><?php esc_html_e( 'API responses are cached. Clear the cache to load fresh data on the next request.', 'user-atlas' ); ?></p>
			<?php $this->pauseNotice(); ?>
			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="user_atlas_clear_cache"/>
				<?php
				wp_nonce_field( 'user_atlas_clear_cache' );
				submit_button( __( 'Clear cache', 'user-atlas' ), 'secondary', 'submit', false );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Show that API requests are paused, with the reason of the failed request
	 *
	 * Visitors only get a generic error message, so this is where administrators see what failed.
	 *
	 * @return void
	 */
	public function pauseNotice(): void {
		$reason = $this->cache->pauseReason();
		if ( null === $reason ) {
			return;
		}
		?>
		<div class="notice notice-warning inline">
			<p>
				<?php esc_html_e( 'API requests are paused after a failed request:', 'user-atlas' ); ?>
				<code><?php echo esc_html( $reason ); ?></code>
			</p>
			<p><?php esc_html_e( 'They resume on their own after the pause. Clear the cache to try again right away.', 'user-atlas' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Clear the API cache from the settings page
	 *
	 * @return void
	 */
	public function clearCache(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to clear the cache.', 'user-atlas' ), 403 );
		}

		check_admin_referer( 'user_atlas_clear_cache' );

		$this->cache->clear();
		$this->plugin->init();

		wp_safe_redirect(
			add_query_arg(
				[
					'page'                     => $this->plugin->getBasename(),
					'user-atlas-cache-cleared' => '1',
				],
				admin_url( 'options-general.php' )
			)
		);
		exit;
	}

	/**
	 * Show a notice after the cache was cleared
	 *
	 * @return void
	 */
	public function cacheClearedNotice(): void {
		$screen = get_current_screen();
		if ( ! $screen || '' === $this->pageHook || $this->pageHook !== $screen->id ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only flag set by our own redirect.
		if ( empty( $_GET['user-atlas-cache-cleared'] ) ) {
			return;
		}
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'API cache cleared.', 'user-atlas' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Add the settings sections and fields to the options page of the admin panel
	 *
	 * @return void
	 */
	public function fields(): void {
		$this->plugin->init();
		register_setting( 'user_atlas__group', Option::OPTION_NAME, [ $this->prepareOption, 'sanitize' ] );
		add_settings_section( 'user_atlas__section_main', esc_html__( 'Options', 'user-atlas' ), '', $this->plugin->getSlug() );

		add_settings_field(
			'endpoint',
			esc_html__( 'Endpoint', 'user-atlas' ),
			[ $this, 'fieldInputEndpoint' ],
			$this->plugin->getSlug(),
			'user_atlas__section_main',
			[ 'label_for' => 'user_atlas_endpoint' ]
		);

		add_settings_field(
			'title',
			esc_html__( 'Title', 'user-atlas' ),
			[ $this, 'fieldInputTitle' ],
			$this->plugin->getSlug(),
			'user_atlas__section_main',
			[ 'label_for' => 'user_atlas_title' ]
		);

		add_settings_field(
			'last_update',
			esc_html__( 'Last Update', 'user-atlas' ),
			[ $this, 'fieldLabelLastUpdate' ],
			$this->plugin->getSlug(),
			'user_atlas__section_main'
		);
	}

	/**
	 * Show input field for endpoint, with a link to the page below it
	 *
	 * @return void
	 */
	public function fieldInputEndpoint(): void {
		$value = $this->option->get( 'endpoint' );
		$url   = $this->checkPage->getUrl();
		?>
		<input type="text" id="user_atlas_endpoint" class="regular-text" name="<?php echo esc_attr( Option::OPTION_NAME ); ?>[endpoint]" value="<?php echo esc_attr( $value ); ?>" aria-describedby="user_atlas_endpoint_description"/>
		<p class="description" id="user_atlas_endpoint_description">
			<?php esc_html_e( 'The page is available at:', 'user-atlas' ); ?>
			<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $url ); ?></a>
		</p>
		<?php
	}

	/**
	 * Show input field for title
	 *
	 * @return void
	 */
	public function fieldInputTitle(): void {
		$value = $this->option->get( 'title' );
		?>
		<input type="text" id="user_atlas_title" class="regular-text" name="<?php echo esc_attr( Option::OPTION_NAME ); ?>[title]" value="<?php echo esc_attr( $value ); ?>"/>
		<?php
	}

	/**
	 * Show when the settings were last saved
	 *
	 * @return void
	 */
	public function fieldLabelLastUpdate(): void {
		$value = (int) $this->option->get( 'last_update' );
		if ( $value > 0 ) {
			echo esc_html( (string) wp_date( 'Y-m-d H:i:s', $value ) );
		}
	}
}
