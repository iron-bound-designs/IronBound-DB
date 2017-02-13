<?php
/**
 * Where tag.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\DB\Query\Tag;

/**
 * Class Where
 *
 * @package IronBound\DB\Query\Tag
 */
class Where extends Generic {

	/**
	 * And queries.
	 */
	const kAND = 'AND';

	/**
	 * Or queries.
	 */
	const kOR = 'OR';

	/**
	 * Exclusive or queries.
	 */
	const kXOR = 'XOR';

	/**
	 * @var array
	 */
	private $clauses = array();

	/**
	 * @var string
	 */
	private $column;

	/**
	 * @var bool
	 */
	private $operator;

	/**
	 * @var bool
	 */
	private $for_clause = false;

	/**
	 * Constructor.
	 *
	 * @param string      $column
	 * @param string|bool $equality True for =, False for !=
	 * @param mixed       $value    Values should be pre-escaped.
	 */
	public function __construct( $column, $equality, $value ) {
		parent::__construct( 'where' );

		$this->column = $column;

		if ( is_array( $value ) && count( $value ) == 1 ) {
			$this->value = reset( $value );
		} else {
			$this->value = $value;
		}

		if ( is_array( $this->value ) ) {
			$quoted_value = array();

			foreach ( $this->value as $value ) {
				$quoted_value[] = "'$value'";
			}

			$this->value = $quoted_value;
		}

		$this->operator = $equality;
	}

	/**
	 * Generate a Where tag that will be used only as a clause.
	 *
	 * @since 2.0.0
	 *
	 * @return Where
	 */
	public static function for_clause() {
		$where             = new self( '', '', '' );
		$where->for_clause = true;


		return $where;
	}

	/**
	 * Is the where tag empty.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_empty() {
		return ! $this->column && ! $this->clauses;
	}

	/**
	 * Is the Where tag supposed to represent an entire clause.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_for_clause() { return $this->for_clause; }

	/**
	 * Perform an OR.
	 *
	 * @since 1.0
	 *
	 * @param Where $or
	 *
	 * @return $this
	 */
	public function qOr( Where $or ) {
		$this->add_clause( self::kOR, $or );

		return $this;
	}

	/**
	 * Perform an AND.
	 *
	 * @since 1.0
	 *
	 * @param Where $and
	 *
	 * @return $this
	 */
	public function qAnd( Where $and ) {
		$this->add_clause( self::kAND, $and );

		return $this;
	}

	/**
	 * Perform a XOR.
	 *
	 * @since 1.0
	 *
	 * @param Where $xor
	 *
	 * @return $this
	 */
	public function qXor( Where $xor ) {
		$this->add_clause( self::kXOR, $xor );

		return $this;
	}

	/**
	 * Add a clause.
	 *
	 * @since 1.0
	 *
	 * @param       $type
	 * @param Where $clause
	 */
	protected function add_clause( $type, Where $clause ) {
		$this->clauses[] = array(
			'type'   => $type,
			'clause' => $clause
		);
	}

	/**
	 * Get the actual comparison value to be checked.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	protected function get_comparison() {

		if ( ! $this->column ) {
			return '';
		}

		$query = "{$this->column} ";

		if ( is_array( $this->value ) ) {
			$values = $this->implode( $this->value );

			if ( $this->operator ) {
				$query .= "IN ($values)";
			} else {
				$query .= "NOT IN ($values)";
			}
		} elseif ( $this->value === null ) {

			if ( $this->operator === true || $this->operator === '=' ) {
				$query .= 'IS NULL';
			} else {
				$query .= 'IS NOT NULL';
			}

		} else {

			if ( is_bool( $this->operator ) ) {
				$operator = $this->operator ? '=' : '!=';
			} else {
				$operator = $this->operator;
			}

			$query .= "$operator '{$this->value}'";
		}

		return $query;
	}

	/**
	 * Override the value method. Handles recursion of nested Where clauses.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	protected function get_value() {

		$query = $this->for_clause ? '(' : $this->get_comparison();

		if ( ! empty( $this->clauses ) ) {
			foreach ( $this->clauses as $i => $clause ) {

				// We don't want to add the connector if this is the first clause and there is no comparison.
				if ( strlen( $query ) > 1 ) {
					$query .= ' ' . $clause['type'] . ' ';
				}

				/** @var Where $where */
				$where = $clause['clause'];

				if ( ! $where->is_for_clause() ) {
					$query .= '(';
				}

				$query .= $where->get_value();

				if ( ! $where->is_for_clause() ) {
					$query .= ')';
				}
			}
		}

		if ( $this->for_clause ) {
			$query .= ')';
		}

		return $query;
	}

	/**
	 * @inheritDoc
	 */
	public function __toString() {

		if ( $this->is_empty() ) {
			return '';
		}

		return parent::__toString();
	}
}