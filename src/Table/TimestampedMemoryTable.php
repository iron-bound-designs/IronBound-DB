<?php
/**
 * Timestamped in-memory table.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2018 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\DB\Table;

/**
 * Class TimestampedMemoryTable
 *
 * @package IronBound\DB\Table
 */
class TimestampedMemoryTable extends InMemoryTable implements TimestampedTable {

	/**
	 * @inheritDoc
	 */
	public function get_created_at_column() {
		return 'created_at';
	}

	/**
	 * @inheritDoc
	 */
	public function get_updated_at_column() {
		return 'updated_at';
	}
}