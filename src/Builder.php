<?php
/**
 * Builder for DB models.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\DB;

use IronBound\DB\Table\Table;

/**
 * Class Builder
 *
 * @package IronBound\DB
 */
abstract class Builder {

	/**
	 * Data to be inserted.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		foreach ( $this->get_table()->get_column_defaults() as $col => $default ) {
			$this->set_col_value( $col, $default );
		}
	}

	/**
	 * Build the object.
	 *
	 * @since 1.0
	 *
	 * @return Model
	 * @throws Exception
	 */
	public abstract function build();

	/**
	 * Set a column value.
	 *
	 * @since 1.0
	 *
	 * @param string $col
	 * @param mixed  $val
	 */
	protected final function set_col_value( $col, $val ) {
		$this->data[ $col ] = $val;
	}

	/**
	 * Get the value of a column.
	 *
	 * @since 1.0
	 *
	 * @param string $col
	 *
	 * @return mixed
	 */
	protected final function get_col_value( $col ) {
		if ( ! isset( $this->data[ $col ] ) ) {
			throw new \InvalidArgumentException( "Column does not exist." );
		}

		return $this->data[ $col ];
	}

	/**
	 * Create the object.
	 *
	 * This will call validate functions on columns if they exist. These should
	 * be used to validate data that is dependent on other data.
	 *
	 * @since 1.0
	 *
	 * @return int|string PK of new record.
	 * @throws Exception
	 */
	protected function create() {

		foreach ( $this->data as $col => $val ) {

			if ( method_exists( $this, "validate_$col" ) ) {
				$this->data[ $col ] = $this->{"validate_$col"}( $val );
			}
		}

		return Manager::make_simple_query_object( $this->get_table()->get_slug() )
		              ->insert( $this->data );
	}

	/**
	 * Get the table for this model.
	 *
	 * @since 1.0
	 *
	 * @return Table
	 */
	protected abstract function get_table();
}