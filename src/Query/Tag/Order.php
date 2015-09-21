<?php
/**
 * Order By Tag
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\DB\Query\Tag;

/**
 * Class Order
 * @package IronBound\DB\Query\Tag
 */
class Order extends Generic {

	/**
	 * Sort in ascending order.
	 */
	const ASC = 'ASC';

	/**
	 * Sort in descending order.
	 */
	const DESC = 'DESC';

	/**
	 * Order by random.
	 */
	const RAND = 'RAND()';

	/**
	 * @var bool
	 */
	private $is_rand = false;

	/**
	 * @var array
	 */
	private $orders = array();

	/**
	 * Constructor.
	 *
	 * @param string      $col
	 * @param string|null $direction Either ASC, DESC, or RAND. If null, mysql default.
	 */
	public function __construct( $col, $direction = null ) {

		if ( $col === self::RAND ) {
			$this->is_rand = true;
		} else {
			$this->add_order( $col, $direction );
		}

		parent::__construct( "ORDER BY" );
	}

	/**
	 * Add an additional order by clause.
	 *
	 * @since 1.0
	 *
	 * @param string      $col
	 * @param string|null $direction
	 *
	 * @return Order
	 */
	public function then( $col, $direction = null ) {
		if ( $this->is_rand ) {
			throw new \LogicException( "This ORDER BY statement is already RAND()." );
		}


		$this->orders[ $col ] = $direction;

		return $this;
	}

	/**
	 * Sanitize inputs and save state.
	 *
	 * @since 1.0
	 *
	 * @param string      $col
	 * @param string|null $direction
	 */
	protected function add_order( $col, $direction ) {
		if ( $direction !== null && ! in_array( $direction, array( self::ASC, self::DESC ) ) ) {
			throw new \InvalidArgumentException( "Invalid ORDER BY direction." );
		}


		$this->orders[ $col ] = $direction;
	}

	/**
	 * Override the get value method.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	protected function get_value() {
		if ( $this->is_rand ) {
			return self::RAND;
		}

		$query = '';

		foreach ( $this->orders as $column => $direction ) {
			$query .= "$column";

			if ( $direction !== null ) {
				$query .= " $direction";
			}

			$query .= ', ';
		}

		$query = substr( $query, 0, - 2 );

		return $query;
	}

}