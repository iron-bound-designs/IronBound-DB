<?php
/**
 * Test the Select SQL tag.
 *
 * @author      Iron Bound Designs
 * @since       1.2
 * @copyright   2016 (c) Iron Bound Designs.
 * @license     MIT
 */

namespace IronBound\DB\Query\Tag\Tests;

use IronBound\DB\Query\Tag\Select;

/**
 * Class Test_Select
 * @package IronBound\DB\Query\Tag\Tests
 */
class Test_Select extends \IronBound\DB\Tests\TestCase {

	public function test_all() {

		$select = new Select( Select::ALL );
		$this->assertEquals( "SELECT *", (string) $select );
	}

	public function test_all_as() {

		$select = new Select( Select::ALL, 'as' );
		$this->assertEquals( "SELECT as.*", (string) $select );
	}

	public function test_single_column() {

		$select = new Select( 'column' );
		$this->assertEquals( "SELECT column", (string) $select );
	}

	public function test_multi_column() {

		$select = new Select( 'colA' );
		$select->also( 'colB' );

		$this->assertEquals( "SELECT colA, colB", (string) $select );
	}

	public function test_multi_column_as() {

		$select = new Select( 'columnA', 'colA' );
		$select->also( 'columnB', 'colB' );

		$this->assertEquals( "SELECT columnA AS colA, columnB AS colB", (string) $select );
	}

	public function test_expression() {

		$select = new Select( null );
		$select->expression( 'SUM', 'colA', 'total' );

		$this->assertEquals( "SELECT SUM(colA) AS total", (string) $select );
	}

	public function test_flags() {

		$select = new Select();
		$select->filter_distinct();
		$select->calc_found_rows();

		$this->assertEquals( "SELECT DISTINCT SQL_CALC_FOUND_ROWS *", (string) $select );
	}

}