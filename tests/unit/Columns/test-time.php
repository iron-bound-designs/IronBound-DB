<?php
/**
 * Test the TIME column.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Unit\Columns;

use IronBound\DB\Exception\InvalidDataForColumnException;
use IronBound\DB\Table\Column\Time;

class Test_Time extends \IronBound\DB\Tests\TestCase {

	/**
	 * @expectedException \IronBound\DB\Exception\InvalidDataForColumnException
	 */
	public function test_convert_raw_to_value_throws_exception_if_pattern_match_fails() {

		$time = new Time( 'time' );
		$time->convert_raw_to_value( 'garbage' );
	}

	/**
	 * @dataProvider _dp_convert_raw_to_value
	 *
	 * @param $raw
	 * @param $value
	 */
	public function test_convert_raw_to_value( $raw, $value ) {

		$time = new Time( 'time' );

		$this->assertEquals( $value, $time->convert_raw_to_value( $raw ) );
	}

	public function _dp_convert_raw_to_value() {
		$inverted         = new \DateInterval( 'PT4H15M30S' );
		$inverted->invert = true;

		return array(
			array( null, null ),
			array( '', null ),
			array( '00:00:30', new \DateInterval( 'PT30S' ) ),
			array( '00:15:30', new \DateInterval( 'PT15M30S' ) ),
			array( '4:15:30', new \DateInterval( 'PT4H15M30S' ) ),
			array( '-4:15:30', $inverted ),
		);
	}

	/**
	 * @expectedException \IronBound\DB\Exception\InvalidDataForColumnException
	 */
	public function test_prepare_for_storage_throws_exception_if_pattern_match_fails() {

		$time = new Time( 'time' );
		$time->prepare_for_storage( 'garbage' );
	}

	public function test_prepare_for_storage_throws_exception_if_data_is_out_of_range_date_interval() {

		$now  = new \DateTime( '2016-01-01 00:05:09', new \DateTimeZone( 'UTC' ) );
		$days = $now->diff( new \DateTime( '2016-02-28 23:30:59', new \DateTimeZone( 'UTC' ) ) );

		$time = new Time( 'time' );
		try {
			$time->prepare_for_storage( $days );
		} catch ( InvalidDataForColumnException $e ) {
			$this->assertNotFalse( strstr( $e->getMessage(), (string) Time::MAX_HOURS ) );

			return;
		}

		$this->fail();
	}

	public function test_prepare_for_storage_throws_exception_if_data_is_out_of_range_string() {

		$time = new Time( 'time' );
		try {
			$time->prepare_for_storage( '1000:59:59' );
		} catch ( InvalidDataForColumnException $e ) {
			$this->assertNotFalse( strstr( $e->getMessage(), (string) Time::MAX_HOURS ) );

			return;
		}

		$this->fail();
	}

	/**
	 * @dataProvider _dp_prepare_for_storage
	 *
	 * @param $value
	 * @param $expected
	 */
	public function test_prepare_for_storage( $value, $expected ) {

		$time = new Time( 'time' );

		$this->assertEquals( $expected, $time->prepare_for_storage( $value ) );
	}

	public function _dp_prepare_for_storage() {
		$inverted         = new \DateInterval( 'PT4H15M30S' );
		$inverted->invert = true;

		$now  = new \DateTime( '2016-01-01 00:05:09', new \DateTimeZone( 'UTC' ) );
		$days = $now->diff( new \DateTime( '2016-02-02 23:30:59', new \DateTimeZone( 'UTC' ) ) );

		return array(
			array( null, null ),
			array( '', null ),
			array( ' ', null ),
			array( '4:30:59', '04:30:59' ),
			array( new \DateInterval( 'PT30S' ), '00:00:30' ),
			array( new \DateInterval( 'PT15M30S' ), '00:15:30' ),
			array( new \DateInterval( 'PT4H15M30S' ), '04:15:30' ),
			array( new \DateInterval( 'P4DT4H15M30S' ), '100:15:30' ),
			array( $days, '791:25:50' ),
			array( $inverted, '-04:15:30' ),
		);
	}
}