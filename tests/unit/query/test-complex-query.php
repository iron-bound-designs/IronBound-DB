<?php
/**
 * Test the Complex Query class.
 *
 * @author      Iron Bound Designs
 * @since       1.2
 * @copyright   2016 (c) Iron Bound Designs.
 * @license     MIT
 */

namespace IronBound\DB\Query\Tests;

use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;

/**
 * Class Test_Complex_Query
 * @package IronBound\DB\Query\Tests
 */
class Test_Complex_Query extends \IronBound\DB\Tests\TestCase {

	public function test_parse_select() {

		$table = $this->getMockForAbstractClass( 'IronBound\DB\Table\Table' );
		$wpdb  = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->willReturn( array() );

		$query = $this->getMockForAbstractClass( 'IronBound\DB\Query\Complex_Query', array(
			$table,
			array(),
			$wpdb
		) );

		$method = new \ReflectionMethod( 'IronBound\DB\Query\Complex_Query', 'parse_select' );
		$method->setAccessible( true );
		$select = $method->invoke( $query );

		$this->assertEquals( "SELECT q.*", (string) $select );
	}

	public function test_parse_select_for_multi_column_return() {

		$table = $this->getMockForAbstractClass( 'IronBound\DB\Table\Table' );
		$wpdb  = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->willReturn( array() );

		$query = $this->getMockForAbstractClass( 'IronBound\DB\Query\Complex_Query', array(
			$table,
			array( 'return_value' => array( 'colA', 'colB' ) ),
			$wpdb
		) );

		$method = new \ReflectionMethod( 'IronBound\DB\Query\Complex_Query', 'parse_select' );
		$method->setAccessible( true );
		$select = $method->invoke( $query );

		$this->assertEquals( "SELECT q.colA, q.colB", (string) $select );
	}

	public function test_parse_select_for_count() {

		$table = $this->getMockForAbstractClass( 'IronBound\DB\Table\Table' );
		$wpdb  = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->willReturn( array() );

		$query = $this->getMockForAbstractClass( 'IronBound\DB\Query\Complex_Query', array(
			$table,
			array( 'return_value' => 'count' ),
			$wpdb
		) );

		$method = new \ReflectionMethod( 'IronBound\DB\Query\Complex_Query', 'parse_select' );
		$method->setAccessible( true );
		$select = $method->invoke( $query );

		$this->assertEquals( "SELECT COUNT(1) AS COUNT", (string) $select );
	}

	public function test_parse_select_for_non_object() {

		$table = $this->getMockForAbstractClass( 'IronBound\DB\Table\Table' );
		$wpdb  = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->willReturn( array() );

		$query = $this->getMockForAbstractClass( 'IronBound\DB\Query\Complex_Query', array(
			$table,
			array( 'return_value' => 'column' ),
			$wpdb
		) );

		$method = new \ReflectionMethod( 'IronBound\DB\Query\Complex_Query', 'parse_select' );
		$method->setAccessible( true );
		$select = $method->invoke( $query );

		$this->assertEquals( "SELECT q.column", (string) $select );
	}

	public function test_in_or_not_in__in_only() {

		$table = $this->getMockForAbstractClass( 'IronBound\DB\Table\Table', array( 'get_columns' ) );
		$table->method( 'get_columns' )->willReturn( array( 'col' => new StringBased( 'VARCHAR', 'col' ) ) );
		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->willReturn( array() );

		$query = $this->getMockForAbstractClass( 'IronBound\DB\Query\Complex_Query', array( $table, array(), $wpdb ) );

		$method = new \ReflectionMethod( 'IronBound\DB\Query\Complex_Query', 'parse_in_or_not_in_query' );
		$method->setAccessible( true );
		$where = $method->invoke( $query, 'col', array( 'a', 'b' ), array() );

		$this->assertEquals( "WHERE q.`col` IN ('a', 'b')", (string) $where );
	}

	public function test_in_or_not_in__not_in_only() {

		$table = $this->getMockForAbstractClass( 'IronBound\DB\Table\Table', array( 'get_columns' ) );
		$table->method( 'get_columns' )->willReturn( array( 'col' => new StringBased( 'VARCHAR', 'col' ) ) );
		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->willReturn( array() );

		$query = $this->getMockForAbstractClass( 'IronBound\DB\Query\Complex_Query', array( $table, array(), $wpdb ) );

		$method = new \ReflectionMethod( 'IronBound\DB\Query\Complex_Query', 'parse_in_or_not_in_query' );
		$method->setAccessible( true );
		$where = $method->invoke( $query, 'col', array(), array( 'a', 'b' ) );

		$this->assertEquals( "WHERE q.`col` NOT IN ('a', 'b')", (string) $where );
	}

	public function test_in_or_not_in__both() {

		$table = $this->getMockForAbstractClass( 'IronBound\DB\Table\Table', array( 'get_columns' ) );
		$table->method( 'get_columns' )->willReturn( array( 'col' => new StringBased( 'VARCHAR', 'col' ) ) );
		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->willReturn( array() );

		$query = $this->getMockForAbstractClass( 'IronBound\DB\Query\Complex_Query', array( $table, array(), $wpdb ) );

		$method = new \ReflectionMethod( 'IronBound\DB\Query\Complex_Query', 'parse_in_or_not_in_query' );
		$method->setAccessible( true );
		$where = $method->invoke( $query, 'col', array( 'a', 'b' ), array( 'c' ) );

		$this->assertEquals( "WHERE q.`col` IN ('a', 'b') AND (q.`col` != 'c')", (string) $where );
	}

	public function test_parse_order_rand() {

		$table = $this->getMockForAbstractClass( 'IronBound\DB\Table\Table' );
		$wpdb  = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->willReturn( array() );

		$query = $this->getMockForAbstractClass( 'IronBound\DB\Query\Complex_Query', array(
			$table,
			array( 'order' => 'rand' ),
			$wpdb
		) );

		$method = new \ReflectionMethod( 'IronBound\DB\Query\Complex_Query', 'parse_order' );
		$method->setAccessible( true );
		$order = $method->invoke( $query );

		$this->assertEquals( "ORDER BY RAND()", (string) $order );
	}

	public function test_parse_order() {

		$table = $this->getMockForAbstractClass( 'IronBound\DB\Table\Table', array( 'get_columns' ) );
		$table->method( 'get_columns' )->willReturn( array(
			'colA' => new StringBased( 'VARCHAR', 'colA' ),
			'colB' => new StringBased( 'VARCHAR', 'colB' )
		) );

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->willReturn( array() );

		$query = $this->getMockForAbstractClass( 'IronBound\DB\Query\Complex_Query', array(
			$table,
			array(
				'order' => array(
					'colA' => 'ASC',
					'colB' => 'DESC'
				)
			),
			$wpdb
		) );

		$method = new \ReflectionMethod( 'IronBound\DB\Query\Complex_Query', 'parse_order' );
		$method->setAccessible( true );
		$order = $method->invoke( $query );

		$this->assertEquals( "ORDER BY q.colA ASC, q.colB DESC", (string) $order );
	}

	public function test_default_parse_order() {

		$table = $this->getMockForAbstractClass( 'IronBound\DB\Table\Table', array(
			'get_columns',
			'get_primary_key'
		) );
		$table->method( 'get_columns' )->willReturn( array(
			'ID'   => new IntegerBased( 'BIGINT', 'ID' ),
			'colA' => new StringBased( 'VARCHAR', 'colA' ),
			'colB' => new StringBased( 'VARCHAR', 'colB' )
		) );
		$table->method( 'get_primary_key' )->willReturn( 'ID' );

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->willReturn( array() );

		$query = $this->getMockForAbstractClass( 'IronBound\DB\Query\Complex_Query', array( $table, array(), $wpdb ) );

		$method = new \ReflectionMethod( 'IronBound\DB\Query\Complex_Query', 'parse_order' );
		$method->setAccessible( true );
		$order = $method->invoke( $query );

		$this->assertEquals( "ORDER BY q.ID ASC", (string) $order );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function test_exception_thrown_if_order_is_not_aray() {

		$table = $this->getMockForAbstractClass( 'IronBound\DB\Table\Table' );
		$wpdb  = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->willReturn( array() );

		$query = $this->getMockForAbstractClass( 'IronBound\DB\Query\Complex_Query', array(
			$table,
			array( 'order' => 'fake' ),
			$wpdb
		) );

		$method = new \ReflectionMethod( 'IronBound\DB\Query\Complex_Query', 'parse_order' );
		$method->setAccessible( true );
		$method->invoke( $query );

	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function test_exception_thrown_if_invalid_direction() {

		$table = $this->getMockForAbstractClass( 'IronBound\DB\Table\Table', array( 'get_columns' ) );
		$table->method( 'get_columns' )->willReturn( array( 'col' => new StringBased( 'VARCHAR', 'col' ) ) );

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->willReturn( array() );

		$query = $this->getMockForAbstractClass( 'IronBound\DB\Query\Complex_Query', array(
			$table,
			array(
				'order' => array(
					'col' => 'fake'
				)
			),
			$wpdb
		) );

		$method = new \ReflectionMethod( 'IronBound\DB\Query\Complex_Query', 'parse_order' );
		$method->setAccessible( true );
		$method->invoke( $query );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function test_exception_thrown_if_invalid_column() {

		$table = $this->getMockForAbstractClass( 'IronBound\DB\Table\Table', array( 'get_columns' ) );
		$table->method( 'get_columns' )->willReturn( array( 'col' => new StringBased( 'VARCHAR', 'col' ) ) );

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->willReturn( array() );

		$query = $this->getMockForAbstractClass( 'IronBound\DB\Query\Complex_Query', array(
			$table,
			array(
				'order' => array(
					'fake' => 'ASC'
				)
			),
			$wpdb
		) );

		$method = new \ReflectionMethod( 'IronBound\DB\Query\Complex_Query', 'parse_order' );
		$method->setAccessible( true );
		$method->invoke( $query );
	}

	public function test_parse_pagination() {

		$table = $this->getMockForAbstractClass( 'IronBound\DB\Table\Table' );

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->willReturn( array() );

		$query = $this->getMockForAbstractClass( 'IronBound\DB\Query\Complex_Query', array(
			$table,
			array(
				'items_per_page' => 5,
				'page'           => 3
			),
			$wpdb
		) );

		$method = new \ReflectionMethod( 'IronBound\DB\Query\Complex_Query', 'parse_pagination' );
		$method->setAccessible( true );
		$limit = $method->invoke( $query );

		$this->assertEquals( "LIMIT 10, 5", (string) $limit );
	}

	public function test_parse_pagination_null_returned_if_items_per_page_is_minus_one() {

		$table = $this->getMockForAbstractClass( 'IronBound\DB\Table\Table' );

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->willReturn( array() );

		$query = $this->getMockForAbstractClass( 'IronBound\DB\Query\Complex_Query', array(
			$table,
			array(
				'items_per_page' => - 1
			),
			$wpdb
		) );

		$method = new \ReflectionMethod( 'IronBound\DB\Query\Complex_Query', 'parse_pagination' );
		$method->setAccessible( true );
		$limit = $method->invoke( $query );

		$this->assertNull( $limit );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function test_parse_pagination_exception_thrown_if_page_less_than_one() {

		$table = $this->getMockForAbstractClass( 'IronBound\DB\Table\Table' );

		$wpdb = $this->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->willReturn( array() );

		$query = $this->getMockForAbstractClass( 'IronBound\DB\Query\Complex_Query', array(
			$table,
			array(
				'items_per_page' => 5,
				'page'           => - 2
			),
			$wpdb
		) );

		$method = new \ReflectionMethod( 'IronBound\DB\Query\Complex_Query', 'parse_pagination' );
		$method->setAccessible( true );
		$method->invoke( $query );
	}
}