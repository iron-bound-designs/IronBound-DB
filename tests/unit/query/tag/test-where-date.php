<?php
/**
 * Test the Where_Date SQL tag.
 *
 * @author      Iron Bound Designs
 * @since       1.2
 * @copyright   2016 (c) Iron Bound Designs.
 * @license     MIT
 */

namespace IronBound\DB\Query\Tag\Tests;

use IronBound\DB\Query\Tag\Where_Date;

/**
 * Class Test_Where_Date
 *
 * @package IronBound\DB\Query\Tag\Tests
 */
class Test_Where_Date extends \IronBound\DB\Tests\TestCase {

	public function test() {

		$date_query = new \WP_Date_Query( array(
			'year' => '2016'
		), 'table.column' );

		$tag = new Where_Date( $date_query );

		// this is a bit fragile because we are relying on the spacing from WordPress
		$this->assertEquals( "WHERE YEAR( table.column ) = 2016", trim( (string) $tag ) );
	}
}