<?php
/**
 * ManyToMany relation.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Relations;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use IronBound\DB\Model;
use IronBound\DB\Query\FluentQuery;
use IronBound\DB\Table\AssociationTable;
use IronBound\DB\Table\Table;

/**
 * Class ManyToMany
 * @package IronBound\DB\Relations
 */
class ManyToMany extends Relation {

	/**
	 * @var AssociationTable
	 */
	protected $association;

	/**
	 * @var string
	 */
	protected $other_column;

	/**
	 * @var string
	 */
	protected $primary_column;

	/**
	 * ManyToMany constructor.
	 *
	 * @param string           $related Related class name.
	 * @param Model            $parent  Parent object.
	 * @param AssociationTable $association
	 */
	public function __construct( $related, Model $parent, AssociationTable $association ) {
		parent::__construct( $related, $parent );

		$this->association = $association;

		/** @var Table $related_table */
		$related_table = $related::table();

		if ( $related_table->get_slug() === $association->get_table_a()->get_slug() ) {
			$this->other_column   = $association->get_col_b();
			$this->primary_column = $association->get_col_a();
		} else {
			$this->other_column   = $association->get_col_a();
			$this->primary_column = $association->get_col_b();
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function fetch_results() {

		/** @var FluentQuery $query */
		$query = call_user_func( array( $this->related_model, 'query' ) );
		$query->distinct();

		$related = $this->related_model;
		$parent  = $this->parent;
		$column  = $this->other_column;

		$query->join( $this->association, $related::table()->get_primary_key(), $this->primary_column, '=',
			function ( FluentQuery $query ) use ( $parent, $column ) {
				$query->where( $column, true, $parent->get_pk() );
			} );

		return $query->results();
	}

	/**
	 * @inheritDoc
	 */
	public function model_matches_relation( Model $model ) {

		$query = new FluentQuery( $GLOBALS['wpdb'], $this->association );
		$query->where( $this->primary_column, true, $this->parent->get_pk() );
		$query->and_where( $this->other_column, true, $model->get_pk() );

		return ! is_null( $query->first() );
	}

	/**
	 * Fetch results for eager loading.
	 *
	 * @since 2.0
	 *
	 * @param Model[]  $models
	 * @param callable $callback
	 *
	 * @return Collection
	 */
	protected function fetch_results_for_eager_load( array $models, $callback = null ) {

		$related      = $this->related_model;
		$other_column = $this->other_column;

		$query = new FluentQuery( $GLOBALS['wpdb'], $related::table() );
		$query->distinct();
		$query->select_all( false );

		$query->join( $this->association, $related::table()->get_primary_key(), $this->primary_column, '=',
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
	public function eager_load( array $models, $attribute, $callback = null ) {

		$results = $this->fetch_results_for_eager_load( $models, $callback );

		$related = array();

		$relationship_map = array();

		foreach ( $results as $result ) {

			$attributes = $result;
			unset( $attributes[ $this->primary_column ] );
			unset( $attributes[ $this->other_column ] );

			$model = call_user_func( array( $this->related_model, 'from_query' ), $attributes );

			$related[ $model->get_pk() ] = $model;

			$relationship_map[ $result[ $this->other_column ] ][ $model->get_pk() ] = $model;
		}

		foreach ( $models as $model ) {

			if ( isset( $relationship_map[ $model->get_pk() ] ) ) {
				$model->set_relation_value( $attribute, new ArrayCollection( $relationship_map[ $model->get_pk() ] ) );
			} else {
				$model->set_relation_value( $attribute, new ArrayCollection() );
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function persist( $values ) {

		/** @var \wpdb $wpdb */
		global $wpdb;

		if ( $this->parent->get_pk() ) {
			$wpdb->delete( $this->association->get_table_name( $wpdb ), array(
				$this->other_column => $this->parent->get_pk()
			) );
		}

		$insert = array();

		foreach ( $values as $value ) {
			$value->save();
			$insert[] = "({$this->parent->get_pk()},{$value->get_pk()})";
		}

		if ( empty( $insert ) ) {
			return;
		}

		$insert = implode( ',', $insert );

		$sql = "INSERT IGNORE INTO `{$this->association->get_table_name( $wpdb )}` ";
		$sql .= "({$this->other_column},{$this->primary_column}) VALUES $insert";

		$wpdb->query( $sql );
	}
}