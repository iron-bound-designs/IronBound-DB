<?php
/**
 * Contains the BaseAssociationTable class definition.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Association;

use IronBound\DB\Table\BaseTable;

/**
 * Class BaseAssociationTable
 * @package IronBound\DB\Table\Association
 */
abstract class BaseAssociationTable extends BaseTable implements AssociationTable {

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			$this->get_col_a() => '',
			$this->get_col_b() => ''
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_primary_key() {
		return '';
	}
}