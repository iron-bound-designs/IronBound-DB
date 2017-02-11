<?php
/**
 * Test the Boolean column.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Unit\Columns;

use IronBound\DB\Table\Column\Boolean;

class Test_Boolean extends \IronBound\DB\Tests\TestCase {

	public function test_convert_raw_to_value_casts_to_bool() {

		$column = new Boolean( 'active' );
		$value  = $column->convert_raw_to_value( '1' );

		$this->assertInternalType( 'boolean', $value );
		$this->assertTrue( $value );

		$value  = $column->convert_raw_to_value( '0' );

		$this->assertInternalType( 'boolean', $value );
		$this->assertFalse( $value );
	}

	public function test_prepare_for_storage_passes_null_through() {

		$column = new Boolean( 'active' );
		$this->assertNull( $column->prepare_for_storage( null ) );
	}

	/**
	 * @expectedException \IronBound\DB\Exception\InvalidDataForColumnException
	 */
	public function test_exception_thrown_if_non_scalar_value_given() {

		$column = new Boolean( 'active' );
		$column->prepare_for_storage( new \stdClass() );
	}

	public function test_prepare_for_storage_casts_to_bool() {

		$column = new Boolean( 'active' );
		$value  = $column->convert_raw_to_value( '1' );

		$this->assertInternalType( 'boolean', $value );
		$this->assertTrue( $value );

		$value  = $column->convert_raw_to_value( '0' );

		$this->assertInternalType( 'boolean', $value );
		$this->assertFalse( $value );
	}
}