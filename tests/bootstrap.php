<?php
/**
 * Abstract model class for models built upon our DB table.
 *
 * @author      Iron Bound Designs
 * @since       1.2
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     MIT
 */
/**
 * Determine where the WP test suite lives.
 *
 * Support for:
 * 1. `WP_DEVELOP_DIR` environment variable, which points to a checkout
 *   of the develop.svn.wordpress.org repository (this is recommended)
 * 2. `WP_TESTS_DIR` environment variable, which points to a checkout
 * 3. `WP_ROOT_DIR` environment variable, which points to a checkout
 * 4. Plugin installed inside of WordPress.org developer checkout
 * 5. Tests checked out to /tmp
 */
if ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	$test_root = getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit';
} else if ( false !== getenv( 'WP_TESTS_DIR' ) ) {
	$test_root = getenv( 'WP_TESTS_DIR' );
} else if ( false !== getenv( 'WP_ROOT_DIR' ) ) {
	$test_root = getenv( 'WP_ROOT_DIR' ) . '/tests/phpunit';
} else if ( file_exists( '../../../../tests/phpunit/includes/bootstrap.php' ) ) {
	$test_root = '../../../../tests/phpunit';
} else if ( file_exists( '/tmp/wordpress-tests-lib/includes/bootstrap.php' ) ) {
	$test_root = '/tmp/wordpress-tests-lib';
}

if ( getenv( 'SAVEQUERIES' ) && ! defined( 'SAVEQUERIES' ) ) {
	define( 'SAVEQUERIES', true );
}

require_once $test_root . '/includes/functions.php';

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/Stub/model.php';
require_once __DIR__ . '/Stub/table.php';
require_once __DIR__ . '/Stub/builder.php';

require $test_root . '/includes/bootstrap.php';

ini_set( 'xdebug.max_nesting_level', 250 );