<?php
/**
 * Contains the class for the ForeignModel column type.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Column;

use IronBound\DB\Model;
use IronBound\DB\Saver\ModelSaver;
use IronBound\DB\Table\Column\Contracts\Savable;
use IronBound\DB\Table\ForeignKey\DeleteConstrainable;
use IronBound\DB\Table\Table;
use IronBound\WPEvents\GenericEvent;

/**
 * Class ForeignModel
 * @package IronBound\DB\Table\Column
 */
class ForeignModel extends SimpleForeign implements DeleteConstrainable {

	/**
	 * @var Table
	 */
	protected $foreign_table;

	/**
	 * @var string
	 */
	protected $model_class;

	/**
	 * ForeignModel constructor.
	 *
	 * @param string $name          Column name.
	 * @param string $model_class   FQCN for the model.
	 * @param Table  $foreign_table Table the foreign key resides in.
	 */
	public function __construct( $name, $model_class, Table $foreign_table ) {
		parent::__construct( $name, $foreign_table, $foreign_table->get_primary_key() );

		$this->model_class = $model_class;
	}

	/**
	 * @inheritDoc
	 */
	public function convert_raw_to_value( $raw ) {
		
		if ( empty( $raw ) ) {
			return null;
		}
		
		return call_user_func( array( $this->model_class, 'get' ), $raw );
	}

	/**
	 * @inheritDoc
	 */
	public function prepare_for_storage( $value ) {

		if ( $value instanceof $this->model_class ) {
			return $value->get_pk();
		}

		return $this->get_column()->prepare_for_storage( $value );
	}

	/**
	 * @inheritDoc
	 */
	public function register_delete_callback( $callback ) {
		call_user_func( array( $this->model_class, 'deleting' ), function ( GenericEvent $event ) use ( $callback ) {
			$callback( $event->get_subject()->get_pk(), $event->get_subject() );
		} );
	}
}