<?php
/**
 * Contains the MetaTable interface definition.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Extensions\Meta;

use IronBound\DB\Table\Table;

/**
 * Interface MetaTable
 * @package IronBound\DB\Table\Meta
 */
interface MetaTable extends Table {

	/**
	 * Get the column name for the primary id column.
	 *
	 * For example 'post_id' for the postmeta table.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_primary_id_column();
}