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

use IronBound\DB\Collection;
use IronBound\DB\Model;
use IronBound\WPEvents\GenericEvent;

/**
 * Class HasMany
 *
 * @package IronBound\DB\Relations
 */
class HasMany extends HasOneOrMany {

	/**
	 * @inheritdoc
	 */
	protected function register_events( Collection $results ) {

		$class = $this->related_model;

		$foreign_key = $this->foreign_key;
		$parent      = $this->parent;
		$self        = $this;

		$class::saved( function ( GenericEvent $event ) use ( $self, $results, $foreign_key, $parent ) {

			/** @var Model $model */
			$model = $event->get_subject();

			if ( ( $foreign = $model->get_attribute( $foreign_key ) ) && $foreign->get_pk() === $parent->get_pk() ) {
				if ( ! $results->contains( $event->get_subject() ) ) {
					$results->add( $event->get_subject() );
				}
			} else {
				$results->remove_model( $event->get_subject()->get_pk() );
			}

		} );

		$class::deleted( function ( GenericEvent $event ) use ( $results ) {
			$results->remove_model( $event->get_subject()->get_pk() );
		} );
	}

	/**
	 * @inheritDoc
	 */
	protected function register_cache_events() {
		parent::register_cache_events();

		$class = $this->related_model;

		$foreign_key = $this->foreign_key;
		$parent      = $this->parent;
		$self        = $this;
		$cache_group = $this->get_cache_group();

		$class::saved( function ( GenericEvent $event ) use ( $self, $foreign_key, $parent, $cache_group ) {

			/** @var Model $model */
			$model = $event->get_subject();

			$foreign_key = $model->get_raw_attribute( $foreign_key );

			if ( $foreign_key instanceof Model ) {
				$foreign_key = $foreign_key->get_pk();
			}

			if ( $foreign_key === $parent->get_pk() ) {
				$ids   = wp_cache_get( $parent->get_pk(), $cache_group );
				$ids[] = $model->get_pk();
				wp_cache_set( $parent->get_pk(), $ids, $cache_group );
			}

		} );

		$class::updated( function ( GenericEvent $event ) use ( $self, $foreign_key, $parent, $cache_group ) {

			/** @var Model $model */
			$model = $event->get_subject();
			$from  = $event->get_argument( 'from' );

			if ( isset( $from[ $foreign_key ] ) && $from[ $foreign_key ] == $parent->get_pk() ) {
				$ids = wp_cache_get( $parent->get_pk(), $cache_group );

				$i = array_search( $model->get_pk(), $ids );

				if ( $i !== false ) {
					unset( $ids[ $i ] );
					wp_cache_set( $parent->get_pk(), $ids, $cache_group );
				}
			}
		} );
	}

	/**
	 * @inheritDoc
	 */
	protected function fetch_results() {

		$results = parent::fetch_results();
		$results->keep_memory();

		return $results;
	}

	/**
	 * @inheritDoc
	 */
	protected function load_collection_from_cache( array $cached, Model $for ) {

		$collection = parent::load_collection_from_cache( $cached, $for );
		$collection->keep_memory();

		return $collection;
	}

	/**
	 * @inheritDoc
	 */
	protected function wrap_eager_loaded_results( $results ) {
		return new Collection( $results ?: array(), true );
	}

	/**
	 * @inheritDoc
	 */
	public function persist( $values ) {

		foreach ( $values->get_removed() as $removed ) {
			$removed->set_attribute( $this->foreign_key, null )->save();
		}

		/** @var Model $value */
		foreach ( $values as $value ) {
			$value->set_attribute( $this->foreign_key, $this->parent->get_pk() )->save();
		}

		return $values;
	}
}