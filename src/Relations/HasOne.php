<?php
/**
 * HasOne relation class definition.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Relations;

use IronBound\DB\Collections\Collection;
use IronBound\DB\Model;
use IronBound\DB\Query\FluentQuery;

/**
 * Class HasOne
 *
 * @package IronBound\DB\Relations
 */
class HasOne extends HasOneOrMany {

	/**
	 * @inheritDoc
	 */
	protected function register_events() {
		// no-op
	}

	/**
	 * @inheritDoc
	 */
	protected function fetch_results() {
		return parent::fetch_results()->first();
	}

	/**
	 * @inheritdoc
	 */
	protected function apply_scopes_for_fetch( FluentQuery $query ) {
		$query->take( 1 );
	}

	/**
	 * @inheritDoc
	 */
	protected function wrap_eager_loaded_results( $results ) {

		if ( empty( $results ) ) {
			return null;
		}

		return reset( $results );
	}

	/**
	 * @inheritDoc
	 */
	public function persist( $values ) {
		$values->save();
	}
}