<?php
/**
 * Contains the class definition for BaseMetaTable.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Extensions\Meta;

use IronBound\DB\Table\BaseTable;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\SimpleForeign;
use IronBound\DB\Table\Column\StringBased;
use IronBound\DB\Table\Table;

/**
 * Class BaseMetaTable
 * @package IronBound\DB\Table\Meta
 */
class BaseMetaTable extends BaseTable implements MetaTable {

	/**
	 * @var Table
	 */
	protected $main_table;

	/**
	 * @var array
	 */
	protected $overrides = array();

	/**
	 * @var string
	 */
	protected $primary_id_column;

	/**
	 * @var string
	 */
	protected $slug;

	/**
	 * BaseMetaTable constructor.
	 *
	 * @param Table $main_table
	 * @param array $overrides
	 */
	public function __construct( Table $main_table, array $overrides = array() ) {
		$this->main_table = $main_table;
		$this->overrides  = $overrides;

		if ( ! empty( $overrides['primary_id_column'] ) ) {
			$this->primary_id_column = $overrides['primary_id_column'];
		} else {
			$this->primary_id_column = $this->build_column_name_for_table( $main_table );
		}

		if ( ! empty( $overrides['slug'] ) ) {
			$this->slug = $overrides['slug'];
		} else {
			$this->slug = $main_table->get_slug() . '-meta';
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_primary_id_column() {
		return $this->primary_id_column;
	}

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return $this->main_table->get_table_name( $wpdb ) . 'meta';
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {

		$primary = $this->get_primary_id_column();

		return array(
			'meta_id'    => new IntegerBased( 'BIGINT', 'meta_id', array( 'unsigned', 'auto_increment' ), array( 20 ) ),
			$primary     => new SimpleForeign( $primary, $this->main_table ),
			'meta_key'   => new StringBased( 'VARCHAR', 'meta_key', array(), array( 255 ) ),
			'meta_value' => new StringBased( 'LONGTEXT', 'meta_value' )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'meta_id'                      => 0,
			$this->get_primary_id_column() => 0,
			'meta_key'                     => '',
			'meta_value'                   => ''
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
		return $this->main_table->get_version();
	}
}