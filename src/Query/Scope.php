<?php
/**
 * Contains the query Scope interface.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Query;

/**
 * Interface Scope
 *
 * @package IronBound\DB\Query
 */
interface Scope {

	/**
	 * Apply this global scope to a given query.
	 *
	 * @since 2.0
	 *
	 * @param FluentQuery $query
	 */
	public function apply( FluentQuery $query );
}