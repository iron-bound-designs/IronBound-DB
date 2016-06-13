<?php
/**
 * PHP 5.4 Table
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Stub\Tables;

use IronBound\DB\Table\BaseTable;
use IronBound\DB\Table\Column\DateTime;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;
use IronBound\DB\Extensions\Trash\TrashTable;

/**
 * Class PHP54s
 * @package IronBound\DB\Tests\Stub\Tables
 */
class PHP54s extends BaseTable implements TrashTable {

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return "{$wpdb->prefix}php54";
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'php54';
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {
		return array(
			'id'         => new IntegerBased( 'BIGINT', 'id', array( 'unsigned', 'auto_increment' ), array( 20 ) ),
			'name'       => new StringBased( 'VARCHAR', 'name', array(), array( 255 ) ),
			'deleted_at' => new DateTime( 'deleted_at' )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'id'   => 0,
			'name' => ''
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_deleted_at_column() {
		return 'deleted_at';
	}

	/**
	 * @inheritDoc
	 */
	public function get_primary_key() {
		return 'id';
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() {
		return 1;
	}
}