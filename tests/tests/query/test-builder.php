<?php
/**
 * Test the SQL builder class.
 *
 * @author      Iron Bound Designs
 * @since       1.2
 * @copyright   2016 (c) Iron Bound Designs.
 * @license     MIT
 */

namespace IronBound\DB\Query\Tests;

use IronBound\DB\Query\Builder;
use IronBound\DB\Query\Tag\Generic;

/**
 * Class Test_Builder
 * @package IronBound\DB\Query\Tests
 */
class Test_Builder extends \WP_UnitTestCase {

	public function test_basic() {

		$builder = new Builder();
		$builder->append( new Generic( 'TAG', 'value' ) )->append( new Generic( 'OTHER', 'value' ) );

		$this->assertEquals( 'TAG value OTHER value ' , $builder->build() );
	}
}