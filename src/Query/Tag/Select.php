<?php
/**
 * Select tag.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\DB\Query\Tag;

/**
 * Class Select
 *
 * @package IronBound\DB\Query\Tag
 */
class Select extends Generic {

	/**
	 * Select ALL values.
	 */
	const ALL = '*';

	/**
	 * @var array
	 */
	private $columns = array();

	/**
	 * @var bool
	 */
	private $all_columns;

	/**
	 * @var string
	 */
	private $all_as;

	/**
	 * @var bool
	 */
	private $calc_found_rows = false;

	/**
	 * @var bool
	 */
	private $distinct = false;

	/**
	 * Constructor.
	 *
	 * @param string|null $column
	 * @param string|null $as
	 */
	public function __construct( $column = '*', $as = null ) {

		if ( $column == self::ALL ) {
			$this->all_columns = true;
			$this->all_as      = $as;
		} elseif ( $column !== null ) {
			$this->columns[ $column ] = $as;
		}

		parent::__construct( "select" );
	}

	/**
	 * Select another column.
	 *
	 * @since 1.0
	 *
	 * @param string      $column
	 * @param string|null $as
	 *
	 * @return $this
	 */
	public function also( $column, $as = null ) {
		$this->columns[ $column ] = $as;

		return $this;
	}

	/**
	 * Select an expression.
	 *
	 * Ex. AVG(total)
	 *
	 * @since 1.0
	 *
	 * @param string      $function
	 * @param string      $column
	 * @param string|null $as
	 *
	 * @return $this
	 */
	public function expression( $function, $column, $as = null ) {
		$this->columns["{$function}($column)"] = $as;

		return $this;
	}

	/**
	 * Whether to set the calculate total found rows flag.
	 *
	 * @since 1.0
	 *
	 * @param bool $calculate
	 *
	 * @return $this
	 */
	public function calc_found_rows( $calculate = true ) {
		$this->calc_found_rows = (bool) $calculate;

		return $this;
	}

	/**
	 * Whether to only show distinct results.
	 *
	 * @since 1.0
	 *
	 * @param bool $distinct
	 *
	 * @return $this
	 */
	public function filter_distinct( $distinct = true ) {
		$this->distinct = $distinct;

		return $this;
	}

	/**
	 * Override the get value method.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	protected function get_value() {
		$query = '';

		if ( $this->distinct ) {
			$query .= "DISTINCT ";
		}

		if ( $this->calc_found_rows ) {
			$query .= "SQL_CALC_FOUND_ROWS ";
		}

		if ( $this->all_columns ) {
			if ( $this->all_as ) {
				$query .= "{$this->all_as}.*";
			} else {
				$query .= '*';
			}
		} else {

			foreach ( $this->columns as $column => $as ) {
				$query .= $column;

				if ( $as !== null ) {
					$query .= " AS $as";
				}

				$query .= ", ";
			}

			$query = substr( $query, 0, - 2 );
		}

		return $query;
	}
}