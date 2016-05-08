<?php
/**
 * Contains the BaseTable class definition.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table;
use Doctrine\Common\Inflector\Inflector;

/**
 * Class BaseTable
 * @package IronBound\DB\Table
 */
abstract class BaseTable implements Table {

	/**
	 * @inheritDoc
	 */
	public function get_creation_sql( \wpdb $wpdb ) {

		$tn = $this->get_table_name( $wpdb );

		$sql = "CREATE TABLE {$tn} (\n";
		$sql .= $this->get_columns_definition();

		if ( $keys = $this->get_keys_definition() ) {
			$sql .= ",\n{$keys}";
		}

		$sql .= "\n) {$wpdb->get_charset_collate()};";

		return $sql;
	}

	/**
	 * Get the column definitions.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	protected function get_columns_definition() {
		return implode( ",\n", $this->get_columns() );
	}

	/**
	 * Get the keys definition.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	protected function get_keys_definition() {
		return implode( ",\n", $this->get_keys() );
	}

	/**
	 * Get all keys on the table.
	 *
	 * @since 2.0
	 *
	 * @return array
	 */
	protected function get_keys() {

		$keys = array();

		if ( $this->get_primary_key() ) {
			$keys[] = "PRIMARY KEY  ({$this->get_primary_key()})";
		}

		return $keys;
	}

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
}