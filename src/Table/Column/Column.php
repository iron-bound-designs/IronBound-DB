<?php
/**
 * Contains the Column interface definition.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Column;
use IronBound\DB\Exception\InvalidDataForColumnException;

/**
 * Interface Column
 *
 * @package IronBound\DB\Column
 */
interface Column {

	/**
	 * Get the full column definition.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_definition();

	/**
	 * Get the column type as represented in mysql.
	 *
	 * Example: DATETIME, VARCHAR
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_mysql_type();

	/**
	 * Get a value from its raw form.
	 *
	 * For example, convert a date time string to a `DateTime` object.
	 *
	 * @since 2.0
	 *
	 * @param string $raw Raw value retrieved directly from the database.
	 *
	 * @return mixed
	 *
	 * @throws InvalidDataForColumnException
	 */
	public function convert_raw_to_value( $raw );

	/**
	 * Prepare a value for storage and coerce it to be valid.
	 *
	 * Should not apply escaping functions.
	 *
	 * @since 2.0
	 *
	 * @param mixed $value Can be either a raw value or an object.
	 *                     For example, both 2016-01-01 and a \DateTime object are valid.
	 *
	 * @return mixed Prepared.
	 *
	 * @throws InvalidDataForColumnException
	 */
	public function prepare_for_storage( $value );

	/**
	 * Get a string representation of the column.
	 *
	 * In this case, the full column definition.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function __toString();
}