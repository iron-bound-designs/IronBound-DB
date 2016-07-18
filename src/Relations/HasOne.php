<?php
/**
 * HasOne relation class definition.
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
use IronBound\WPEvents\GenericEvent;

/**
 * Class HasOne
 *
 * @package IronBound\DB\Relations
 */
class HasOne extends HasOneOrMany {

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

			if ( $model->get_attribute( $foreign_key )->get_pk() === $parent->get_pk() ) {
				wp_cache_set( $parent->get_pk(), $model->get_pk(), $cache_group );
			}

		} );

		$class::updated( function ( GenericEvent $event ) use ( $self, $foreign_key, $parent, $cache_group ) {

			$from = $event->get_argument( 'from' );

			if ( isset( $from[ $foreign_key ] ) && $from[ $foreign_key ] === $parent->get_pk() ) {
				wp_cache_delete( $parent->get_pk(), $cache_group );
			}
		} );
	}

	/**
	 * @inheritDoc
	 */
	protected function fetch_results() {
		return parent::fetch_results()->first();
	}

	/**
	 * @inheritdoc
	 */
	protected function apply_scopes_for_fetch( FluentQuery $query ) {
		$query->take( 1 );
	}

	/**
	 * @inheritDoc
	 */
	protected function wrap_eager_loaded_results( $results ) {

		if ( empty( $results ) ) {
			return null;
		}

		return reset( $results );
	}

	/**
	 * @inheritDoc
	 */
	public function persist( $values ) {
		$values->set_attribute( $this->foreign_key, $this->parent->get_pk() )->save();

		return $values;
	}
}