<?php
/**
 * Test Authors table.
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
use IronBound\DB\Table\TimestampedTable;

/**
 * Class Authors
 * @package IronBound\DB\Tests\Stub\Tables
 */
class Authors extends BaseTable implements TimestampedTable {

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return "{$wpdb->prefix}authors";
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'authors';
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {
		return array(
			'id'         => new IntegerBased( 'BIGINT', 'id', array( 'unsigned', 'auto_increment' ), array( 20 ) ),
			'name'       => new StringBased( 'VARCHAR', 'name', array(), array( 60 ) ),
			'birth_date' => new DateTime( 'birth_date' ),
			'bio'        => new StringBased( 'LONGTEXT', 'bio' ),
			'picture'    => new ForeignPost( 'picture' ),
			'created_at' => new DateTime( 'created_at' ),
			'updated_at' => new DateTime( 'updated_at' )
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
			'picture'    => 0,
			'created_at' => '',
			'updated_at' => ''
		);
	}

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