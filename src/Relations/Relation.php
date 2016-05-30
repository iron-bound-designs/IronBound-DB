<?php
/**
 * Contains the abstract Relation class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Relations;

use IronBound\DB\Collections\Collection;
use IronBound\DB\Model;
use IronBound\WPEvents\GenericEvent;

/**
 * Class Relation
 * @package IronBound\DB\Relations
 */
abstract class Relation {

	/**
	 * @var string
	 */
	protected $related_model;

	/**
	 * @var Model
	 */
	protected $parent;

	/**
	 * @var bool
	 */
	protected $keep_synced = false;

	/**
	 * Results will only be cached here if they are a Collection.
	 *
	 * @var Collection
	 */
	protected $results;

	/**
	 * @var string
	 */
	protected $attribute;

	/**
	 * Relation constructor.
	 *
	 * @param string $related Class name of the related model.
	 * @param Model  $parent
	 * @param string $attribute
	 */
	public function __construct( $related, Model $parent, $attribute ) {

		if ( ! empty( $related ) && ! is_subclass_of( $related, 'IronBound\DB\Model' ) ) {
			throw new \InvalidArgumentException( '$related must be a subclass of IronBound\DB\Model' );
		}

		$this->related_model = $related;
		$this->parent        = $parent;
		$this->attribute     = $attribute;
	}

	/**
	 * Keep the result collection synced.
	 *
	 * @since 2.0
	 *
	 * @param bool $keep_synced
	 *
	 * @return $this
	 */
	public function keep_synced( $keep_synced = true ) {
		$this->keep_synced = $keep_synced;

		return $this;
	}

	/**
	 * Get the results of the relation.
	 *
	 * @since 2.0
	 *
	 * @return Collection|Model|mixed
	 */
	public function get_results() {

		$results = $this->fetch_results();

		if ( $this->keep_synced && $results instanceof Collection ) {
			$this->results = $results;
			$this->register_events();
		}

		return $results;
	}

	/**
	 * Get the related model class.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_related_model() {
		return $this->related_model;
	}

	/**
	 * Register model events for keeping the results in sync.
	 *
	 * @since 2.0
	 */
	protected abstract function register_events();

	/**
	 * Fetch results from the database.
	 *
	 * @since 2.0
	 *
	 * @return mixed
	 */
	protected abstract function fetch_results();

	/**
	 * Eager-load a relation on a set of models.
	 *
	 * @since 2.0
	 *
	 * @param Model[]  $models   Array of models to eager-load. Keyed by their primary key.
	 * @param callable $callback Called with the FluentQuery object to customize the relations loaded.
	 *
	 * @return Collection All eager loaded models.
	 */
	public abstract function eager_load( array $models, $callback = null );

	/**
	 * Persist the results of a relation.
	 *
	 * @since 2.0
	 *
	 * @param Collection|Model $values
	 */
	public abstract function persist( $values );

	/**
	 * Called when the parent model is deleted.
	 *
	 * Can be used to perform additional cleanup when a model is deleted,
	 * since the EventDispatcher is not guaranteed to be configured.
	 *
	 * @since 2.0
	 *
	 * @param Model $model
	 */
	public function on_delete( Model $model ) {

	}
}