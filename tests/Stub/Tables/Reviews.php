<?php
/**
 * Reviews table.
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
use IronBound\DB\Table\Column\DateTime;
use IronBound\DB\Table\Column\DecimalBased;
use IronBound\DB\Table\Column\ForeignModel;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;
use IronBound\DB\Tests\Stub\Models\Book;

/**
 * Class Reviews
 * @package IronBound\DB\Tests\Stub\Tables
 */
class Reviews extends BaseTable {

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return "{$wpdb->prefix}reviews";
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'reviews';
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {
		return array(
			'id'        => new IntegerBased( 'BIGINT', 'id', array( 'unsigned', 'auto_increment' ), array( 20 ) ),
			'content'   => new StringBased( 'TEXT', 'content' ),
			'stars'     => new DecimalBased( 'TINYINT', 'stars', array( 'unsigned' ) ),
			'published' => new DateTime( 'published' ),
			'book'      => new ForeignModel( 'book', 'IronBound\DB\Tests\Stub\Models\Book', Manager::get( 'books' ) )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'id'        => 0,
			'content'   => '',
			'stars'     => 3,
			'published' => current_time( 'mysql', true ),
			'book'      => 0
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