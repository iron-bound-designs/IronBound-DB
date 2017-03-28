<?php
/**
 * Test the FluentQuery
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Query;

use IronBound\DB\Query\FluentQuery;
use IronBound\DB\Query\Tag\Order;
use IronBound\DB\Query\Tag\Select;
use IronBound\DB\WP\Comments;
use IronBound\DB\WP\Posts;
use IronBound\DB\WP\Users;

/**
 * Class Test_FluentQuery
 *
 * @package IronBound\DB\Tests\Query
 */
class Test_FluentQuery extends \IronBound\DB\Tests\TestCase {

	public function test_select_variadic() {

		$sql = 'SELECT t1.`ID`, t1.`post_author` FROM wp_posts t1';

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->select( 'ID', 'post_author' );
		$fq->results();
	}

	public function test_select_all() {

		$sql = 'SELECT t1.* FROM wp_posts t1';

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->select( Select::ALL );
		$fq->results();
	}

	public function test_select_also() {

		$sql = 'SELECT t1.`ID`, t1.`post_author` FROM wp_posts t1';

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->select( 'ID' );
		$fq->select( 'post_author' );
		$fq->results();
	}

	/**
	 * @expectedException \IronBound\DB\Exception\InvalidColumnException
	 */
	public function test_select_throws_exception_invalid_column() {
		$fq = new FluentQuery( new Posts() );
		$fq->select( 'fake' );
	}

	public function test_select_all_full() {

		$sql = 'SELECT * FROM wp_posts t1';

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->select_all( false );
		$fq->results();
	}

	public function test_distinct() {

		$sql = 'SELECT DISTINCT t1.`ID`, t1.`post_author` FROM wp_posts t1';

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->select( 'ID', 'post_author' );
		$fq->distinct();
		$fq->results();
	}

	public function test_where_simple() {

		$sql = "SELECT t1.* FROM wp_posts t1 WHERE t1.`ID` = '5'";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->where( 'ID', true, 5 );
		$fq->results();
	}

	public function test_where_multiple_columns_as_array_as_only_clause() {

		$sql = "SELECT t1.* FROM wp_posts t1 WHERE ((t1.`ID` = '5') AND (t1.`post_type` = 'page'))";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->where( array( 'ID' => 5, 'post_type' => 'page' ) );
		$fq->results();
	}

	public function test_where_multiple_columns_as_array_as_first_clause() {

		$sql = "SELECT t1.* FROM wp_posts t1 WHERE ((t1.`ID` = '5') AND (t1.`post_type` = 'page')) AND (t1.`post_author` = '1')";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->where( array( 'ID' => 5, 'post_type' => 'page' ) );
		$fq->and_where( 'post_author', '=', 1 );
		$fq->results();
	}

	public function test_where_multiple_columns_as_array_as_middle_clause() {

		$sql = "SELECT t1.* FROM wp_posts t1 WHERE t1.`post_author` = '1' AND ((t1.`ID` = '5') AND (t1.`post_type` = 'page')) AND (t1.`pinged` = '')";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->and_where( 'post_author', '=', 1 );
		$fq->and_where( array( 'ID' => 5, 'post_type' => 'page' ) );
		$fq->and_where( 'pinged', '=', '' );
		$fq->results();
	}

	public function test_where_multiple_columns_as_array_as_last_clause() {

		$sql = "SELECT t1.* FROM wp_posts t1 WHERE t1.`post_author` = '1' AND ((t1.`ID` = '5') AND (t1.`post_type` = 'page'))";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->and_where( 'post_author', '=', 1 );
		$fq->and_where( array( 'ID' => 5, 'post_type' => 'page' ) );
		$fq->results();
	}

	public function test_where_multiple_columns_as_array_as_middle_clause_with_or() {

		$sql = "SELECT t1.* FROM wp_posts t1 WHERE t1.`post_author` = '1' OR ((t1.`ID` = '5') AND (t1.`post_type` = 'page')) AND (t1.`pinged` = '')";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->and_where( 'post_author', '=', 1 );
		$fq->or_where( array( 'ID' => 5, 'post_type' => 'page' ) );
		$fq->and_where( 'pinged', '=', '' );
		$fq->results();
	}

	public function test_where_array_in() {

		$sql = "SELECT t1.* FROM wp_posts t1 WHERE t1.`post_type` IN ('page', 'post')";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->where( 'post_type', '=', array( 'page', 'post' ) );
		$fq->results();
	}

	public function test_where_array_not_in() {

		$sql = "SELECT t1.* FROM wp_posts t1 WHERE t1.`post_type` IN ('page', 'post')";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->where( 'post_type', '!=', array( 'page', 'post' ) );
		$fq->results();
	}

	public function test_where_different_operator() {

		$sql = "SELECT t1.* FROM wp_posts t1 WHERE t1.`ID` > '5'";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->where( 'ID', '>', 5 );
		$fq->results();
	}

	public function test_where_nested_callback() {

		$sql = "SELECT t1.* FROM wp_posts t1 WHERE t1.`ID` = '5' OR (t1.`post_type` = 'page')";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->where( 'ID', '=', 5, function ( FluentQuery $query ) {
			$query->or_where( 'post_type', '=', 'page' );
		} );
		$fq->results();
	}

	public function test_where_nested_method() {
		$sql = "SELECT t1.* FROM wp_posts t1 WHERE t1.`ID` = '5' AND (t1.`post_type` = 'page' OR (t1.`post_author` = '1'))";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->where( 'ID', '=', 5 );
		$fq->add_nested_where( function ( FluentQuery $query ) {
			$query->and_where( 'post_type', '=', 'page' );
			$query->or_where( 'post_author', '=', 1 );
		}, 'and' );
		$fq->results();
	}

	public function test_where_nested_method_boolean() {
		$sql = "SELECT t1.* FROM wp_posts t1 WHERE t1.`ID` = '5' OR (t1.`post_type` = 'page')";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->where( 'ID', '=', 5 );
		$fq->add_nested_where( function ( FluentQuery $query ) {
			$query->and_where( 'post_type', '=', 'page' );
		}, 'or' );
		$fq->results();
	}

	public function test_where_nested_valid_sql_if_no_nests_added() {
		$sql = "SELECT t1.* FROM wp_posts t1 WHERE t1.`ID` = '5'";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->where( 'ID', '=', 5 );
		$fq->add_nested_where( function ( FluentQuery $query ) {
			// do nothing
		}, 'or' );
		$fq->results();
	}

	public function test_where_date() {

		$sql = "SELECT t1.* FROM wp_posts t1 WHERE YEAR( t1.post_date_gmt ) = 2016";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->where_date( array( 'year' => 2016 ), 'post_date_gmt' );
		$fq->results();
	}

	public function test_order_by_asc() {

		$sql = "SELECT t1.* FROM wp_posts t1 ORDER BY t1.`post_date` ASC";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->order_by( 'post_date', Order::ASC );
		$fq->results();
	}

	public function test_order_by_desc() {

		$sql = "SELECT t1.* FROM wp_posts t1 ORDER BY t1.`post_date` DESC";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->order_by( 'post_date', Order::DESC );
		$fq->results();
	}

	public function test_order_by_multiple() {

		$sql = "SELECT t1.* FROM wp_posts t1 ORDER BY t1.`post_date` DESC, t1.`post_title` ASC";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->order_by( 'post_date', Order::DESC );
		$fq->order_by( 'post_title', Order::ASC );
		$fq->results();
	}

	public function test_group_by() {

		$sql = "SELECT t1.* FROM wp_posts t1 GROUP BY t1.`post_date`";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->group_by( 'post_date' );
		$fq->results();
	}

	public function test_group_by_multiple() {

		$sql = "SELECT t1.* FROM wp_posts t1 GROUP BY t1.`post_date`, t1.`post_title`";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->group_by( 'post_date' );
		$fq->group_by( 'post_title' );
		$fq->results();
	}

	public function test_take() {

		$sql = "SELECT t1.* FROM wp_posts t1 LIMIT 5";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->take( 5 );
		$fq->results();
	}

	public function test_offset() {

		$sql = "SELECT t1.* FROM wp_posts t1 LIMIT 1, 5";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql ) );
		$fq->take( 5 );
		$fq->offset( 1 );
		$fq->results();
	}

	public function test_paginate() {

		$sql = "SELECT SQL_CALC_FOUND_ROWS t1.* FROM wp_posts t1 LIMIT 10, 5";

		$fq = new FluentQuery( new Posts(), $this->get_wpdb( $sql, 'SELECT FOUND_ROWS() AS COUNT' ) );
		$fq->paginate( 3, 5 );
		$fq->results();
	}

	public function test_multiple_joins() {

		// This is a nonsensical query, but I just want to make sure that more than one join works.
		$expected = 'SELECT t1.* FROM wp_posts t1 JOIN wp_users t2 ON (t1.`post_author` = t2.`ID` AND (t2.`user_url` != \'\')) ' .
		            'LEFT JOIN wp_comments t3 ON (t1.`post_date_gmt` <= t3.`comment_date_gmt`)';

		$wpdb = $this->get_wpdb( $expected );

		$wpdb->users    = 'wp_users';
		$wpdb->comments = 'wp_comments';

		$fq = new FluentQuery( new Posts(), $wpdb );
		$fq->join( new Users(), 'post_author', 'ID', '=', function ( FluentQuery $query ) {
			$query->where( 'user_url', '!=', '' );
		} );
		$fq->join( new Comments(), 'post_date_gmt', 'comment_date_gmt', '<=', null, 'LEFT' );
		$fq->results();
	}

	public function test_results_no_saver() {

		$wpdb = $this->getMockBuilder( '\wpdb' )->setMethods( array( 'get_results' ) )
		             ->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->with( $this->anything() )->willReturn( array(
			array( 'ID' => 1, 'post_title' => 'Title' ),
			array( 'ID' => 2, 'post_title' => 'Another Title' ),
		) );

		$fq      = new FluentQuery( new Posts(), $wpdb );
		$results = $fq->results();

		$this->assertInstanceOf( '\Doctrine\Common\Collections\ArrayCollection', $results );
		$this->assertEquals( array(
			array( 'ID' => 1, 'post_title' => 'Title' ),
			array( 'ID' => 2, 'post_title' => 'Another Title' )
		), $results->toArray() );
	}

	public function test_results_saver() {

		$wpdb = $this->getMockBuilder( '\wpdb' )->setMethods( array( 'get_results' ) )
		             ->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->with( $this->anything() )->willReturn( array(
			array( 'ID' => 1, 'post_title' => 'Title' ),
			array( 'ID' => 2, 'post_title' => 'Another Title' ),
		) );

		$table = $this->getMockBuilder( '\IronBound\DB\Table\Table' )
		              ->setMethods( array( 'get_table_name' ) )
		              ->getMockForAbstractClass();
		$table->method( 'get_table_name' )->with( $wpdb )->willReturn( 'my_table' );

		$saver = $this->getMockBuilder( '\IronBound\DB\Saver\Saver' )
		              ->setMethods( array( 'make_model', 'get_pk' ) )
		              ->getMockForAbstractClass();
		$saver->method( 'make_model' )->willReturnCallback( function ( $result ) {
			return (object) $result;
		} );
		$saver->method( 'get_pk' )->willReturnCallback( function ( $result ) {
			return $result->ID;
		} );

		$fq      = new FluentQuery( $table, $wpdb );
		$results = $fq->results( $saver );

		$this->assertInstanceOf( '\IronBound\DB\Collection', $results );
		$this->assertFalse( $results->keep_memory( false ) );
		$this->assertEquals( $saver, $results->get_saver() );
		$this->assertEquals( array(
			1 => (object) array( 'ID' => 1, 'post_title' => 'Title' ),
			2 => (object) array( 'ID' => 2, 'post_title' => 'Another Title' )
		), $results->toArray() );
	}

	public function test_select_single_column() {

		$wpdb = $this->getMockBuilder( '\wpdb' )->setMethods( array( 'get_results' ) )
		             ->disableOriginalConstructor()->getMock();
		$wpdb->method( 'get_results' )->with( $this->anything() )->willReturn( array(
			array( 'ID' => 1, 'post_title' => 'Title' ),
			array( 'ID' => 2, 'post_title' => 'Another Title' ),
		) );

		$fq = new FluentQuery( new Posts(), $wpdb );
		$fq->select_single( 'post_title' );

		$results = $fq->results();

		$this->assertInstanceOf( '\Doctrine\Common\Collections\ArrayCollection', $results );
		$this->assertEquals( array(
			1 => 'Title',
			2 => 'Another Title'
		), $results->toArray() );


	}
}