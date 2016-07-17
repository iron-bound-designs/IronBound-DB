<?php
/**
 * HasForeignTerm class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Relations;

use IronBound\DB\Model;
use IronBound\DB\Query\FluentQuery;
use IronBound\DB\Saver\TermSaver;
use IronBound\DB\WP\Terms;
use IronBound\DB\WP\TermTaxonomy;

/**
 * Class HasForeignTerm
 *
 * @package IronBound\DB\Relations
 */
class HasForeignTerm extends HasForeign {

	/**
	 * @var bool
	 */
	protected $update_meta_cache = true;

	/**
	 * @inheritDoc
	 */
	public function __construct( $attribute, Model $parent ) {
		parent::__construct( $attribute, $parent, new TermSaver() );

		$this->related_primary_key_column = 'term_id';
	}

	/**
	 * @inheritDoc
	 */
	protected function make_query_object( $model_class = false ) {
		return new FluentQuery( new Terms() );
	}

	/**
	 * Update the term meta cache when loading this relation.
	 *
	 * By default, the meta cache IS updated.
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
		$term = parent::get_results();

		if ( ! $term ) {
			return $term;
		}

		$terms = array( $term );

		update_term_cache( $terms );

		if ( $this->update_meta_cache ) {
			update_meta_cache( 'term', $term->term_id );
		}

		return $term;
	}

	/**
	 * @inheritDoc
	 */
	protected function fetch_results_for_eager_load( $primary_keys ) {

		$query = $this->make_query_object( true );
		$query->where( $this->related_primary_key_column, true, $primary_keys );

		$query->join( new TermTaxonomy(), 'term_id', 'term_taxonomy_id' );
		$query->select_all( false );

		return $query->results( $this->saver );
	}

	/**
	 * @inheritDoc
	 */
	public function eager_load( array $models, $callback = null ) {
		$loaded = parent::eager_load( $models, $callback );
		$terms  = $loaded->toArray();

		update_term_cache( $terms );

		if ( $this->update_meta_cache ) {
			$term_ids = wp_list_pluck( $terms, 'term_id' );
			update_meta_cache( 'term', $term_ids );
		}

		return $loaded;
	}
}