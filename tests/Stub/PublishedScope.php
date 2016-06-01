<?php
/**
 * PublishedScope stub.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Stub;

use IronBound\DB\Query\FluentQuery;
use IronBound\DB\Query\Scope;

/**
 * Class PublishedScope
 *
 * @package IronBound\DB\Tests\Stub
 */
class PublishedScope implements Scope {

	/**
	 * @inheritDoc
	 */
	public function apply( FluentQuery $query ) {
		$query->where( 'published', false, null );
	}
}