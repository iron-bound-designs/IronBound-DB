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
 *
 * @package IronBound\DB\Tests
 */
class Test_Manager extends \IronBound\DB\Tests\TestCase {

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

	public function test_maybe_install_table_uses_creation_sql_if_new_table() {

		/** @var \PHPUnit_Framework_MockObject_MockObject $wpdb */
		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->expects( $this->once() )->method( 'query' )->with( "CREATE TABLE `table_name`" )->willReturn( true );

		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )
		              ->setMethods( array( 'get_table_name', 'get_version', 'get_creation_sql' ) )
		              ->getMockForAbstractClass();
		$table->method( 'get_table_name' )->with( $wpdb )->willReturn( 'table_name' );
		$table->method( 'get_version' )->willReturn( 1 );
		$table->method( 'get_creation_sql' )->with( $wpdb )->willReturn( "CREATE TABLE `table_name`" );

		Manager::maybe_install_table( $table, $wpdb );
	}

	public function test_maybe_drop_table_if_exists() {

		/** @var \PHPUnit_Framework_MockObject_MockObject $wpdb */
		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->with( "SHOW TABLES LIKE 'wp_table'" )->willReturn( array( 'wp_table' ) );
		$wpdb->expects( $this->once() )->method( 'query' )->with( "DROP TABLE IF EXISTS `wp_table`" )->willReturn( true );

		$slug  = uniqid();
		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )
		              ->setMethods( array( 'get_slug', 'get_table_name' ) )
		              ->getMockForAbstractClass();
		$table->method( 'get_slug' )->willReturn( $slug );
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );

		Manager::register( $table );

		$this->assertTrue( Manager::maybe_uninstall_table( $table, $wpdb ) );
	}

	public function test_maybe_empty_table_if_exists() {

		/** @var \PHPUnit_Framework_MockObject_MockObject $wpdb */
		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->with( "SHOW TABLES LIKE 'wp_table'" )->willReturn( array( 'wp_table' ) );
		$wpdb->expects( $this->once() )->method( 'query' )->with( "TRUNCATE TABLE `wp_table`" )->willReturn( true );

		$slug  = uniqid();
		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )
		              ->setMethods( array( 'get_slug', 'get_table_name' ) )
		              ->getMockForAbstractClass();
		$table->method( 'get_slug' )->willReturn( $slug );
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );

		Manager::register( $table );

		$this->assertTrue( Manager::maybe_empty_table( $table, $wpdb ) );
	}

	public function test_maybe_install_table_calls_upgrade_schema_methods_if_existing_table() {

		/** @var \PHPUnit_Framework_MockObject_MockObject $wpdb */
		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->expects( $this->never() )->method( 'query' );

		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )
		              ->setMethods( array(
			              'get_table_name',
			              'get_version',
			              'get_creation_sql',
			              'v1_schema_update',
			              'v2_schema_update',
			              'v4_schema_update',
		              ) )
		              ->getMockForAbstractClass();
		$table->method( 'get_table_name' )->with( $wpdb )->willReturn( 'table_name' );
		$table->method( 'get_version' )->willReturn( 4 );
		$table->method( 'get_creation_sql' )->with( $wpdb )->willReturn( "CREATE TABLE `table_name`" );

		$table->expects( $this->never() )->method( 'v1_schema_update' );
		$table->expects( $this->once() )->method( 'v2_schema_update' )->with( $wpdb, 1 );
		$table->expects( $this->once() )->method( 'v4_schema_update' )->with( $wpdb, 1 );

		update_option( $table->get_table_name( $wpdb ) . '_version', 1 );

		Manager::maybe_install_table( $table, $wpdb );
	}

}
