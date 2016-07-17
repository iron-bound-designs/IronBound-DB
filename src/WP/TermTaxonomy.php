<?php
/**
 * TermTaxonomy table.
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
 * Class TermTaxonomy
 *
 * @package IronBound\DB\WP
 */
class TermTaxonomy extends BaseTable {

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return $wpdb->term_taxonomy;
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'term_taxonomy';
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {
		return array(
			'term_taxonomy_id' =>
				new IntegerBased(
					'BIGINT', 'term_taxonomy_id', array( 'NOT NULL', 'auto_increment', 'unsigned' ), array( 20 )
				),
			'term_id'          => new IntegerBased( 'BIGINT', 'term_id', array( 'NOT NULL', 'unsigned' ), array( 20 ) ),
			'taxonomy'         => new StringBased( 'VARCHAR', 'taxonomy', array( 'NOT NULL' ), array( 32 ) ),
			'description'      => new StringBased( 'LONGTEXT', 'description', array( 'NOT NULL' ) ),
			'parent'           => new IntegerBased( 'BIGINT', 'parent', array( 'NOT NULL', 'unsigned' ), array( 20 ) ),
			'count'            => new IntegerBased( 'BIGINT', 'count', array( 'NOT NULL' ), array( 20 ) ),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'term_taxonomy_id' => 0,
			'term_id'          => 0,
			'taxonomy'         => '',
			'description'      => '',
			'parent'           => 0,
			'count'            => 0,
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_primary_key() {
		return 'term_taxonomy_id';
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() {
		return 1;
	}
}