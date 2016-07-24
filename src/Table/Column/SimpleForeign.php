<?php
/**
 * Contains the class for the Foreign column type.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Column;

use IronBound\DB\Table\Table;

/**
 * Class SimpleForeign
 * @package IronBound\DB\Table\Column
 */
class SimpleForeign extends BaseColumn implements Foreign {

	/**
	 * @var Table
	 */
	protected $foreign_table;

	/**
	 * @var string
	 */
	protected $foreign_column;

	/**
	 * SimpleForeign constructor.
	 *
	 * @param string      $name           Column name.
	 * @param Table       $foreign_table  Table the foreign key resides in.
	 * @param string|null $foreign_column Specify the column being related to. If null, the primary key column is used.
	 */
	public function __construct( $name, Table $foreign_table, $foreign_column = null ) {
		parent::__construct( $name );

		$this->foreign_table  = $foreign_table;
		$this->foreign_column = $foreign_column;
	}

	/**
	 * @inheritDoc
	 */
	public function get_foreign_table() {
		return $this->foreign_table;
	}

	/**
	 * @inheritDoc
	 */
	public function get_foreign_table_column_name() {
		return $this->foreign_column ?: $this->foreign_table->get_primary_key();
	}

	/**
	 * Get the referenced column.
	 *
	 * @since 2.0
	 *
	 * @return BaseColumn
	 */
	protected function get_column() {

		$column  = $this->foreign_column ?: $this->foreign_table->get_primary_key();
		$columns = $this->foreign_table->get_columns();

		return $columns[ $column ];
	}

	/**
	 * @inheritDoc
	 */
	public function get_definition() {
		return "{$this->name} {$this->get_column()->get_definition_without_column_name( array( 'auto_increment' ) )}";
	}

	/**
	 * @inheritDoc
	 */
	public function get_mysql_type() {
		return $this->get_column()->get_mysql_type();
	}

	/**
	 * @inheritDoc
	 */
	public function convert_raw_to_value( $raw ) {
		return $this->get_column()->convert_raw_to_value( $raw );
	}

	/**
	 * @inheritDoc
	 */
	public function prepare_for_storage( $value ) {
		return $this->get_column()->prepare_for_storage( $value );
	}
}