<?php
/**
 * Contains the class for the DateTime column type.
 *
 * @author    Steven A Zahm
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
class Date extends DateTime {

	/**
	 * @inheritDoc
	 */
	public function get_mysql_type() {
		return 'DATE';
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

		return $value->format( 'Y-m-d' );
	}
}
