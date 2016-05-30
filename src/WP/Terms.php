<?php
/**
 * Terms table.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\WP;

use IronBound\DB\Table\BaseTable;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;

/**
 * Class Terms
 * @package IronBound\DB\WP
 */
class Terms extends BaseTable {

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return $wpdb->terms;
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'terms';
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {
		return array(
			'term_id'    =>
				new IntegerBased( 'BIGINT', 'term_id', array( 'NOT NULL', 'auto_increment', 'unsigned' ), array( 20 ) ),
			'name'       => new StringBased( 'VARCHAR', 'name', array( 'NOT NULL' ), array( 200 ) ),
			'slug'       => new StringBased( 'VARCHAR', 'slug', array( 'NOT NULL' ), array( 200 ) ),
			'term_group' => new IntegerBased( 'BIGINT', 'term_group', array( 'NOT NULL' ), array( 10 ) ),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'term_id'    => 0,
			'name'       => '',
			'slug'       => '',
			'term_group' => 0
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_primary_key() {
		return 'term_id';
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() {
		return 1;
	}
}