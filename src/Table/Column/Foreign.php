<?php
/**
 * Foreign column interface.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Column;
use IronBound\DB\Table\Table;

/**
 * Interface Foreign
 * @package IronBound\DB\Table\Column
 */
interface Foreign extends Column {

	/**
	 * Get the foreign table object.
	 *
	 * @since 2.0
	 *
	 * @return Table
	 */
	public function get_foreign_table();

	/**
	 * Get the column name of the foreign table being connected to.
	 *
	 * Typically, this would be the primary key of the foreign table.
	 * For example 'ID' for the wp_posts table.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_foreign_table_column_name();
}