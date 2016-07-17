<?php
/**
 * Contains the ModelSaver class definition.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Saver;

/**
 * Class ModelSaver
 *
 * @package IronBound\DB\Saver
 */
class ModelSaver extends Saver {

	/**
	 * @var string
	 */
	protected $model_class;

	/**
	 * ModelSaver constructor.
	 *
	 * @param $model_class
	 */
	public function __construct( $model_class = 'IronBound\DB\Model' ) {
		$this->model_class = $model_class;
	}

	/**
	 * Set the model class used for validation.
	 *
	 * @since 2.0
	 *
	 * @param string $model_class
	 */
	public function set_model_class( $model_class ) {
		$this->model_class = $model_class;
	}

	/**
	 * @inheritDoc
	 */
	public function get_pk( $value ) {
		return $value->get_pk();
	}

	/**
	 * @inheritDoc
	 */
	public function get_model( $pk ) {
		return call_user_func( array( $this->model_class, 'get' ), $pk );
	}

	/**
	 * @inheritDoc
	 */
	public function make_model( $attributes ) {
		return call_user_func( array( $this->model_class, 'from_query' ), $attributes );
	}

	/**
	 * @inheritDoc
	 */
	public function save( $value, array $options = array() ) {

		if ( ! $value instanceof $this->model_class ) {
			throw new \InvalidArgumentException( sprintf(
				'ForeignModel column can only save %s objects, %s given.', $this->model_class,
				is_object( $value ) ? get_class( $value ) : gettype( $value )
			) );
		}

		$value->save( $options );

		return $value;
	}
}