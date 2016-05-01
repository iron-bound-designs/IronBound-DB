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

use Doctrine\Common\Collections\Collection;
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
	 * Relation constructor.
	 *
	 * @param string $related Class name of the related model.
	 * @param Model  $parent
	 */
	public function __construct( $related, Model $parent ) {

		if ( ! is_subclass_of( $related, 'IronBound\DB\Model' ) ) {
			throw new \InvalidArgumentException( '$related must be a subclass of IronBound\DB\Model' );
		}

		$this->related_model = $related;
		$this->parent        = $parent;
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
	 * @return mixed
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
	protected function register_events() {

		$class = $this->related_model;

		$self    = $this;
		$results = $this->results;

		$class::saved( function ( GenericEvent $event ) use ( $self, $results ) {

			if ( $self->model_matches_relation( $event->get_subject() ) ) {
				$results->set( $event->get_subject()->get_pk(), $event->get_subject() );
			} else {
				$results->remove( $event->get_subject()->get_pk() );
			}

		} );

		$class::deleted( function ( GenericEvent $event ) use ( $self, $results ) {
			$results->remove( $event->get_subject()->get_pk() );
		} );
	}

	/**
	 * Fetch results from the database.
	 *
	 * @since 2.0
	 *
	 * @return mixed
	 */
	protected abstract function fetch_results();

	/**
	 * Evaluate whether a given model matches this relation.
	 *
	 * @since 2.0
	 *
	 * @param Model $model
	 *
	 * @return bool
	 */
	public abstract function model_matches_relation( Model $model );

	/**
	 * Eager-load a relation on a set of models.
	 *
	 * @since 2.0
	 *
	 * @param Model[]  $models   Array of models to eager-load. Keyed by their primary key.
	 * @param string   $attribute
	 * @param callable $callback Called with the FluentQuery object to customize the relations loaded.
	 *
	 * @return $this
	 */
	public abstract function eager_load( array $models, $attribute, $callback = null );

	/**
	 * Persist the results of a relation.
	 *
	 * @since 2.0
	 *
	 * @param Collection|Model $values
	 */
	public abstract function persist( $values );
}