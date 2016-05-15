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

use Doctrine\Common\Collections\ArrayCollection;
use IronBound\DB\Collections\Collection;
use IronBound\DB\Model;
use IronBound\DB\Query\Builder;
use IronBound\DB\Query\FluentQuery;
use IronBound\DB\Query\Tag\From;
use IronBound\DB\Query\Tag\Join;
use IronBound\DB\Query\Tag\Select;
use IronBound\DB\Query\Tag\Where;
use IronBound\DB\Query\Tag\Where_Raw;
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
	protected function fetch_results() {

		$query = new FluentQuery( new Posts() );
		$query->distinct();

		$parent = $this->parent;
		$column = $this->other_column;

		$query->join( $this->association, 'ID', $this->primary_column, '=',
			function ( FluentQuery $query ) use ( $parent, $column ) {
				$query->where( $column, true, $parent->get_pk() );
			} );

		$results = $query->results();

		$posts = array();

		foreach ( $results as $result ) {
			$posts[ $result['ID'] ] = $this->make_model_from_attributes( $result );
		}

		return new Collection( $posts, true, $this->association->get_saver() );
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
	protected function register_events() {
		// no-op there is no corresponding model to keep synced
	}

	/**
	 * @inheritDoc
	 */
	protected function fetch_results_for_eager_load( array $models, $callback = null ) {

		$query = new FluentQuery( new Posts() );
		$query->distinct();
		$query->select_all( false );

		$other_column = $this->other_column;

		$query->join( $this->association, 'ID', $this->primary_column, '=',
			function ( FluentQuery $query ) use ( $other_column, $models ) {
				$query->where( $other_column, true, array_keys( $models ) );
			}, 'LEFT' );

		if ( $callback ) {
			$callback( $query );
		}

		return $query->results();
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

	/**
	 * @inheritDoc
	 */
	protected function make_model_from_attributes( $attributes ) {
		return new \WP_Post( (object) $attributes );
	}
}