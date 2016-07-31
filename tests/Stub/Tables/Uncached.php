<?php
/**
 * Uncached table.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Stub\Tables;

use IronBound\DB\Table\BaseTable;
use IronBound\DB\Table\Column\DateTime;
use IronBound\DB\Table\Column\ForeignPost;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;
use IronBound\DB\Saver\PostSaver;

/**
 * Class Uncached
 * @package IronBound\DB\Tests\Stub\Tables
 */
class Uncached extends BaseTable {

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return "{$wpdb->prefix}uncached";
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'uncached';
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {
		return array(
			'id'         => new IntegerBased( 'BIGINT', 'id', array( 'unsigned', 'auto_increment' ), array( 20 ) ),
			'name'       => new StringBased( 'VARCHAR', 'name', array(), array( 60 ) ),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'id'         => 0,
			'name'       => '',
			'birth_date' => '',
			'bio'        => '',
			'picture'    => 0
		);
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