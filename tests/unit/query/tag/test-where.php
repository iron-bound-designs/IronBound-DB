<?php
/**
 * Test the Where SQL tag.
 *
 * @author      Iron Bound Designs
 * @since       1.2
 * @copyright   2016 (c) Iron Bound Designs.
 * @license     MIT
 */

namespace IronBound\DB\Query\Tag\Tests;

use IronBound\DB\Query\Tag\Where;

/**
 * Class Test_Where
 * @package IronBound\DB\Query\Tag\Tests
 */
class Test_Where extends \IronBound\DB\Tests\TestCase {

	public function test_equals() {

		$where = new Where( 'col', true, 'val' );
		$this->assertEquals( "WHERE col = 'val'", (string) $where );
	}

	public function test_not_equals() {

		$where = new Where( 'col', false, 'val' );
		$this->assertEquals( "WHERE col != 'val'", (string) $where );
	}

	public function test_custom_operator() {

		$where = new Where( 'col', '>', 5 );
		$this->assertEquals( "WHERE col > '5'", (string) $where );
	}

	public function test_in() {

		$where = new Where( 'col', true, array( '5', '7' ) );
		$this->assertEquals( "WHERE col IN ('5', '7')", (string) $where );
	}

	public function test_not_in() {

		$where = new Where( 'col', false, array( '5', '7' ) );
		$this->assertEquals( "WHERE col NOT IN ('5', '7')", (string) $where );
	}

	public function test_and() {

		$where = new Where( 'colA', true, 'val1' );
		$where->qAnd( new Where( 'colB', false, 'val2' ) );

		$this->assertEquals( "WHERE colA = 'val1' AND (colB != 'val2')", (string) $where );
	}

	public function test_or() {

		$where = new Where( 'colA', true, 'val1' );
		$where->qOr( new Where( 'colB', false, 'val2' ) );

		$this->assertEquals( "WHERE colA = 'val1' OR (colB != 'val2')", (string) $where );
	}

	public function test_xor() {

		$where = new Where( 'colA', true, 'val1' );
		$where->qXor( new Where( 'colB', false, 'val2' ) );

		$this->assertEquals( "WHERE colA = 'val1' XOR (colB != 'val2')", (string) $where );
	}

	public function test_nesting() {

		$first = new Where( 'colA', true, 'val1' );
		$first->qAnd( $second = new Where( 'colB', true, 'val2' ) );
		$second->qOr( $third = new Where( 'colC', true, 'val3' ) );
		$first->qXor( new Where( 'colD', true, 'val4' ) );

		$this->assertEquals( "WHERE colA = 'val1' AND (colB = 'val2' OR (colC = 'val3')) XOR (colD = 'val4')", (string) $first );
	}

}