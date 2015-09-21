<?php
/**
 * Group By tag.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\DB\Query\Tag;

/**
 * Class Group
 * @package IronBound\DB\Query\Tag
 */
class Group extends Generic {

	/**
	 * @var array
	 */
	private $groups = array();

	/**
	 * Constructor.
	 *
	 * @param string $column
	 */
	public function __construct( $column ) {

		$this->groups[] = $column;

		parent::__construct( "GROUP BY" );
	}

	/**
	 * Add an additional group by clause.
	 *
	 * @param string $column
	 *
	 * @return Group
	 */
	public function then( $column ) {
		$this->groups[] = $column;

		return $this;
	}

	/**
	 * Override the value function.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	protected function get_value() {
		return $this->implode( $this->groups );
	}

}