<?php
/**
 * Test Books table.
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
use IronBound\DB\Table\ForeignKey\DeleteConstrained;
use IronBound\DB\Tests\Stub\Models\Author;

/**
 * Class Books
 * @package IronBound\DB\Tests\Stub\Tables
 */
class Books extends BaseTable implements DeleteConstrained {

	/**
	 * @var string
	 */
	protected $constraint;

	/**
	 * Books constructor.
	 *
	 * @param string $constraint
	 */
	public function __construct( $constraint = self::CASCADE ) {
		$this->constraint = $constraint;
	}

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return "{$wpdb->prefix}books";
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'books';
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {
		return array(
			'id'        => new IntegerBased( 'BIGINT', 'id', array( 'unsigned', 'auto_increment' ), array( 20 ) ),
			'title'     => new StringBased( 'TEXT', 'title' ),
			'price'     => new DecimalBased( 'DECIMAL', 'price', array( 'unsigned' ), array( 10, 2 ) ),
			'published' => new DateTime( 'published' ),
			'author'    => new ForeignModel( 'author', get_class( new Author() ), Manager::get( 'authors' ) )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'id'        => 0,
			'title'     => '',
			'price'     => 0,
			'published' => current_time( 'mysql', true ),
			'author'    => 0
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

	/**
	 * @inheritDoc
	 */
	public function get_delete_constraints() {
		return array(
			'author' => $this->constraint
		);
	}
}