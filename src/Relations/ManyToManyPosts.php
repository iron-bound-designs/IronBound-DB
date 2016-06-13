<?php
/**
 * Contains the class definition for ManyToManyPosts
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
use IronBound\DB\Table\Association\PostAssociationTable;
use IronBound\DB\WP\Posts;

/**
 * Class ManyToManyPosts
 * @package IronBound\DB\Relations
 */
class ManyToManyPosts extends ManyToMany {

	/**
	 * @var bool
	 */
	protected $update_meta_cache = true;

	/**
	 * @var bool
	 */
	protected $update_term_cache = true;

	/**
	 * @inheritDoc
	 */
	public function __construct( Model $parent, PostAssociationTable $association, $attribute ) {
		parent::__construct( '', $parent, $association, $attribute );

		$this->join_on = 'ID';
	}

	/**
	 * Update the post meta cache when loading this relation.
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
	 * Update the term cache when loading this relation.
	 *
	 * @since 2.0
	 *
	 * @param bool $update
	 *
	 * @return $this
	 */
	public function update_term_cache( $update = true ) {
		$this->update_term_cache = $update;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	protected function make_query_object( $model_class = false ) {
		return new FluentQuery( new Posts() );
	}

	/**
	 * @inheritDoc
	 */
	public function get_results() {
		$results = parent::get_results();
		$posts   = $results->toArray();

		update_post_caches( $posts, 'any', $this->update_term_cache, $this->update_meta_cache );

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
		$posts  = $loaded->toArray();

		update_post_caches( $posts, 'any', $this->update_term_cache, $this->update_meta_cache );

		return $loaded;
	}
}