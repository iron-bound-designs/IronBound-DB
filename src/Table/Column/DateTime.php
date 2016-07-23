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
	public function convert_raw_to_value( $raw, \stdClass $row = null ) {

		if ( empty( $raw ) ) {
			return null;
		}

		try {
			return new \DateTime( $raw, new \DateTimeZone( 'UTC' ) );
		}
		catch ( \Exception $e ) {
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
			$value = new \DateTime( "@$value" );
		} elseif ( is_string( $value ) ) {
			$value = new \DateTime( $value );
		} elseif ( is_object( $value ) && ! $value instanceof \DateTime && ! $value instanceof \DateTimeInterface ) {
			throw new InvalidDataForColumnException(
				'Non \DateTime object encountered while preparing value.', $this, $value
			);
		}

		return $value->format( 'Y-m-d H:i:s' );
	}
}