<?php
/**
 * HasForeign class definition.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Relations;

use IronBound\DB\Model;
use IronBound\DB\Query\FluentQuery;
use IronBound\DB\Saver\ModelSaver;
use IronBound\DB\Saver\Saver;

/**
 * Class HasForeign
 *
 * @package IronBound\DB\Relations
 */
class HasForeign extends Relation {

	/**
	 * @var string
	 */
	protected $related_primary_key_column;

	/**
	 * HasForeign constructor.
	 *
	 * @param string       $attribute
	 * @param Model        $parent
	 * @param string|Saver $related
	 */
	public function __construct( $attribute, Model $parent, $related ) {

		if ( $related instanceof Saver ) {
			$saver   = $related;
			$related = '';
		} else {
			$saver = new ModelSaver( $related );
		}

		if ( $related ) {
			$this->related_primary_key_column = $related::table()->get_primary_key();
		}

		parent::__construct( $related, $parent, $attribute, $saver );

		$this->cache( false );
	}

	/**
	 * Make a FluentQuery object.
	 *
	 * @since 2.0
	 *
	 * @param bool $model_class Set the Model class if possible.
	 *
	 * @return FluentQuery
	 */
	protected function make_query_object( $model_class = false ) {

		$query = call_user_func( array( $this->related_model, 'query' ) );

		if ( ! $model_class ) {
			$query->set_model_class( null );
		}

		return $query;
	}

	/**
	 * @inheritDoc
	 */
	protected function fetch_results() {

		$class = $this->related_model;

		if ( $this->parent->get_raw_attribute( $this->attribute ) instanceof $class ) {
			return $this->parent->get_raw_attribute( $this->attribute );
		}

		return $this->saver->get_model( $this->parent->get_raw_attribute( $this->attribute ) );
	}

	/**
	 * Fetch results for an eager loading query.
	 *
	 * @since 2.0
	 *
	 * @param array $primary_keys
	 *
	 * @return \Doctrine\Common\Collections\Collection|\IronBound\DB\Collection
	 */
	protected function fetch_results_for_eager_load( $primary_keys ) {

		$query = $this->make_query_object( true );
		$query->where( $this->related_primary_key_column, true, $primary_keys );

		return $query->results( $this->saver );
	}

	/**
	 * @inheritDoc
	 */
	public function eager_load( array $models, $callback = null ) {

		/** @var Model[] $map */
		$map = array();
		$pks = array();

		foreach ( $models as $model ) {

			$pk = $model->get_raw_attribute( $this->attribute );

			// while freshly hydrated models will never have objects as the raw attribute value
			// this is a public API method so let's add safe guards just in case.
			if ( is_object( $pk ) ) {
				$pk = $this->saver->get_pk( $pk );
			}

			if ( ! $pk ) {
				continue;
			}

			$pks[]      = $pk;
			$map[ $pk ] = $model;
		}

		$related_models = $this->fetch_results_for_eager_load( $pks );

		foreach ( $related_models as $related_model ) {
			if ( isset( $map[ $this->saver->get_pk( $related_model ) ] ) ) {
				$mapped = $map[ $this->saver->get_pk( $related_model ) ];
				$mapped->set_raw_attribute( $this->attribute, $related_model );
				$mapped->sync_original_attribute( $this->attribute );
			}
		}

		return $related_models;
	}

	/**
	 * @inheritDoc
	 */
	public function persist( $values ) {
		return $this->saver->save( $values );
	}

	/**
	 * Get the primary key for the related model.
	 *
	 * @since 2.0
	 *
	 * @param object $value
	 *
	 * @return int|string
	 */
	public function get_pk_for_value( $value ) {
		return $this->saver->get_pk( $value );
	}
}