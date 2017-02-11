<?php
/**
 * Test the Join SQL tag.
 *
 * @author      Iron Bound Designs
 * @since       1.2
 * @copyright   2016 (c) Iron Bound Designs.
 * @license     MIT
 */

namespace IronBound\DB\Query\Tag\Tests;

use IronBound\DB\Query\Tag\From;
use IronBound\DB\Query\Tag\Join;
use IronBound\DB\Query\Tag\Where;

/**
 * Class Test_Join
 * @package IronBound\DB\Query\Tag\Tests
 */
class Test_Join extends \IronBound\DB\Tests\TestCase {

	public function test() {

		$from  = new From( 'table', 'tab' );
		$where = new Where( 'col', true, 'val' );
		$join  = new Join( $from, $where );

		$this->assertEquals( "JOIN table tab ON (col = 'val')", (string) $join );
	}

}