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
use IronBound\DB\Model;
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
	 * @param string       $foreign_key   Foreign key that references the $parent model.
	 * @param string       $related_model Class name of the related model.
	 * @param Simple_Query $query
	 * @param Model        $parent
	 */
	public function __construct( $foreign_key, $related_model, Simple_Query $query, Model $parent ) {
		parent::__construct( $related_model, $query, $parent );

		$this->foreign_key = $foreign_key;
	}

	/**
	 * @inheritDoc
	 */
	protected function fetch_results() {

		$model_class = $this->related_model;

		$models = array();

		$results = $this->query->get_many_by( $this->foreign_key, $this->parent->get_pk() );

		foreach ( $results as $result ) {

			$model = $model_class::from_query( (array) $result );

			$models[ $model->get_pk() ] = $model;
		}

		return new ArrayCollection( $models );
	}

	/**
	 * @inheritDoc
	 */
	protected function model_matches_relation( Model $model ) {
		return $model->get_attribute( $this->foreign_key )->get_pk() === $this->parent->get_pk();
	}
}