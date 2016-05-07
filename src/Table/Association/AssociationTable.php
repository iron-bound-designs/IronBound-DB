<?php
/**
 * AssociationTable interface.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Association;

use IronBound\DB\Saver\Saver;
use IronBound\DB\Table\Table;

/**
 * Class AssociationTable
 * @package IronBound\DB\Table
 */
interface AssociationTable extends Table {

	/**
	 * Get the primary column based on a table.
	 *
	 * @since 2.0
	 *
	 * @param Table $table
	 *
	 * @return string
	 */
	public function get_primary_column_for_table( Table $table );
	
	/**
	 * Get the other column based on a table.
	 *
	 * @since 2.0
	 *
	 * @param Table $table
	 *
	 * @return string
	 */
	public function get_other_column_for_table( Table $table );
	
	/**
	 * Get the column name for the a connecting table.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_col_a();

	/**
	 * Get the column name for the b connecting table.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_col_b();

	/**
	 * Get the saver to use.
	 * 
	 * @since 2.0
	 * 
	 * @return Saver
	 */
	public function get_saver();
}