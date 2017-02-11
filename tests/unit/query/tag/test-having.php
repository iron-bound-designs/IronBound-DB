<?php
/**
 * Test the Having SQL tag.
 *
 * @author      Iron Bound Designs
 * @since       1.2
 * @copyright   2016 (c) Iron Bound Designs.
 * @license     MIT
 */

namespace IronBound\DB\Query\Tag\Tests;

use IronBound\DB\Query\Tag\Having;
use IronBound\DB\Query\Tag\Where;

/**
 * Class Test_Having
 * @package IronBound\DB\Query\Tag\Tests
 */
class Test_Having extends \IronBound\DB\Tests\TestCase {

	public function test() {

		$having = new Having( new Where( 'col', true, 'val' ) );
		$this->assertEquals( "HAVING col = 'val'", (string) $having );
	}
}