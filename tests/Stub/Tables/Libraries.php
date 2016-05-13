<?php
/**
 * Libraries table.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Stub\Tables;

use IronBound\DB\Table\BaseTable;
use IronBound\DB\Table\Column\DecimalBased;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;

/**
 * Class Libraries
 * @package IronBound\DB\Tests\Stub\Tables
 */
class Libraries extends BaseTable {

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return "{$wpdb->prefix}libraries";
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'libraries';
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {
		return array(
			'id'   => new IntegerBased( 'BIGINT', 'id', array( 'unsigned', 'auto_increment' ), array( 20 ) ),
			'name' => new StringBased( 'VARCHAR', 'name', array(), array( 255 ) ),
			'lat'  => new DecimalBased( 'DECIMAL', 'lat', array(), array( 10, 6 ) ),
			'lon'  => new DecimalBased( 'DECIMAL', 'lon', array(), array( 10, 6 ) )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'id'   => 0,
			'name' => '',
			'lat'  => 0,
			'lon'  => 0
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