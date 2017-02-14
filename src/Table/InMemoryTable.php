<?php
/**
 * InMemoryTable class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2017.
 */

namespace IronBound\DB\Table;

use IronBound\DB\Table\Column\Column;

/**
 * Class InMemoryTable
 *
 * @package IronBound\DB\Table
 */
class InMemoryTable extends BaseTable {

	/** @var string */
	private $name;

	/** @var string */
	private $slug;

	/** @var string */
	private $primary_key;

	/** @var array */
	private $columns = array();

	/** @var array */
	private $defaults = array();

	/**
	 * InMemoryTable constructor.
	 *
	 * @param string   $name
	 * @param Column[] $columns
	 * @param array    $args
	 */
	public function __construct( $name, array $columns, array $args = array() ) {
		$this->name    = $name;
		$this->columns = $columns;

		if ( isset( $args['slug'] ) ) {
			$this->slug = $args['slug'];
		} else {
			$this->slug = str_replace( '_', '-', strtolower( $name ) );
		}

		if ( isset( $args['defaults'] ) ) {
			$this->defaults = $args['defaults'];
		}

		$this->primary_key = empty( $args['primary-key'] ) ? 'id' : $args['primary-key'];

		foreach ( $this->columns as $column_name => $column ) {
			if ( isset( $this->defaults[ $column_name ] ) ) {
				continue;
			}

			switch ( $column->get_mysql_type() ) {
				case 'TINYINT':
				case 'SMALLINT':
				case 'MEDIUMINT':
				case 'INT':
				case 'BIGINT':
					$this->defaults[ $column_name ] = 0;
					break;
				case 'FLOAT':
				case 'DOUBLE':
				case 'DECIMAL':
					$this->defaults[ $column_name ] = 0.0;
					break;
				default:
					$this->defaults[ $column_name ] = '';
					break;
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) { return $wpdb->prefix . $this->name; }

	/**
	 * @inheritDoc
	 */
	public function get_slug() { return $this->slug; }

	/**
	 * @inheritDoc
	 */
	public function get_columns() { return $this->columns; }

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() { return $this->defaults; }

	/**
	 * @inheritDoc
	 */
	public function get_primary_key() { return $this->primary_key; }

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }
}