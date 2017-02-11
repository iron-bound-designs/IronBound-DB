<?php
/**
 * Test the ForeignPost column.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Unit\Columns;

use IronBound\DB\Table\Column\ForeignPost;

/**
 * Class Test_ForeignPost
 *
 * @package IronBound\DB\Tests\Unit\Columns
 */
class Test_ForeignPost extends \IronBound\DB\Tests\TestCase {

	public function test_convert_raw_to_value() {

		$column = new ForeignPost( 'column' );

		$object_id = $this->factory()->post->create();
		$object    = $column->convert_raw_to_value( $object_id );

		$this->assertInstanceOf( 'WP_Post', $object );
		$this->assertEquals( $object_id, $object->ID );
	}

	public function test_prepare_for_storage() {

		$column = new ForeignPost( 'column' );

		$object    = $this->factory()->post->create_and_get();
		$object_id = $column->prepare_for_storage( $object );

		$this->assertInstanceOf( 'WP_Post', $object );
		$this->assertEquals( $object_id, $object->ID );
	}

	public function test_delete_callback() {

		$column    = new ForeignPost( 'column' );
		$object_id = $this->factory()->post->create();

		$called = false;
		$column->register_delete_callback( function () use ( &$called ) {
			$called = true;
		} );

		wp_delete_post( $object_id, true );

		$this->assertTrue( $called );
	}
}