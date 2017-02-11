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

use IronBound\DB\Query\Tag\Generic;

/**
 * Class Test_Generic
 * @package IronBound\DB\Query\Tag\Tests
 */
class Test_Generic extends \IronBound\DB\Tests\TestCase {

	public function test() {

		$tag = new Generic( 'tag', 'value' );
		$this->assertEquals( 'TAG value', (string) $tag );
	}
}