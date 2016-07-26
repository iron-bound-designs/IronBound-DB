<?php
/**
 * Contains the class definition for ManyToManyTerms
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
use IronBound\DB\Table\Association\TermAssociationTable;
use IronBound\DB\WP\Terms;
use IronBound\DB\WP\TermTaxonomy;

/**
 * Class ManyToManyTerms
 *
 * @package IronBound\DB\Relations
 */
class ManyToManyTerms extends ManyToMany {

	/**
	 * @var bool
	 */
	protected $update_meta_cache = true;

	/**
	 * @inheritDoc
	 */
	public function __construct( Model $parent, TermAssociationTable $association, $attribute ) {
		parent::__construct( '', $parent, $association, $attribute );

		$this->join_on = 'term_id';
	}

	/**
	 * Update the term meta cache when loading this relation.
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
		$query = new FluentQuery( new Terms() );
		$query->join( new TermTaxonomy(), 'term_id', 'term_taxonomy_id' );

		return $query;
	}

	/**
	 * @inheritDoc
	 */
	public function get_results() {
		$results = parent::get_results();
		$terms   = $results->toArray();

		update_term_cache( $terms );

		if ( $this->update_meta_cache ) {
			$ids = array_map( function ( $term ) {
				return $term->term_id;
			}, $terms );

			update_meta_cache( 'term', $ids );
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
		$terms  = $loaded->toArray();

		update_term_cache( $terms );

		if ( $this->update_meta_cache ) {
			$ids = array_map( function ( $term ) {
				return $term->term_id;
			}, $terms );

			update_meta_cache( 'term', $ids );
		}

		return $loaded;
	}
}