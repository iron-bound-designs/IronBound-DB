<?php
/**
 * HasOneOrMany relation base class.
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

/**
 * Class HasOneOrMany
 *
 * @package IronBound\DB\Relations
 */
abstract class HasOneOrMany extends Relation {

	/**
	 * @var string
	 */
	protected $foreign_key;

	/**
	 * Relation constructor.
	 *
	 * @param string $foreign_key   Foreign key that references the $parent model.
	 * @param string $related_model Class name of the related model.
	 * @param Model  $parent
	 * @param string $attribute
	 */
	public function __construct( $foreign_key, $related_model, Model $parent, $attribute ) {
		parent::__construct( $related_model, $parent, $attribute );

		$this->foreign_key = $foreign_key;
	}

	/**
	 * @inheritDoc
	 */
	protected function fetch_results() {

		$related = $this->related_model;

		$query = $related::query()->where( $this->foreign_key, true, $this->parent->get_pk() );

		$this->apply_scopes_for_fetch( $query );

		return $query->results();
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

		$cached = array();

		if ( $callback === null ) {
			foreach ( $models as $pk => $model ) {
				$result = $this->load_from_cache( $model );

				if ( $result ) {
					$cached[ $pk ] = $result instanceof Collection ? $result->toArray() : array( $result );
				}
			}

			if ( count( $cached ) === count( $models ) ) {
				return new Collection( $this->flatten( $cached ), false, $this->saver );
			}
		}

		/** @var FluentQuery $query */
		$query = call_user_func( array( $this->related_model, 'query' ) );

		$pks = array_keys( $models );
		$pks = array_diff( $pks, array_keys( $cached ) );
		$pks = array_filter( $pks );

		if ( count( $pks ) === 0 ) {
			return new Collection( array(), false, $this->saver );
		}

		$query->where( $this->foreign_key, true, $pks );

		$this->apply_scopes_for_eager_load( $query );

		if ( $callback ) {
			$callback( $query );
		}

		$results = $query->results()->toArray();
		$cached  = $this->flatten( $cached );

		return new Collection( array_merge( $results, $cached ), false, $this->saver );
	}

	/**
	 * Recursively flatten an array.
	 *
	 * @since 2.0
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	private function flatten( $array ) {
		if ( ! is_array( $array ) ) {
			// nothing to do if it's not an array
			return array( $array );
		}

		$result = array();
		foreach ( $array as $value ) {
			// explode the sub-array, and add the parts
			$result = array_merge( $result, $this->flatten( $value ) );
		}

		return $result;
	}

	/**
	 * Apply scopes to the fetch results query.
	 *
	 * @since 2.0
	 *
	 * @param FluentQuery $query
	 */
	protected function apply_scopes_for_fetch( FluentQuery $query ) {

	}

	/**
	 * Apply scopes to the eager load query.
	 *
	 * @since 2.0
	 *
	 * @param FluentQuery $query
	 */
	protected function apply_scopes_for_eager_load( FluentQuery $query ) {

	}

	/**
	 * Build an eager load map.
	 *
	 * @since 2.0
	 *
	 * @param Collection $models
	 *
	 * @return array
	 */
	protected function build_eager_load_map( $models ) {

		$map = array();

		$foreign = $this->foreign_key;

		foreach ( $models as $model ) {
			$pk = $model->get_raw_attribute( $foreign );

			if ( $pk instanceof Model ) {
				$pk = $pk->get_pk();
			}

			$map[ $pk ][ $model->get_pk() ] = $model;
		}

		return $map;
	}

	/**
	 * @inheritDoc
	 */
	public function eager_load( array $models, $callback = null ) {

		$results = $this->fetch_results_for_eager_load( $models, $callback );

		$map = $this->build_eager_load_map( $results );

		/** @var Model $model */
		foreach ( $models as $model ) {

			if ( isset( $map[ $model->get_pk() ] ) ) {
				$related = $map[ $model->get_pk() ];

				$model->set_relation_value( $this->attribute, $this->wrap_eager_loaded_results( $related ) );
			} else {
				$model->set_relation_value( $this->attribute, $this->wrap_eager_loaded_results( null ) );
			}
		}

		return $results;
	}

	/**
	 * Wrap the eager loaded results before setting them on the Model.
	 *
	 * @since 2.0
	 *
	 * @param mixed $results
	 *
	 * @return mixed
	 */
	protected abstract function wrap_eager_loaded_results( $results );
}