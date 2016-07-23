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

use IronBound\DB\Collection;
use IronBound\DB\Model;
use IronBound\DB\Saver\ModelSaver;
use IronBound\DB\Saver\Saver;
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
	 * @var bool
	 */
	protected $cache;

	/**
	 * @var array
	 */
	private $registered_cache_events = array();

	/**
	 * @var string
	 */
	protected $attribute;

	/**
	 * @var Saver
	 */
	protected $saver;

	/**
	 * Relation constructor.
	 *
	 * @param string $related Class name of the related model.
	 * @param Model  $parent
	 * @param string $attribute
	 * @param Saver  $saver
	 */
	public function __construct( $related, Model $parent, $attribute, Saver $saver = null ) {

		if ( ! empty( $related ) && ! is_subclass_of( $related, 'IronBound\DB\Model' ) ) {
			throw new \InvalidArgumentException( '$related must be a subclass of IronBound\DB\Model' );
		}

		$this->related_model = $related;
		$this->parent        = $parent;
		$this->attribute     = $attribute;

		if ( $saver ) {
			$this->saver = $saver;
		} elseif ( $related ) {
			$this->saver = new ModelSaver( $related );
		} else {
			$this->saver = new ModelSaver();
		}

		$this->cache = (bool) $parent::get_event_dispatcher();
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
	 * Cache the relation results.
	 *
	 * Defaults to true.
	 *
	 * @since 2.0
	 *
	 * @param bool $cache
	 *
	 * @return $this
	 */
	public function cache( $cache = true ) {
		$this->cache = $cache;

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

		if ( ( $results = $this->load_from_cache( $this->parent ) ) === null ) {
			$results = $this->fetch_results();

			if ( $this->cache ) {
				$this->cache_results( $results, $this->parent );
				$this->maybe_register_cache_events();
			}
		}

		if ( $this->keep_synced && $results instanceof Collection ) {
			$this->register_events( $results );
		}

		return $results;
	}

	/**
	 * Load the results of a relation from the cache.
	 *
	 * @since 2.0
	 *
	 * @param Model $model
	 *
	 * @return Collection|Model|mixed|null
	 */
	protected function load_from_cache( Model $model ) {

		$cached = wp_cache_get( $model->get_pk(), $this->get_cache_group() );

		if ( $cached === false ) {
			return null;
		}

		if ( is_array( $cached ) ) {
			return $this->load_collection_from_cache( $cached, $model );
		} else {
			return $this->load_single_from_cache( $cached );
		}
	}

	/**
	 * Load a collection of models from the cache.
	 *
	 * @since 2.0
	 *
	 * @param int[]|string[] $cached
	 * @param Model          $for
	 *
	 * @return Collection
	 */
	protected function load_collection_from_cache( array $cached, Model $for ) {

		$models  = array();
		$removed = array();

		foreach ( $cached as $id ) {
			$model = $this->saver->get_model( $id );

			if ( $model ) {
				$models[ $id ] = $model;
			} else {
				$removed[] = $id;
			}
		}

		$diff = array_diff( $cached, $removed );
		wp_cache_set( $for->get_pk(), $diff, $this->get_cache_group() );

		return new Collection( $models, false, $this->saver );
	}

	/**
	 * Load a single model from the cache.
	 *
	 * @since 2.0
	 *
	 * @param int|string $cached
	 *
	 * @return object
	 */
	protected function load_single_from_cache( $cached ) {
		return $this->saver->get_model( $cached );
	}

	/**
	 * Cache the results of a relation.
	 *
	 * @since 2.0
	 *
	 * @param Collection|Model|mixed $results
	 * @param Model                  $model
	 */
	protected function cache_results( $results, Model $model ) {

		if ( $results instanceof Collection ) {
			$this->cache_collection( $results, $model );
		} elseif ( is_object( $results ) ) {
			$this->cache_single( $results, $model );
		}
	}

	/**
	 * Cache a collection of results.
	 *
	 * @since 2.0
	 *
	 * @param Collection $collection
	 * @param Model      $model
	 */
	protected function cache_collection( Collection $collection, Model $model ) {

		$ids = array_map( function ( $e ) use ( $collection ) {
			return $collection->get_saver()->get_pk( $e );
		}, $collection->toArray() );

		wp_cache_set( $model->get_pk(), $ids, $this->get_cache_group() );
	}

	/**
	 * Cache a single result.
	 *
	 * @since 2.0
	 *
	 * @param Model|mixed $result
	 * @param Model       $model
	 */
	protected function cache_single( $result, Model $model ) {

		$id = $this->saver->get_pk( $result );

		wp_cache_set( $model->get_pk(), $id, $this->get_cache_group() );
	}

	/**
	 * Maybe register cache events.
	 *
	 * Cache events should only be registered once per relation.
	 *
	 * @since 2.o
	 */
	private function maybe_register_cache_events() {

		$key = get_class( $this );
		$key .= "-{$this->attribute}";

		if ( isset( $this->registered_cache_events[ $key ] ) ) {
			return;
		}

		$this->register_cache_events();

		$this->registered_cache_events[ $key ] = true;
	}

	/**
	 * Register cache busting events.
	 *
	 * @since 2.0
	 */
	protected function register_cache_events() {

	}

	/**
	 * Get the cache group name.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	final protected function get_cache_group() {
		return "relation:{$this->parent->table()->get_slug()}/{$this->attribute}";
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
	 *
	 * @param Collection $results
	 */
	protected function register_events( Collection $results ) {

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
	 * @param Collection|Model|mixed $values
	 *
	 * @return Collection|Model|mixed Saved values.
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
		wp_cache_delete( $model->get_pk(), $this->get_cache_group() );
	}
}