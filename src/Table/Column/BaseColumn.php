<?php
/**
 * Contains the abstract class representing table columns.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Column;

/**
 * Class BaseColumn
 * @package IronBound\DB\Column
 */
abstract class BaseColumn implements Column {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var array
	 */
	protected $options = array();

	/**
	 * @var array
	 */
	protected $type_options = array();

	/**
	 * BaseColumn constructor.
	 *
	 * @param string $name         Name of this column.
	 * @param array  $options      Additional options for this column. For example, 'NOT NULL'.
	 * @param array  $type_options Type options. For example '20' in 'BIGINT(20)'.
	 */
	public function __construct( $name, array $options = array(), array $type_options = array() ) {
		$this->name         = $name;
		$this->options      = $options;
		$this->type_options = $type_options;
	}

	/**
	 * @inheritdoc
	 */
	public function get_definition() {
		return "`{$this->name}` {$this->get_definition_without_column_name()}";
	}

	/**
	 * @inheritDoc
	 */
	public function __toString() {
		return $this->get_definition();
	}

	/**
	 * Get the column definition without the column name attached.
	 *
	 * @since 2.0
	 *
	 * @param array $exclude_options
	 *
	 * @return string
	 */
	protected function get_definition_without_column_name( array $exclude_options = array() ) {

		$definition = $this->get_mysql_type();

		if ( $this->type_options ) {
			$definition .= '(' . implode( ',', $this->type_options ) . ')';
		}

		if ( $this->options ) {

			$options = array_udiff( $this->options, $exclude_options, function ( $a, $b ) {
				return strcmp( strtolower( $a ), strtolower( $b ) );
			} );

			$definition .= ' ' . implode( ' ', $options );
		}

		return $definition;
	}

	/**
	 * @inheritdoc
	 */
	abstract public function get_mysql_type();

	/**
	 * @inheritdoc
	 */
	abstract public function convert_raw_to_value( $raw );

	/**
	 * @inheritdoc
	 */
	abstract public function prepare_for_storage( $value );
}