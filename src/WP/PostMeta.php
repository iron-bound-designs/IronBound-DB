<?php
/**
 * Post Meta table.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\WP;

use IronBound\DB\Extensions\Meta\MetaTable;
use IronBound\DB\Table\BaseTable;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;

/**
 * Class PostMeta
 * @package IronBound\DB\WP
 */
class PostMeta extends BaseTable implements MetaTable {

	/**
	 * @inheritDoc
	 */
	public function get_primary_id_column() {
		return 'post_id';
	}

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return $wpdb->postmeta;
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'post-meta';
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {
		return array(
			'meta_id'    => new IntegerBased( 'BIGINT', 'meta_id', array( 'unsigned', 'auto_increment' ), array( 20 ) ),
			'post_id'    => new IntegerBased( 'BIGINT', 'post_id', array( 'unsigned' ), array( 20 ) ),
			'meta_key'   => new StringBased( 'VARCHAR', 'meta_key', array(), array( 255 ) ),
			'meta_value' => new StringBased( 'LONGTEXT', 'meta_value' )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'meta_id'    => 0,
			'post_id'    => 0,
			'meta_key'   => '',
			'meta_value' => '',
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_primary_key() {
		return 'meta_id';
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() {
		return 1;
	}
}
