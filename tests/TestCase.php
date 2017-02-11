<?php
/**
 * Test Case.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace IronBound\DB\Tests;
use IronBound\DB\Manager;

/**
 * Class TestCase
 *
 * @package IronBound\DB\Tests
 */
abstract class TestCase extends \WP_UnitTestCase {

	/**
	 * @inheritDoc
	 */
	public function tearDown() {
		parent::tearDown();

		global $wpdb;

		$wpdb->suppress_errors();
		foreach ( Manager::all() as $table ) {
			$wpdb->query( "DROP TABLE {$table->get_table_name( $wpdb )}" );
		}
		$wpdb->suppress_errors( false );

		if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
			$wpdb->queries = array();
		}
	}
}