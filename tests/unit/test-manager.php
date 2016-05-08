<?php
/**
 * Test the Manager class.
 *
 * @author      Iron Bound Designs
 * @since       1.2
 * @copyright   2016 (c) Iron Bound Designs.
 * @license     MIT
 */

namespace IronBound\DB\Tests;

use IronBound\DB\Manager;

/**
 * Class Test_Manager
 * @package IronBound\DB\Tests
 */
class Test_Manager extends \WP_UnitTestCase {

	public function test_register() {

		$slug  = uniqid();
		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )
		              ->setMethods( array( 'get_slug', 'get_table_name' ) )
		              ->getMockForAbstractClass();
		$table->method( 'get_slug' )->willReturn( $slug );
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );

		Manager::register( $table );

		$this->assertEquals( $table, Manager::get( $slug ) );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function test_register_throws_exception_for_non_complex_query_subclass() {

		$table = $this->getMockForAbstractClass( 'IronBound\DB\Table\Table' );
		Manager::register( $table, 'stdClass' );
	}

	public function test_make_simple_query_object() {

		$slug  = uniqid();
		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )
		              ->setMethods( array( 'get_slug', 'get_table_name' ) )
		              ->getMockForAbstractClass();
		$table->method( 'get_slug' )->willReturn( $slug );
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );

		Manager::register( $table );

		$this->assertInstanceOf( 'IronBound\DB\Query\Simple_Query', Manager::make_simple_query_object( $slug ) );
	}

	public function test_is_table_installed() {

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->with( "SHOW TABLES LIKE 'wp_table'" )->willReturn( array() );

		$slug  = uniqid();
		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )
		              ->setMethods( array( 'get_slug', 'get_table_name' ) )->getMockForAbstractClass();
		$table->method( 'get_slug' )->willReturn( $slug );
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );

		Manager::register( $table );

		$this->assertFalse( Manager::is_table_installed( $table, $wpdb ) );
	}
}