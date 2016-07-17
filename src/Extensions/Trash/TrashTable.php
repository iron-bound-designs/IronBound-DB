<?php
/**
 * TrashTable interface.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIt
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Extensions\Trash;

use IronBound\DB\Table\Table;

/**
 * Interface TrashTable
 * @package IronBound\DB\Table\Trash
 */
interface TrashTable extends Table {

	/**
	 * Get the name of the deleted at column.
	 *
	 * This MUST correspond to a DATETIME colun.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_deleted_at_column();
}