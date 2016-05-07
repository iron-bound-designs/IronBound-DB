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

use IronBound\DB\Collections\Collection;
use IronBound\DB\Model;
use IronBound\DB\Query\Builder;
use IronBound\DB\Query\Tag\From;
use IronBound\DB\Query\Tag\Join;
use IronBound\DB\Query\Tag\Select;
use IronBound\DB\Query\Tag\Where;
use IronBound\DB\Query\Tag\Where_Raw;
use IronBound\DB\Table\Association\PostAssociationTable;

/**
 * Class ManyToManyPosts
 * @package IronBound\DB\Relations
 */
class ManyToManyPosts extends ManyToMany {

	/**
	 * @inheritDoc
	 */
	public function __construct( Model $parent, PostAssociationTable $association, $attribute ) {
		parent::__construct( '', $parent, $association, $attribute );
	}

	/**
	 * @inheritDoc
	 */
	protected function fetch_results() {

		/** @var \wpdb $wpdb */
		global $wpdb;

		$builder = new Builder();

		$select = new Select( 't1.*' );
		$select->filter_distinct();

		$from = new From( $wpdb->posts, 't1' );

		$join_where = new Where_Raw( "t1.ID = t2.{$this->primary_column}" );
		$join_where->qAnd( new Where( "t2.{$this->other_column}", true, $this->parent->get_pk() ) );

		$join = new Join( new From( $this->association->get_table_name( $wpdb ), 't2' ), $join_where );

		$builder->append( $select )->append( $from )->append( $join );
		$sql = $builder->build();

		$results = $wpdb->get_results( $sql, ARRAY_A );

		$posts = array();

		foreach ( $results as $result ) {
			$posts[ $result['ID'] ] = $this->make_model_from_attributes( $result );
		}

		return new Collection( $posts, true, $this->association->get_saver() );
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

		/** @var \wpdb $wpdb */
		global $wpdb;

		$builder = new Builder();

		$select = new Select();
		$select->filter_distinct();

		$from = new From( $wpdb->posts, 't1' );

		$join_where = new Where_Raw( "t1.ID = t2.{$this->primary_column}" );
		$join_where->qAnd( new Where( "t2.{$this->other_column}", true, array_keys( $models ) ) );

		$join = new Join( new From( $this->association->get_table_name( $wpdb ), 't2' ), $join_where, 'LEFT' );

		$builder->append( $select )->append( $from )->append( $join );
		$sql = $builder->build();

		return new Collection( $wpdb->get_results( $sql, ARRAY_A ) );
	}

	/**
	 * @inheritDoc
	 */
	protected function make_model_from_attributes( $attributes ) {
		return new \WP_Post( (object) $attributes );
	}
}