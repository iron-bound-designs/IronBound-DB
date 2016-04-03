<?php
/**
 * Test the Simple Query class.
 *
 * @author      Iron Bound Designs
 * @since       1.2
 * @copyright   2016 (c) Iron Bound Designs.
 * @license     MIT
 */

namespace IronBound\DB\Query\Tests;

use IronBound\DB\Query\Simple_Query;

/**
 * Class Test_Simple_Query
 * @package IronBound\DB\Query\Tests
 */
class Test_Simple_Query extends \WP_UnitTestCase {

	public function test_get() {

		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )->setMethods( array(
			'get_primary_key',
			'get_columns',
			'get_table_name'
		) )->getMockForAbstractClass();
		$table->method( 'get_primary_key' )->willReturn( 'ID' );
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );
		$table->method( 'get_columns' )->willReturn( array( 'ID' => '%d', 'column' => '%s' ) );

		$sql = "SELECT * FROM wp_table WHERE ID = '1'";

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->expects( $this->once() )->method( 'get_row' )->with( $sql );

		$simple_query = new Simple_Query( $wpdb, $table );
		$simple_query->get( 1 );
	}

	public function test_get_by_column() {

		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )->setMethods( array(
			'get_columns',
			'get_table_name'
		) )->getMockForAbstractClass();
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );
		$table->method( 'get_columns' )->willReturn( array( 'ID' => '%d', 'column' => '%s' ) );

		$sql = "SELECT * FROM wp_table WHERE column = 'value'";

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->expects( $this->once() )->method( 'get_row' )->with( $sql );

		$simple_query = new Simple_Query( $wpdb, $table );
		$simple_query->get_by( 'column', 'value' );
	}

	public function test_get_by_retrieve_single_column() {

		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )->setMethods( array(
			'get_columns',
			'get_table_name'
		) )->getMockForAbstractClass();
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );
		$table->method( 'get_columns' )->willReturn( array( 'ID' => '%d', 'column' => '%s' ) );

		$sql = "SELECT ID FROM wp_table WHERE column = 'value'";

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->expects( $this->once() )->method( 'get_row' )->with( $sql );

		$simple_query = new Simple_Query( $wpdb, $table );
		$simple_query->get_by( 'column', 'value', 'ID' );
	}

	public function test_get_by_retrieve_multiple_columns() {

		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )->setMethods( array(
			'get_columns',
			'get_table_name'
		) )->getMockForAbstractClass();
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );
		$table->method( 'get_columns' )->willReturn( array( 'ID' => '%d', 'colA' => '%s', 'colB' => '%s' ) );

		$sql = "SELECT ID, colA FROM wp_table WHERE colB = 'value'";

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->expects( $this->once() )->method( 'get_row' )->with( $sql );

		$simple_query = new Simple_Query( $wpdb, $table );
		$simple_query->get_by( 'colB', 'value', array( 'ID', 'colA' ) );
	}

	/**
	 * @expectedException \IronBound\DB\Exception\InvalidColumnException
	 */
	public function test_exception_thrown_for_invalid_get_by_column() {

		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )->setMethods( array(
			'get_columns',
			'get_table_name'
		) )->getMockForAbstractClass();
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );
		$table->method( 'get_columns' )->willReturn( array( 'ID' => '%d', 'column' => '%s' ) );

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();

		$simple_query = new Simple_Query( $wpdb, $table );
		$simple_query->get_by( 'invalidColumn', 'value' );
	}

	/**
	 * @expectedException \IronBound\DB\Exception\InvalidColumnException
	 */
	public function test_exception_thrown_for_invalid_retrieval_column() {

		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )->setMethods( array(
			'get_columns',
			'get_table_name'
		) )->getMockForAbstractClass();
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );
		$table->method( 'get_columns' )->willReturn( array( 'ID' => '%d', 'column' => '%s' ) );

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();

		$simple_query = new Simple_Query( $wpdb, $table );
		$simple_query->get_by( 'column', 'value', 'invalidColumn' );
	}

	public function test_get_column() {

		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )->setMethods( array(
			'get_primary_key',
			'get_columns',
			'get_table_name'
		) )->getMockForAbstractClass();
		$table->method( 'get_primary_key' )->willReturn( 'ID' );
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );
		$table->method( 'get_columns' )->willReturn( array( 'ID' => '%d', 'column' => '%s' ) );

		$sql = "SELECT column FROM wp_table WHERE ID = '1'";

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->expects( $this->once() )->method( 'get_var' )->with( $sql );

		$simple_query = new Simple_Query( $wpdb, $table );
		$simple_query->get_column( 'column', '1' );
	}

	public function test_get_column_by() {

		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )->setMethods( array(
			'get_columns',
			'get_table_name'
		) )->getMockForAbstractClass();
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );
		$table->method( 'get_columns' )->willReturn( array( 'ID' => '%d', 'colA' => '%s', 'colB' => '%s' ) );

		$sql = "SELECT colA FROM wp_table WHERE colB = 'value'";

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->expects( $this->once() )->method( 'get_var' )->with( $sql );

		$simple_query = new Simple_Query( $wpdb, $table );
		$simple_query->get_column_by( 'colA', 'colB', 'value' );
	}

	/**
	 * @expectedException \IronBound\DB\Exception\InvalidColumnException
	 */
	public function test_exception_thrown_for_invalid_get_column_by_column() {

		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )->setMethods( array(
			'get_columns',
			'get_table_name'
		) )->getMockForAbstractClass();
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );
		$table->method( 'get_columns' )->willReturn( array( 'ID' => '%d', 'column' => '%s' ) );

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();

		$simple_query = new Simple_Query( $wpdb, $table );
		$simple_query->get_column_by( 'invalidColumn', 'column', 'value' );
	}

	public function test_simple_count() {

		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )->setMethods( array(
			'get_columns',
			'get_table_name'
		) )->getMockForAbstractClass();
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );
		$table->method( 'get_columns' )->willReturn( array( 'ID' => '%d', 'colA' => '%s', 'colB' => '%s' ) );

		$sql = "SELECT COUNT(*) FROM wp_table";

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->expects( $this->once() )->method( 'get_var' )->with( $sql );

		$simple_query = new Simple_Query( $wpdb, $table );
		$simple_query->count();
	}

	public function test_count_where() {

		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )->setMethods( array(
			'get_columns',
			'get_table_name'
		) )->getMockForAbstractClass();
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );
		$table->method( 'get_columns' )->willReturn( array( 'ID' => '%d', 'colA' => '%s', 'colB' => '%s' ) );

		$sql = "SELECT COUNT(*) FROM wp_table WHERE colA = 'bob' AND (colB = 'sally')";

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->expects( $this->once() )->method( 'get_var' )->with( $sql );

		$simple_query = new Simple_Query( $wpdb, $table );
		$simple_query->count( array(
			'colA' => 'bob',
			'colB' => 'sally',
		) );
	}

	public function test_insert() {

		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )->setMethods( array(
			'get_columns',
			'get_table_name',
			'get_column_defaults'
		) )->getMockForAbstractClass();
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );
		$table->method( 'get_columns' )->willReturn( array( 'ID' => '%d', 'colA' => '%d', 'colB' => '%s' ) );
		$table->method( 'get_column_defaults' )->willReturn( array( 'ID' => '', 'colA' => '', 'colB' => 'bob' ) );

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->expects( $this->once() )->method( 'insert' )->with( 'wp_table', array(
			'colA' => '5',
			'colB' => 'bob'
		), array(
			'colA' => '%d',
			'colB' => '%s'
		) );

		$simple_query = new Simple_Query( $wpdb, $table );
		$simple_query->insert( array(
			'colA' => '5',
			'colC' => 'george'
		) );
	}

	public function test_update() {

		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )->setMethods( array(
			'get_columns',
			'get_table_name',
			'get_primary_key',
		) )->getMockForAbstractClass();
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );
		$table->method( 'get_primary_key' )->willReturn( 'ID' );
		$table->method( 'get_columns' )->willReturn( array( 'ID' => '%d', 'colA' => '%d', 'colB' => '%s' ) );

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->expects( $this->once() )->method( 'update' )->with( 'wp_table', array(
			'colB' => 'sally',
		), array(
			'ID' => '1'
		) );

		$simple_query = new Simple_Query( $wpdb, $table );
		$simple_query->update( '1', array(
			'colB' => 'sally',
			'colC' => 'george'
		) );
	}

	public function test_update_where() {

		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )->setMethods( array(
			'get_columns',
			'get_table_name',
			'get_primary_key',
		) )->getMockForAbstractClass();
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );
		$table->method( 'get_primary_key' )->willReturn( 'ID' );
		$table->method( 'get_columns' )->willReturn( array( 'ID' => '%d', 'colA' => '%d', 'colB' => '%s' ) );

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->expects( $this->once() )->method( 'update' )->with( 'wp_table', array(
			'colB' => 'sally',
		), array(
			'colA' => 5
		) );

		$simple_query = new Simple_Query( $wpdb, $table );
		$simple_query->update( '1', array(
			'colB' => 'sally',
			'colC' => 'george'
		), array( 'colA' => 5 ) );
	}

	public function test_delete() {

		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )->setMethods( array(
			'get_columns',
			'get_table_name',
			'get_primary_key',
		) )->getMockForAbstractClass();
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );
		$table->method( 'get_primary_key' )->willReturn( 'ID' );
		$table->method( 'get_columns' )->willReturn( array( 'ID' => '%d', 'colA' => '%d', 'colB' => '%s' ) );

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->expects( $this->once() )->method( 'delete' )->with( 'wp_table', array(
			'ID' => '1',
		) );

		$simple_query = new Simple_Query( $wpdb, $table );
		$simple_query->delete( '1' );
	}

	public function test_delete_many() {

		$table = $this->getMockBuilder( 'IronBound\DB\Table\Table' )->setMethods( array(
			'get_columns',
			'get_table_name',
			'get_primary_key',
		) )->getMockForAbstractClass();
		$table->method( 'get_table_name' )->willReturn( 'wp_table' );
		$table->method( 'get_primary_key' )->willReturn( 'ID' );
		$table->method( 'get_columns' )->willReturn( array( 'ID' => '%d', 'colA' => '%d', 'colB' => '%s' ) );

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->expects( $this->once() )->method( 'delete' )->with( 'wp_table', array(
			'colA' => '5',
		) );

		$simple_query = new Simple_Query( $wpdb, $table );
		$simple_query->delete_many( array(
			'colA' => '5'
		) );
	}

}