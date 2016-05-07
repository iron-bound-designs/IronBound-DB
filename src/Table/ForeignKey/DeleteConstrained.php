<?php
/**
 * ForeignKeyDelete interface.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\ForeignKey;
use IronBound\DB\Table\Table;

/**
 * Interface DeleteConstrained
 * @package IronBound\DB\Table\ForeignKey
 */
interface DeleteConstrained extends Table {

	/**
	 * Cascade behavior.
	 *
	 * All children will be deleted when the referenced row is deleted.
	 */
	const CASCADE = 'cascade';

	/**
	 * Restrict behavior.
	 *
	 * A `DeleteRestrictedException` will be thrown if any child rows exist when the referenced row is deleted.
	 */
	const RESTRICT = 'restrict';

	/**
	 * Set default behavior.
	 *
	 * All children will have the constrained column's value set to its default value when the reference row is deleted.
	 */
	const SET_DEFAULT = 'set-default';

	/**
	 * Get the foreign key delete constraints for this table.
	 *
	 * This should be a map of column names to behavior types.
	 *
	 * @since 2.0
	 *
	 * @return array
	 */
	public function get_delete_constraints();
}
