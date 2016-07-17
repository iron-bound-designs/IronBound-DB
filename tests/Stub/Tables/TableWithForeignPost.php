<?php
/**
 * TableWithForeignPost.
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
use IronBound\DB\Saver\PostSaver;
use IronBound\DB\Table\ForeignKey\DeleteConstrained;

/**
 * Class TableWithForeignPost
 * @package IronBound\DB\Tests\Stub\Tables
 */
class TableWithForeignPost extends BaseTable implements DeleteConstrained {

	/**
	 * @var string
	 */
	protected $constraint;

	/**
	 * TableWithForeignPost constructor.
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
		return "{$wpdb->prefix}with_foreign_post";
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'with-foreign-post';
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {
		return array(
			'id'        => new IntegerBased( 'BIGINT', 'id', array( 'unsigned', 'auto_increment' ), array( 20 ) ),
			'post'      => new ForeignPost( 'post' ),
			'price'     => new DecimalBased( 'DECIMAL', 'price', array( 'unsigned' ), array( 10, 2 ) ),
			'published' => new DateTime( 'published' )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'id'        => 0,
			'post'      => 0,
			'price'     => 0,
			'published' => current_time( 'mysql', true )
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
			'post' => $this->constraint
		);
	}
}