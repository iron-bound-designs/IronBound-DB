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
use IronBound\DB\Query\Simple_Query;
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
	 * @var Simple_Query
	 */
	protected $query;

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
	 * @param string       $related Class name of the related model.
	 * @param Simple_Query $query
	 * @param Model        $parent
	 */
	public function __construct( $related, Simple_Query $query, Model $parent ) {

		if ( ! is_subclass_of( $related, 'IronBound\DB\Model' ) ) {
			throw new \InvalidArgumentException( '$related must be a subclass of IronBound\DB\Model' );
		}

		$this->related_model = $related;
		$this->query         = $query;
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
	 * Register model events for keeping the results in sync.
	 *
	 * @since 2.0
	 */
	protected function register_events() {

		$class = $this->related_model;

		$self = $this;

		$class::saved( function ( GenericEvent $event ) use ( $self ) {

			if ( $self->model_matches_relation( $event->get_subject() ) ) {
				$self->results->set( $event->get_subject()->get_pk(), $event->get_subject() );
			} else {
				$self->results->remove( $event->get_subject()->get_pk() );
			}

		} );

		$class::deleted( function ( GenericEvent $event ) use ( $self ) {
			$self->results->remove( $event->get_subject()->get_pk() );
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
	protected abstract function model_matches_relation( Model $model );
}