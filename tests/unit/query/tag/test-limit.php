<?php
/**
 * Test the Limit SQL tag.
 *
 * @author      Iron Bound Designs
 * @since       1.2
 * @copyright   2016 (c) Iron Bound Designs.
 * @license     MIT
 */

namespace IronBound\DB\Query\Tag\Tests;

use IronBound\DB\Query\Tag\Limit;

/**
 * Class Test_Limit
 * @package IronBound\DB\Query\Tag\Tests
 */
class Test_Limit extends \IronBound\DB\Tests\TestCase {

	public function test_without_offset() {

		$limit = new Limit( 5 );
		$this->assertEquals( 'LIMIT 5', (string) $limit );
	}

	public function test_with_offset() {

		$limit = new Limit( 5, 3 );
		$this->assertEquals( 'LIMIT 3, 5', (string) $limit );
	}
}