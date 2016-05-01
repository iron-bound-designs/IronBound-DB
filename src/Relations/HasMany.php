<?php
/**
 * HasMany relation.
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
use IronBound\DB\Query\Simple_Query;

/**
 * Class HasMany
 * @package IronBound\DB\Relations
 */
class HasMany extends Relation {

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
	 */
	public function __construct( $foreign_key, $related_model, Model $parent ) {
		parent::__construct( $related_model, $parent );

		$this->foreign_key = $foreign_key;
	}

	/**
	 * @inheritDoc
	 */
	protected function fetch_results() {

		$related = $this->related_model;

		return $related::query()->where( $this->foreign_key, true, $this->parent->get_pk() )->results();
	}

	/**
	 * @inheritDoc
	 */
	public function model_matches_relation( Model $model ) {
		return $model->get_attribute( $this->foreign_key )->get_pk() === $this->parent->get_pk();
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

		$relation_model = $this->related_model;

		/** @var FluentQuery $query */
		$query = $relation_model::query();
		$query->where( $this->foreign_key, true, array_keys( $models ) );

		if ( $callback ) {
			$callback( $query );
		}

		return $query->results();
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
			$map[ $model->get_attribute( $foreign )->get_pk() ][ $model->get_pk() ] = $model;
		}

		return $map;
	}

	/**
	 * @inheritDoc
	 */
	public function eager_load( array $models, $attribute, $callback = null ) {

		$results = $this->fetch_results_for_eager_load( $models, $callback );

		$map = $this->build_eager_load_map( $results );

		/** @var Model $model */
		foreach ( $models as $model ) {

			if ( isset( $map[ $model->get_pk() ] ) ) {
				$related = $map[ $model->get_pk() ];

				$model->set_relation_value( $attribute, new ArrayCollection( $related ) );
			}
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function persist( $values ) {

		/** @var Model $value */
		foreach ( $values as $value ) {
			$value->set_attribute( $this->foreign_key, $this->parent->get_pk() )->save();
		}
	}
}