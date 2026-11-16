<?php
/**
 * Tests of LoadPage
 *
 * @package WpApp\UserAtlas\Tests
 */

declare( strict_types=1 );

namespace WpApp\UserAtlas\Tests\Unit\Service\Page;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Query;
use WpApp\UserAtlas\Service\Option\Option;
use WpApp\UserAtlas\Service\Page\CheckPage;
use WpApp\UserAtlas\Service\Page\LoadPage;
use WpApp\UserAtlas\Tests\Unit\TestCase;

/**
 * @covers \WpApp\UserAtlas\Service\Page\LoadPage
 */
final class LoadPageTest extends TestCase {
	/**
	 * Remove the global query.
	 *
	 * @return void
	 */
	protected function tear_down(): void {
		unset( $GLOBALS['wp_query'] );
		parent::tear_down();
	}

	/**
	 * Build a LoadPage with a saved title.
	 *
	 * @param string $title Saved title.
	 *
	 * @return LoadPage
	 */
	private function loadPage( string $title = 'User Atlas' ): LoadPage {
		$option = Mockery::mock( Option::class );
		$option->shouldReceive( 'get' )->with( 'title', 'User Atlas' )->andReturn( $title );

		return new LoadPage( $option );
	}

	/**
	 * The main query of the plugin page.
	 *
	 * @return WP_Query
	 */
	private function pageQuery(): WP_Query {
		return new WP_Query( [ CheckPage::QUERY_VAR => '1' ] );
	}

	public function test_the_page_query_is_neither_home_nor_404(): void {
		$query          = $this->pageQuery();
		$query->is_404  = true;

		$this->loadPage()->changeWpQuery( $query );

		$this->assertFalse( $query->is_home );
		$this->assertFalse( $query->is_404 );
		$this->assertFalse( $query->is_singular );
	}

	public function test_other_queries_are_left_alone(): void {
		$secondary = new WP_Query( [ CheckPage::QUERY_VAR => '1' ], false );
		$other     = new WP_Query( [ 'pagename' => 'about' ] );

		$this->loadPage()->changeWpQuery( $secondary );
		$this->loadPage()->changeWpQuery( $other );

		$this->assertTrue( $secondary->is_home );
		$this->assertTrue( $other->is_home );
	}

	public function test_the_posts_query_is_skipped_only_for_the_page(): void {
		$this->assertSame( [], $this->loadPage()->skipPostsQuery( null, $this->pageQuery() ) );
		$this->assertNull( $this->loadPage()->skipPostsQuery( null, new WP_Query() ) );
	}

	public function test_the_404_handling_is_skipped_only_for_the_page(): void {
		$this->assertTrue( $this->loadPage()->skip404( false, $this->pageQuery() ) );
		$this->assertFalse( $this->loadPage()->skip404( false, new WP_Query() ) );
	}

	public function test_the_canonical_redirect_is_skipped_only_for_the_page(): void {
		$GLOBALS['wp_query'] = $this->pageQuery();
		$this->assertFalse( $this->loadPage()->skipCanonicalRedirect( 'https://example.com/' ) );

		$GLOBALS['wp_query'] = new WP_Query();
		$this->assertSame( 'https://example.com/', $this->loadPage()->skipCanonicalRedirect( 'https://example.com/' ) );
	}

	public function test_the_plugin_template_is_used_for_the_page(): void {
		Functions\when( 'locate_template' )->justReturn( '' );
		$GLOBALS['wp_query'] = $this->pageQuery();

		$template = $this->loadPage()->changeTemplate( '/theme/index.php' );

		$this->assertSame( dirname( WP_APP_USER_ATLAS_FILE ) . '/templates/page.php', $template );
		$this->assertFileExists( $template );
	}

	public function test_a_theme_can_replace_the_template(): void {
		Functions\expect( 'locate_template' )->once()->with( 'user-atlas/page.php' )->andReturn( '/theme/user-atlas/page.php' );
		$GLOBALS['wp_query'] = $this->pageQuery();

		$this->assertSame( '/theme/user-atlas/page.php', $this->loadPage()->changeTemplate( '/theme/index.php' ) );
	}

	public function test_other_pages_keep_their_template(): void {
		$GLOBALS['wp_query'] = new WP_Query();

		$this->assertSame( '/theme/index.php', $this->loadPage()->changeTemplate( '/theme/index.php' ) );
	}

	public function test_the_title_comes_from_the_settings_and_can_be_filtered(): void {
		Filters\expectApplied( 'user_atlas__title' )->once()->with( 'Our team' )->andReturn( 'Team' );

		$this->assertSame( 'Team', $this->loadPage( 'Our team' )->changeTitle( 'Old title' ) );
	}
}
