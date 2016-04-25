<?php
/**
 * Contains the class for the Time column type.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Column;

use IronBound\DB\Exception\InvalidDataForColumnException;

/**
 * Class Time
 * @package IronBound\DB\Table\Column
 */
class Time extends BaseColumn {

	const PATTERN = '(-?)(\d+):(\d+):(\d+)(?:\.(\d+)|$)';

	/**
	 * Maximum number of hours sortable in mysql.
	 */
	const MAX_HOURS = 838;

	const MAX_VALUE = '838:59:59';
	const MIN_VALUE = '-838:59:59';

	/**
	 * @inheritDoc
	 */
	public function get_mysql_type() {
		return 'TIME';
	}

	/**
	 * @inheritDoc
	 */
	public function convert_raw_to_value( $raw, \stdClass $row = null ) {

		preg_match( self::PATTERN, $raw, $matches );

		$invert  = $matches[0] === '-';
		$hours   = $matches[1];
		$minutes = $matches[2];
		$seconds = $matches[3];

		$format = 'PT';

		if ( $hours ) {
			$format .= "{$hours}H";
		}

		if ( $minutes ) {
			$format .= "{$minutes}M";
		}

		if ( $seconds ) {
			$format .= "{$seconds}S";
		}

		$interval         = new \DateInterval( $format );
		$interval->invert = $invert;

		return $interval;
	}

	/**
	 * @inheritDoc
	 */
	public function prepare_for_storage( $value ) {

		if ( is_string( $value ) || is_null( $value ) ) {

			$value = trim( $value );

			if ( empty( $value ) ) {
				return $value;
			} elseif ( ! preg_match( self::PATTERN, $value ) ) {
				throw new InvalidDataForColumnException(
					"Regex pattern match failed while preparing value.", $this, $value
				);
			} else {
				return $value;
			}
		} elseif ( $value instanceof \DateInterval ) {

			if ( $value->days ) {
				$hours = 24 * $value->days;
			} else {

				if ( $value->y ) {
					return $value->invert ? self::MIN_VALUE : self::MAX_VALUE;
				}

				if ( $value->m > 1 ) {
					return $value->invert ? self::MIN_VALUE : self::MAX_VALUE;
				}

				$hours = $value->h;

				if ( $value->m ) {
					// This can't be accurate. To ensure accuracy, only hours, minutes, and seconds should be used.
					$hours += 24 * 30 * $value->m;
				}

				if ( $value->d ) {
					$hours += 24 * $value->d;
				}
			}

			if ( $hours > self::MAX_HOURS ) {
				return $value->invert ? self::MIN_VALUE : self::MAX_VALUE;
			}

			$minutes = $value->i;
			$seconds = $value->s;

			$mysql = "{$hours}:{$minutes}:{$seconds}";

			if ( $value->invert ) {
				$mysql = "-{$mysql}";
			}

			return $mysql;
		} else {
			throw new InvalidDataForColumnException( 'Invalid data format encountered while preparing value.', $this, $value );
		}
	}
}