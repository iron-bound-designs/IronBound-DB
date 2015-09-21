<?php
/**
 * Limit tag.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\DB\Query\Tag;

/**
 * Class Limit
 * @package IronBound\DB\Query\Tag
 */
class Limit extends Generic {

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @param int      $count
	 * @param int|null $offset
	 */
	public function __construct( $count, $offset = null ) {

		$value = $count;

		if ( $offset !== null ) {
			$value = "$offset, $value";
		}

		parent::__construct( "LIMIT", $value );
	}

}