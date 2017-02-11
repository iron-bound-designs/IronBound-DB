<?php
/**
 * Test the ForeignComment column.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Unit\Columns;

use IronBound\DB\Table\Column\ForeignComment;

/**
 * Class Test_ForeignComment
 *
 * @package IronBound\DB\Tests\Unit\Columns
 */
class Test_ForeignComment extends \IronBound\DB\Tests\TestCase {

	public function test_convert_raw_to_value() {

		$column = new ForeignComment( 'column' );

		$object_id = $this->factory()->comment->create();
		$object    = $column->convert_raw_to_value( $object_id );

		$this->assertInstanceOf( 'WP_Comment', $object );
		$this->assertEquals( $object_id, $object->comment_ID );
	}

	public function test_prepare_for_storage() {

		$column = new ForeignComment( 'column' );

		$object    = $this->factory()->comment->create_and_get();
		$object_id = $column->prepare_for_storage( $object );

		$this->assertInstanceOf( 'WP_Comment', $object );
		$this->assertEquals( $object_id, $object->comment_ID );
	}

	public function test_delete_callback() {

		$column    = new ForeignComment( 'column' );
		$object_id = $this->factory()->comment->create();

		$called = false;
		$column->register_delete_callback( function () use ( &$called ) {
			$called = true;
		} );

		wp_delete_comment( $object_id, true );

		$this->assertTrue( $called );
	}
}