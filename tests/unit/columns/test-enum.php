<?php
/**
 * Test the Enu
 *
 * @author      Iron Bound Designs
 * @since       2.0
 * @copyright   2016 (c) Iron Bound Designs.
 * @license     MIT
 */

namespace IronBound\DB\Tests\Unit\Columns;

use IronBound\DB\Exception\InvalidDataForColumnException;
use IronBound\DB\Table\Column\Enum;

/**
 * Class Test_Enum
 *
 * @package IronBound\DB\Tests\Unit\Columns
 */
class Test_Enum extends \IronBound\DB\Tests\TestCase {

	/**
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionCode 1
	 */
	public function test_exception_thrown_if_enums_is_empty() {
		new Enum( array(), 'status', '', true );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionCode 2
	 */
	public function test_exception_thrown_if_default_is_empty_and_empty_is_not_allowed() {
		new Enum( array( 'paid', 'pending' ), 'status', '', false );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionCode 3
	 */
	public function test_exception_thrown_if_non_scalar_enum_given() {
		new Enum( array( new \stdClass() ), 'status', '', true );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionCode 4
	 */
	public function test_exception_thrown_if_default_type_does_not_match_enum_type() {
		new Enum( array( 'paid', 'pending' ), 'status', 1, true );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionCode 5
	 */
	public function test_exception_thrown_if_enum_types_are_not_all_equal() {
		new Enum( array( 'bob', 1 ), 'status', '', true );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionCode 6
	 */
	public function test_exception_thrown_if_invalid_storage_given() {
		new Enum( array( 'paid', 'pending' ), new \stdClass(), '', true );
	}

	public function test_convert_raw_to_value_throws_exception_if_invalid_value() {

		$enum = new Enum( array( 'paid', 'pending' ), 'status', 'pending', false );

		try {
			$enum->convert_raw_to_value( 'refunded' );
		} catch ( InvalidDataForColumnException $e ) {
			$enum->fallback_to_default_on_error();

			$this->assertEquals( 'pending', $enum->convert_raw_to_value( 'refunded' ) );

			return;
		}

		$this->fail();
	}

	public function test_prepare_for_storage_throws_exception_if_invalid_value() {

		$enum = new Enum( array( 'paid', 'pending' ), 'status', 'pending', false );

		try {
			$enum->prepare_for_storage( 'refunded' );
		} catch ( InvalidDataForColumnException $e ) {
			$enum->fallback_to_default_on_error();

			$this->assertEquals( 'pending', $enum->prepare_for_storage( 'refunded' ) );

			return;
		}

		$this->fail();
	}
}