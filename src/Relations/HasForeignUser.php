<?php
/**
 * HasForeignUser class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Relations;

use IronBound\DB\Model;
use IronBound\DB\Query\FluentQuery;
use IronBound\DB\Saver\PostSaver;
use IronBound\DB\Saver\UserSaver;
use IronBound\DB\WP\Posts;
use IronBound\DB\WP\Users;

/**
 * Class HasForeignUser
 *
 * @package IronBound\DB\Relations
 */
class HasForeignUser extends HasForeign {

	/**
	 * @var bool
	 */
	protected $update_meta_cache = false;

	/**
	 * @inheritDoc
	 */
	public function __construct( $attribute, Model $parent ) {
		parent::__construct( $attribute, $parent, new UserSaver() );

		$this->related_primary_key_column = 'ID';
	}

	/**
	 * @inheritDoc
	 */
	protected function make_query_object( $model_class = false ) {
		return new FluentQuery( new Users() );
	}

	/**
	 * Update the user meta cache when loading this relation.
	 *
	 * By default, the meta cache is NOT updated.
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
	public function get_results() {
		$user = parent::get_results();

		if ( ! $user ) {
			return $user;
		}

		update_user_caches( $user );

		if ( $this->update_meta_cache ) {
			update_meta_cache( 'user', $user->ID );
		}

		return $user;
	}

	/**
	 * @inheritDoc
	 */
	public function eager_load( array $models, $callback = null ) {
		$loaded = parent::eager_load( $models, $callback );
		$users  = $loaded->toArray();
		$ids    = array();

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