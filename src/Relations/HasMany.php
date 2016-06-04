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

use IronBound\DB\Collections\Collection;
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
	protected function register_events() {

		$class = $this->related_model;

		$foreign_key = $this->foreign_key;
		$self        = $this;
		$results     = $this->results;

		$class::saved( function ( GenericEvent $event ) use ( $self, $results, $foreign_key ) {

			if ( $event->get_subject()->get_attribute( $foreign_key )->get_pk() === $self->parent->get_pk() ) {
				if ( ! $results->contains( $event->get_subject() ) ) {
					$results->add( $event->get_subject() );
				}
			} else {
				$results->remove_model( $event->get_subject()->get_pk() );
			}

		} );

		$class::deleted( function ( GenericEvent $event ) use ( $self, $results ) {
			$results->remove_model( $event->get_subject()->get_pk() );
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
	protected function wrap_eager_loaded_results( $results ) {
		return new Collection( $results ?: array() );
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