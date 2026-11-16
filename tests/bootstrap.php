<?php
/**
 * Bootstrap of the unit tests
 *
 * The tests run without WordPress: Brain Monkey replaces WordPress functions
 * and the stubs in tests/Stubs stand in for the few WordPress classes the
 * plugin uses.
 *
 * @package WpApp\UserAtlas\Tests
 */

declare( strict_types=1 );

require_once dirname( __DIR__ ) . '/vendor/autoload.php';
require_once dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

define( 'ABSPATH', __DIR__ . '/' );
define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'WP_APP_USER_ATLAS_FILE', dirname( __DIR__ ) . '/user-atlas.php' );

require_once __DIR__ . '/Stubs/wordpress-classes.php';
