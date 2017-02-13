<?php
/**
 * Test Case.
 *
 * @since   2.0.0
 * @license MIT
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
			delete_option( $table->get_table_name( $wpdb ) . '_version' );
		}
		$wpdb->suppress_errors( false );

		if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
			$wpdb->queries = array();
		}
	}

	/**
	 * Get a wpdb mock.
	 *
	 * @since 2.0
	 *
	 * @param string $expected
	 *
	 * @return \wpdb
	 */
	protected function get_wpdb( $expected ) {

		$consecutive = array();

		foreach ( func_get_args() as $arg ) {
			$consecutive[] = array( $arg, $this->anything() );
		}

		$wpdb   = $this->getMockBuilder( '\wpdb' )->setMethods( array( 'get_results' ) )
		               ->disableOriginalConstructor()->getMock();
		$method = $wpdb->method( 'get_results' );
		$method = call_user_func_array( array( $method, 'withConsecutive' ), $consecutive );
		$method->willReturn( array() );

		$wpdb->posts = 'wp_posts';

		return $wpdb;
	}
}