<?php
/**
 * Perform simple joins.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\DB\Query\Tag;

/**
 * Class Join
 * @package IronBound\DB\Query\Tag
 */
class Join extends Generic {

	/**
	 * Constructor.
	 *
	 * @param From  $on
	 * @param Where $where
	 */
	public function __construct( From $on, Where $where ) {

		$sql = $on->get_value() . ' ON (' . $where->get_value() . ')';

		parent::__construct( 'JOIN', $sql );
	}
}