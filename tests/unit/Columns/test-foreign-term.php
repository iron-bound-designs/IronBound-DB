<?php
/**
 * Test the ForeignTerm column.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Unit\Columns;

use IronBound\DB\Table\Column\ForeignTerm;

/**
 * Class Test_ForeignTerm
 *
 * @package IronBound\DB\Tests\Unit\Columns
 */
class Test_ForeignTerm extends \IronBound\DB\Tests\TestCase {

	public function test_convert_raw_to_value() {

		$column = new ForeignTerm( 'column' );

		$object_id = $this->factory()->term->create();
		$object    = $column->convert_raw_to_value( $object_id );

		$this->assertInstanceOf( 'WP_Term', $object );
		$this->assertEquals( $object_id, $object->term_id );
	}

	public function test_prepare_for_storage() {

		$column = new ForeignTerm( 'column' );

		$object    = $this->factory()->term->create_and_get();
		$object_id = $column->prepare_for_storage( $object );

		$this->assertInstanceOf( 'WP_Term', $object );
		$this->assertEquals( $object_id, $object->term_id );
	}

	public function test_delete_callback() {

		$column    = new ForeignTerm( 'column' );
		$object_id = $this->factory()->term->create();

		$called = false;
		$column->register_delete_callback( function () use ( &$called ) {
			$called = true;
		} );

		wp_delete_term( $object_id, \WP_UnitTest_Factory_For_Term::DEFAULT_TAXONOMY );

		$this->assertTrue( $called );
	}
}