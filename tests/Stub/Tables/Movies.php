<?php
/**
 * Movies table.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Stub\Tables;

use IronBound\DB\Table\BaseTable;
use IronBound\DB\Table\Column\DateTime;
use IronBound\DB\Table\Column\DecimalBased;
use IronBound\DB\Table\Column\ForeignPost;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;
use IronBound\DB\Saver\PostSaver;

/**
 * Class Movies
 * @package IronBound\DB\Tests\Stub\Tables
 */
class Movies extends BaseTable {

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return "{$wpdb->prefix}movies";
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'movies';
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {
		return array(
			'id'           => new IntegerBased( 'BIGINT', 'id', array( 'unsigned', 'auto_increment' ), array( 20 ) ),
			'title'        => new StringBased( 'VARCHAR', 'title', array(), array( 191 ) ),
			'release_date' => new DateTime( 'release_date' ),
			'poster'       => new ForeignPost( 'poster' ),
			'description'  => new StringBased( 'LONGTEXT', 'description' ),
			'earnings'     => new DecimalBased( 'DECIMAL', 'earnings', array(), array( 10, 2 ) )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'id'           => 0,
			'title'        => '',
			'release_date' => '',
			'poster'       => 0,
			'description'  => '',
			'earnings'     => 0
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