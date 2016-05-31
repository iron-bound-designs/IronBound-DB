<?php
/**
 * BelongsToMany class definition.
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
 * Class BelongsToMany
 *
 * @package IronBound\DB\Relations
 */
class BelongsToMany extends Relation {

	/**
	 * @var Saver
	 */
	protected $saver;

	/**
	 * @var string
	 */
	protected $related_primary_key_column;

	/**
	 * BelongsToMany constructor.
	 *
	 * @param string       $attribute
	 * @param Model        $parent
	 * @param string|Saver $related
	 */
	public function __construct( $attribute, Model $parent, $related ) {

		if ( $related instanceof Saver ) {
			$this->saver = $related;
			$related     = '';
		} else {
			$this->saver = new ModelSaver( $related );
		}

		if ( $related ) {
			$this->related_primary_key_column = $related::table()->get_primary_key();
		}

		parent::__construct( $related, $parent, $attribute );
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

		if ( $model_class && $this->related_model ) {
			return call_user_func( array( $this->related_model, 'query' ) );
		} else {
			return new FluentQuery( call_user_func( array( $this->related_model, 'table' ) ) );
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function register_events() {
		// this is Single model result, so we don't keep it in sync with another collection
	}

	/**
	 * @inheritDoc
	 */
	protected function fetch_results() {

		$class = $this->related_model;

		if ( $this->parent->get_raw_attribute( $this->attribute ) instanceof $class ) {
			return $class;
		}

		return $class::get( $this->parent->get_raw_attribute( $this->attribute ) );
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

		$query = $this->make_query_object( $this->related_model );
		$query->where( $this->related_primary_key_column, true, $pks );

		$related_models = $query->results( $this->saver );

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
		$values->save();
	}
}