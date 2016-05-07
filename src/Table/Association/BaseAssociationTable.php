<?php
/**
 * Contains the BaseAssociationTable class definition.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Association;

use Doctrine\Common\Inflector\Inflector;
use IronBound\DB\Table\BaseTable;
use IronBound\DB\Table\Table;

abstract class BaseAssociationTable extends BaseTable implements AssociationTable {
	
	/**
	 * Build the column name for a table.
	 *
	 * @since 2.0
	 *
	 * @param Table $table
	 *
	 * @return string
	 */
	protected function build_column_name_for_table( Table $table ) {

		$basename  = $this->class_basename( $table );
		$tableized = Inflector::tableize( $basename );

		$parts         = explode( '_', $tableized );
		$last_plural   = array_pop( $parts );
		$last_singular = Inflector::singularize( $last_plural );
		$parts[]       = $last_singular;

		$column_name = implode( '_', $parts );
		$column_name .= '_' . $table->get_primary_key();

		return $column_name;
	}

	/**
	 * Get the basename for a class.
	 *
	 * @since 2.0
	 *
	 * @param string|object $class
	 *
	 * @return string
	 */
	protected function class_basename( $class ) {

		$class = is_object( $class ) ? get_class( $class ) : $class;

		return basename( str_replace( '\\', '/', $class ) );
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			$this->get_col_a() => '',
			$this->get_col_b() => ''
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_primary_key() {
		return '';
	}
}