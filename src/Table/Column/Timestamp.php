<?php
/**
 * Contains the class for the Timestamp column type.
 *
 * @author    Steven A Zahm
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Column;

/**
 * Class DateTime
 *
 * @package IronBound\DB\Table\Column
 */
class Timestamp extends DateTime {

	/**
	 * @inheritDoc
	 */
	public function get_mysql_type() {
		return 'TIMESTAMP';
	}
}
