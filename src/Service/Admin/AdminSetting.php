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
	 * @var object|Plugin $plugin Plugin instance.
	 */
	private $plugin;

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
	 * Settings constructor.
	 *
	 * @param Plugin|null        $plugin Plugin instance.
	 * @param Option|null        $option Option instance.
	 * @param PrepareOption|null $prepareOption PrepareOption instance.
	 * @param ApiCache|null      $cache ApiCache instance.
	 *
	 * @return void
	 */
	public function __construct( Plugin $plugin = null, Option $option = null, PrepareOption $prepareOption = null, ?ApiCache $cache = null ) {
		$this->plugin        = $plugin ?? new Plugin();
		$this->option        = $option ?? Option::instance();
		$this->prepareOption = $prepareOption ?? new PrepareOption();
		$this->cache         = $cache ?? new ApiCache();
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
				submit_button( 'Save' );
				?>
			</form>

			<h2><?php esc_html_e( 'API cache', 'user-atlas' ); ?></h2>
			<p><?php esc_html_e( 'API responses are cached. Clear the cache to load fresh data on the next request.', 'user-atlas' ); ?></p>
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
		register_setting( 'user_atlas__group', 'user_atlas__options', [ $this->prepareOption, 'sanitize' ] );
		add_settings_section( 'user_atlas__section_main', 'Options', '', $this->plugin->getSlug() );
		$endpoint = $this->option->get( 'endpoint', 'user-atlas' );
		$endpoint = apply_filters( 'user_atlas__endpoint', $endpoint );

		add_settings_field(
			'endpoint',
			'<a href="' . home_url( $endpoint ) . '/">Endpoint</a>',
			[ $this, 'fieldInputEndpoint' ],
			$this->plugin->getSlug(),
			'user_atlas__section_main'
		);

		add_settings_field(
			'title',
			'Title',
			[ $this, 'fieldInputTitle' ],
			$this->plugin->getSlug(),
			'user_atlas__section_main'
		);

		add_settings_field(
			'last_update',
			'Last Update',
			[ $this, 'fieldLabelLastUpdate' ],
			$this->plugin->getSlug(),
			'user_atlas__section_main'
		);
	}

	/**
	 * Show input field for endpoint
	 *
	 * @return void
	 */
	public function fieldInputEndpoint(): void {
		$value = $this->option->get( 'endpoint' );
		?>
		<input type="text" name="user_atlas__options[endpoint]" value="<?php echo esc_attr( $value ); ?>"/>
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
		<input type="text" name="user_atlas__options[title]" value="<?php echo esc_attr( $value ); ?>"/>
		<?php
	}

	/**
	 * Show input field for title
	 *
	 * @return void
	 */
	public function fieldLabelLastUpdate(): void {
		$value = $this->option->get( 'last_update' );
		if ( ! empty( $value ) && $value > 0 ) {
			echo esc_html( gmdate( 'Y-m-d H:i:s', intval( $value ) ) );
		}
		?>
		<input type="hidden" name="user_atlas__options[last_update]" value="<?php echo esc_attr( time() ); ?>"/>
		<?php
	}
}
