<?php
/**
 * Contains the class definition for ManyToManyUsers
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Relations;

use IronBound\DB\Collection;
use IronBound\DB\Model;
use IronBound\DB\Query\FluentQuery;
use IronBound\DB\Table\Association\UserAssociationTable;
use IronBound\DB\WP\Users;

/**
 * Class ManyToManyPosts
 *
 * @package IronBound\DB\Relations
 */
class ManyToManyUsers extends ManyToMany {

	/**
	 * @var bool
	 */
	protected $update_meta_cache = false;

	/**
	 * @inheritDoc
	 */
	public function __construct( Model $parent, UserAssociationTable $association, $attribute ) {
		parent::__construct( '', $parent, $association, $attribute );

		$this->join_on = 'ID';
	}

	/**
	 * Update the user meta cache when loading this relation.
	 *
	 * @since 2.0
	 *
	 * @param bool $update
	 *
	 * @return $this
	 */
	public function update_meta_cache( $update = true ) {
		$this->update_meta_cache = $update;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	protected function make_query_object( $model_class = false ) {
		return new FluentQuery( new Users() );
	}

	/**
	 * @inheritDoc
	 */
	public function get_results() {
		$results = parent::get_results();
		$users   = $results->toArray();

		$ids = array();

		foreach ( $users as $user ) {
			update_user_caches( $user );

			$ids[] = $user->ID;
		}

		if ( $this->update_meta_cache ) {
			update_meta_cache( 'user', $ids );
		}

		return $results;
	}

	/**
	 * @inheritDoc
	 */
	protected function register_events( Collection $results ) {
		// no-op there is no corresponding model to keep synced
	}

	/**
	 * @inheritDoc
	 */
	protected function register_cache_events() {
		// no-op
	}

	/**
	 * @inheritDoc
	 */
	public function eager_load( array $models, $callback = null ) {
		$loaded = parent::eager_load( $models, $callback );
		$users  = $loaded->toArray();

		$ids = array();

		foreach ( $users as $user ) {
			update_user_caches( $user );

			$ids[] = $user->ID;
		}

		if ( $this->update_meta_cache ) {
			update_meta_cache( 'user', $ids );
		}

		return $loaded;
	}
}