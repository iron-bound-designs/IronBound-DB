<?php
/**
 * Test the From SQL tag.
 *
 * @author      Iron Bound Designs
 * @since       1.2
 * @copyright   2016 (c) Iron Bound Designs.
 * @license     MIT
 */

namespace IronBound\DB\Query\Tag\Tests;

use IronBound\DB\Query\Tag\From;

/**
 * Class Test_From
 * @package IronBound\DB\Query\Tag\Tests
 */
class Test_From extends \IronBound\DB\Tests\TestCase {

	public function test() {

		$from = new From( 'tableA' );
		$from->also( 'tableB', 'tabB' );
		$from->also( 'tableC' );

		$this->assertEquals( "FROM tableA, tableB tabB, tableC", (string) $from );
	}
}