<?php
/**
 * TimestampedTable interface.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table;

/**
 * Interface TimestampedTable
 * @package IronBound\DB\Table
 */
interface TimestampedTable extends Table {

	/**
	 * Get the name of the created at column.
	 * 
	 * Must correspond to a DateTime column.
	 * 
	 * @since 2.0
	 * 
	 * @return string
	 */
	public function get_created_at_column();

	/**
	 * Get the name of the updated at column.
	 * 
	 * Must correspond to a DateTime column.
	 * 
	 * @since 2.0
	 * 
	 * @return string
	 */
	public function get_updated_at_column();
}