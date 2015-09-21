<?php
/**
 * WP_Date_Query where tag.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\DB\Query\Tag;

/**
 * Class Where_Date
 * @package IronBound\DB\Query\Tag
 */
class Where_Date extends Where_Raw {

	/**
	 * @var \WP_Date_Query
	 */
	private $date_query;

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @param \WP_Date_Query $date_Query
	 */
	public function __construct( \WP_Date_Query $date_Query ) {
		$this->date_query = $date_Query;

		$sql = $this->date_query->get_sql();

		$prefix = ' AND ( ';
		$sql    = substr( $sql, strlen( $prefix ) );
		$sql    = substr( $sql, 0, - 1 );

		parent::__construct( $sql );
	}


}