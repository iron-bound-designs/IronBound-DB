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
 *
 * @package IronBound\DB\Table\Column
 */
class Time extends BaseColumn {

	const PATTERN = '/(-?)(\d+):(\d+):(\d+)(?:\.(\d+)|$)/';

	/**
	 * Maximum number of hours sortable in mysql.
	 */
	const MAX_HOURS = 838;

	const MAX_VALUE = '838:59:59';
	const MIN_VALUE = '-838:59:59';

	/**
	 * @var bool
	 */
	private $fallback = false;

	/**
	 * Whether to fallback to the min or max value if the \DateInterval given is out of range.
	 *
	 * Defaults to false.
	 *
	 * @since 2.0
	 *
	 * @param bool $fallback
	 *
	 * @return $this
	 */
	public function fallback_to_value_on_overflow( $fallback = true ) {
		$this->fallback = $fallback;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function get_mysql_type() {
		return 'TIME';
	}

	/**
	 * @inheritDoc
	 */
	public function convert_raw_to_value( $raw ) {

		if ( empty( $raw ) ) {
			return null;
		}

		if ( ! preg_match( self::PATTERN, $raw, $matches ) ) {
			throw new InvalidDataForColumnException(
				'Regex pattern match failed while converting raw to value.', $this, $raw
			);
		}

		$invert  = $matches[1] === '-';
		$hours   = $matches[2];
		$minutes = $matches[3];
		$seconds = $matches[4];

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
			} elseif ( ! preg_match( self::PATTERN, $value, $matches ) ) {
				throw new InvalidDataForColumnException(
					"Regex pattern match failed while preparing value.", $this, $value
				);
			} else {
				$invert  = $matches[1] === '-';
				$hours   = $matches[2];
				$minutes = $matches[3];
				$seconds = $matches[4];
			}
		} elseif ( $value instanceof \DateInterval ) {

			if ( $value->days && $value->days !== -99999 ) {
				$hours = 24 * $value->days;
				$hours += $value->h;
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

			$minutes = $value->i;
			$seconds = $value->s;
			$invert  = $value->invert;

		} else {
			throw new InvalidDataForColumnException( 'Invalid data format encountered while preparing value.', $this, $value );
		}

		if ( $hours > self::MAX_HOURS ) {
			if ( $this->fallback ) {
				return $value->invert ? self::MIN_VALUE : self::MAX_VALUE;
			} else {
				throw new InvalidDataForColumnException( sprintf(
					'Calculated hours is out of range. Given %d, max is %d.', $hours, self::MAX_HOURS
				) );
			}
		}

		$hours   = zeroise( $hours, 2 );
		$minutes = zeroise( $minutes, 2 );
		$seconds = zeroise( $seconds, 2 );

		$mysql = "{$hours}:{$minutes}:{$seconds}";

		if ( $invert ) {
			$mysql = "-{$mysql}";
		}

		return $mysql;
	}
}