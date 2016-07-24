<?php
/**
 * Test the ForeignUser column.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Unit\Columns;

use IronBound\DB\Table\Column\ForeignUser;

/**
 * Class Test_ForeignUser
 *
 * @package IronBound\DB\Tests\Unit\Columns
 */
class Test_ForeignUser extends \WP_UnitTestCase {

	public function test_convert_raw_to_value() {

		$column = new ForeignUser( 'column' );

		$object_id = $this->factory()->user->create();
		$object    = $column->convert_raw_to_value( $object_id );

		$this->assertInstanceOf( 'WP_User', $object );
		$this->assertEquals( $object_id, $object->ID );
	}

	public function test_prepare_for_storage() {

		$column = new ForeignUser( 'column' );

		$object    = $this->factory()->user->create_and_get();
		$object_id = $column->prepare_for_storage( $object );

		$this->assertInstanceOf( 'WP_User', $object );
		$this->assertEquals( $object_id, $object->ID );
	}

	public function test_delete_callback() {

		$column    = new ForeignUser( 'column' );
		$object_id = $this->factory()->user->create();

		$called = false;
		$column->register_delete_callback( function () use ( &$called ) {
			$called = true;
		} );

		wp_delete_user( $object_id, true );

		$this->assertTrue( $called );
	}
}