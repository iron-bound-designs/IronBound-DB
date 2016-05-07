<?php
/**
 * Galleries stub table.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Stub\Tables;

use IronBound\DB\Table\BaseTable;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;

/**
 * Class Galleries
 * @package IronBound\DB\Tests\Stub\Tables
 */
class Galleries extends BaseTable {

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return "{$wpdb->prefix}galleries";
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'galleries';
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {
		return array(
			'id'    => new IntegerBased( 'BIGINT', 'id', array( 'unsigned', 'auto_increment' ), array( 20 ) ),
			'title' => new StringBased( 'VARCHAR', 'title', array(), array( 255 ) ),
			'theme' => new StringBased( 'LONGTEXT', 'theme' )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'id'    => 0,
			'title' => '',
			'theme' => ''
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