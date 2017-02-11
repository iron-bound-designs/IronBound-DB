<?php
/**
 * Test the DATETIME column.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Unit\Columns;

use IronBound\DB\Table\Column\DateTime;

/**
 * Class Test_DateTime
 *
 * @package IronBound\DB\Tests\Unit\Columns
 */
class Test_DateTime extends \IronBound\DB\Tests\TestCase {

	public function test_convert_raw_to_value_returns_null_for_empty_values() {

		$datetime = new DateTime( 'created' );

		$this->assertNull( $datetime->convert_raw_to_value( '' ) );
	}

	public function test_convert_raw_to_value_returns_null_for_invalid_values() {

		$datetime = new DateTime( 'created' );

		$this->assertNull( $datetime->convert_raw_to_value( 'garbage' ) );
	}

	public function test_prepare_for_storage_returns_null_for_empty_values() {

		$datetime = new DateTime( 'created' );

		$this->assertNull( $datetime->prepare_for_storage( '' ) );
	}

	/**
	 * @expectedException \IronBound\DB\Exception\InvalidDataForColumnException
	 */
	public function test_prepare_for_storage_throws_exception_for_invalid_object() {

		$datetime = new DateTime( 'created' );

		$this->assertNull( $datetime->prepare_for_storage( new \stdClass() ) );
	}

	/**
	 * @dataProvider _dp_prepare_for_storage
	 *
	 * @param mixed  $value
	 * @param string $expected
	 */
	public function test_prepare_for_storage( $value, $expected ) {

		$column = new DateTime( 'created' );

		$this->assertEquals( $expected, $column->prepare_for_storage( $value ) );
	}

	public function _dp_prepare_for_storage() {
		return array(
			array( 1469386689, '2016-07-24 18:58:09' ),
			array( 'July 24, 2016 6pm', '2016-07-24 18:00:00' ),
			array( '2016-07-24 18:58:09', '2016-07-24 18:58:09' ),
			array( new \DateTime( '2016-07-24 18:58:09', new \DateTimeZone( 'UTC' ) ), '2016-07-24 18:58:09' )
		);
	}
}