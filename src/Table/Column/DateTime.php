<?php
/**
 * Contains the class for the DateTime column type.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Column;

use IronBound\DB\Exception\InvalidDataForColumnException;

/**
 * Class DateTime
 *
 * @package IronBound\DB\Table\Column
 */
class DateTime extends BaseColumn {

	/**
	 * @inheritDoc
	 */
	public function get_mysql_type() {
		return 'DATETIME';
	}

	/**
	 * @inheritDoc
	 */
	public function convert_raw_to_value( $raw ) {

		if ( empty( $raw ) ) {
			return null;
		}

		if ( $raw instanceof \DateTime || ( interface_exists( '\DateTimeInterface' ) && $raw instanceof \DateTimeInterface ) ) {

			$date = clone $raw;
			$date->setTimezone( new \DateTimeZone( 'UTC' ) );

			return $date;
		}

		try {
			return new \DateTime( $raw, new \DateTimeZone( 'UTC' ) );
		} catch ( \Exception $e ) {
			return null;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function prepare_for_storage( $value ) {

		if ( empty( $value ) ) {
			return null;
		} elseif ( is_numeric( $value ) ) {
			$value = new \DateTime( "@$value", new \DateTimeZone( 'UTC' ) );
		} elseif ( is_string( $value ) ) {
			$value = new \DateTime( $value, new \DateTimeZone( 'UTC' ) );
		} elseif ( is_object( $value ) && ! $value instanceof \DateTime && ! $value instanceof \DateTimeInterface ) {
			throw new InvalidDataForColumnException(
				'Non \DateTime object encountered while preparing value.', $this, $value
			);
		} elseif ( is_object( $value ) ) {
			$value->setTimezone( new \DateTimeZone( 'UTC' ) );
		}

		return $value->format( 'Y-m-d H:i:s' );
	}
}