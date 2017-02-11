<?php
/**
 * Test the INTEGER column.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Unit\Columns;

use IronBound\DB\Table\Column\IntegerBased;

class Test_IntegerBased extends \IronBound\DB\Tests\TestCase {

	public function test_convert_raw_to_value_casts_to_float() {

		$column = new IntegerBased( 'BIGINT', 'price' );
		$value  = $column->convert_raw_to_value( '5' );

		$this->assertInternalType( 'integer', $value );
		$this->assertSame( 5, $value );
	}

	public function test_prepare_for_storage_passes_null_through() {

		$column = new IntegerBased( 'BIGINT', 'price' );
		$this->assertNull( $column->prepare_for_storage( null ) );
	}

	/**
	 * @expectedException \IronBound\DB\Exception\InvalidDataForColumnException
	 */
	public function test_exception_thrown_if_non_scalar_value_given() {

		$column = new IntegerBased( 'BIGINT', 'price' );
		$column->prepare_for_storage( new \stdClass() );
	}

	public function test_prepare_for_storage_casts_to_float() {

		$column = new IntegerBased( 'BIGINT', 'price' );
		$value  = $column->prepare_for_storage( '5' );

		$this->assertInternalType( 'integer', $value );
		$this->assertSame( 5, $value );
	}
}