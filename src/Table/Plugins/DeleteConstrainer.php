<?php
/**
 * Contains the definition for the DeleteConstrainer class plugin.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Plugins;

use IronBound\DB\Exception\DeleteRestrictedException;
use IronBound\DB\Manager;
use IronBound\DB\Query\FluentQuery;
use IronBound\DB\Table\ForeignKey\DeleteConstrainable;
use IronBound\DB\Table\ForeignKey\DeleteConstrained;
use IronBound\DB\Table\Table;

/**
 * Class DeleteConstrainer
 * @package IronBound\DB\Table\Plugins
 */
class DeleteConstrainer implements Plugin {

	/**
	 * @inheritDoc
	 */
	public function accepts( Table $table ) {
		return $table instanceof DeleteConstrained;
	}

	/**
	 * @inheritDoc
	 * @throws \InvalidArgumentException
	 * @throws \UnexpectedValueException
	 */
	public function registered( Table $table ) {

		if ( ! $table instanceof DeleteConstrained ) {
			throw new \InvalidArgumentException(
				'DeleteConstrainer can only process tables implementing DeleteConstrained.'
			);
		}

		if ( ! ( $model = Manager::get_model( $table ) ) ) {
			throw new \InvalidArgumentException(
				'Table must be registered with a model class to be DeleteConstrained.'
			);
		}

		$columns     = $table->get_columns();
		$constraints = $table->get_delete_constraints();

		foreach ( $constraints as $column_name => $behavior ) {

			if ( ! isset( $columns[ $column_name ] ) ) {
				throw new \UnexpectedValueException(
					"Column '$column_name' not found in table {$table->get_slug()}."
				);
			}

			$column = $columns[ $column_name ];

			if ( ! $column instanceof DeleteConstrainable ) {
				throw new \UnexpectedValueException(
					"Table's {$table->get_slug()} column '$column_name' must implement DeleteConstrainable."
				);
			}

			$column->register_delete_callback( function ( $pk, $object ) use ( $table, $column_name, $model, $behavior ) {

				/** @var FluentQuery $query */
				$query = call_user_func( array( $model, 'query' ) );
				$query->where( $column_name, true, $pk );

				if ( $behavior === DeleteConstrained::RESTRICT ) {
					$query->take( 1 );
				}

				$results = $query->results();

				switch ( $behavior ) {
					case DeleteConstrained::RESTRICT:
						if ( $results ) {
							/** @noinspection ExceptionsAnnotatingAndHandlingInspection */
							throw new DeleteRestrictedException(
								get_class( $results->first() ) . " with primary key '$pk' cannot be deleted due to a constraint " .
								"on the {$table->get_slug()} table for column {$column_name}."
							);
						}
						break;

					case DeleteConstrained::CASCADE:
						foreach ( $results as $result ) {
							$result->delete();
						}
						break;

					case DeleteConstrained::SET_DEFAULT:
						$defaults = $table->get_column_defaults();
						$default  = $defaults[ $column_name ];

						foreach ( $results as $result ) {
							$result->set_attribute( $column_name, $default );
							$result->save();
						}
						break;
				}
			} );
		}
	}
}