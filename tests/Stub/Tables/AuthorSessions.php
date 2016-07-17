<?php
/**
 * AuthorSessions table.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Stub\Tables;

use IronBound\DB\Manager;
use IronBound\DB\Saver\ModelSaver;
use IronBound\DB\Table\BaseTable;
use IronBound\DB\Table\Column\ForeignModel;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;
use IronBound\DB\Tests\Stub\Models\Author;

/**
 * Class AuthorSessions
 * @package IronBound\DB\Tests\Stub\Tables
 */
class AuthorSessions extends BaseTable {
	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return $wpdb->prefix . 'author_sessions';
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'author-sessions';
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {
		return array(
			'id'     => new IntegerBased( 'BIGINT', 'id', array( 'unsigned', 'auto_increment' ), array( 20 ) ),
			'data'   => new StringBased( 'LONGTEXT', 'data' ),
			'author' => new ForeignModel( 'author', get_class( new Author() ), Manager::get( 'authors' ) )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'id'     => 0,
			'data'   => '',
			'author' => 0
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