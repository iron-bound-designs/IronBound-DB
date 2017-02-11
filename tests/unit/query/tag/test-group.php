<?php
/**
 * Test the Generic SQL tag.
 *
 * @author      Iron Bound Designs
 * @since       1.2
 * @copyright   2016 (c) Iron Bound Designs.
 * @license     MIT
 */

namespace IronBound\DB\Query\Tag\Tests;

use IronBound\DB\Query\Tag\Group;

/**
 * Class Test_Group
 * @package IronBound\DB\Query\Tag\Tests
 */
class Test_Group extends \IronBound\DB\Tests\TestCase {

	public function test() {

		$group = new Group( 'colA' );
		$group->then( 'colB' );

		$this->assertEquals( 'GROUP BY colA, colB', (string) $group );
	}
}