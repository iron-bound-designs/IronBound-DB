<?php
/**
 * Make queries against a custom db table.
 *
 * @author Iron Bound Designs
 * @since  1.0
 */

namespace IronBound\DB\Query;

use IronBound\DB\Exception;
use IronBound\DB\Exception\InvalidColumnException;
use IronBound\DB\Query\Tag\From;
use IronBound\DB\Query\Tag\Select;
use IronBound\DB\Query\Tag\Where;
use IronBound\DB\Table\Table;

/**
 * Class Query
 *
 * @package IronBound\DB
 */
class Simple_Query {

	/**
	 * @var \wpdb
	 */
	protected $wpdb;

	/**
	 * @var Table
	 */
	protected $table;

	/**
	 * Constructor.
	 *
	 * @param \wpdb $wpdb
	 * @param Table $table
	 */
	public function __construct( \wpdb $wpdb, Table $table ) {
		$this->wpdb  = $wpdb;
		$this->table = $table;
	}

	/**
	 * Retrieve a row by the primary key
	 *
	 * @since 1.0
	 *
	 * @param string       $row_key
	 * @param array|string $columns
	 *
	 * @return object
	 *
	 * @throws Exception
	 */
	public function get( $row_key, $columns = '*' ) {
		return $this->get_by( $this->table->get_primary_key(), $row_key, $columns );
	}

	/**
	 * Retrieve a row by a specific column / value
	 *
	 * @since 1.0
	 *
	 * @param string       $column Column name
	 * @param string       $value  Value for the column.
	 * @param string|array $columns
	 *
	 * @return object
	 *
	 * @throws Exception
	 */
	public function get_by( $column, $value, $columns = '*' ) {
		return $this->get_by_or_many_by_helper( $column, $value, $columns, 'get_row' );
	}

	/**
	 * Retrieve a specific column's value by the primary key
	 *
	 * @since 1.0
	 *
	 * @param string $column
	 * @param string $row_key
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	public function get_column( $column, $row_key ) {
		return $this->get_column_by( $column, $this->table->get_primary_key(), $row_key );
	}

	/**
	 * Retrieve a specific column's value by the the specified column / value
	 *
	 * @since 1.0
	 *
	 * @param string $column Var to retrieve
	 * @param string $where
	 * @param string $value
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	public function get_column_by( $column, $where, $value ) {

		$builder = new Builder();

		$allowed_columns = $this->table->get_columns();

		if ( ! isset( $allowed_columns[ $column ] ) ) {
			throw new InvalidColumnException( "Invalid column." );
		}

		$builder->append( new Select( "`$column`" ) );
		$builder->append( new From( $this->table->get_table_name( $this->wpdb ) ) );
		$builder->append( new Where( "`$where`", true, $this->escape_value( $where, $value ) ) );

		return $this->wpdb->get_var( trim( $builder->build() ) );
	}

	/**
	 * Get many rows by a certain column.
	 *
	 * @since 2.0
	 *
	 * @param string $column
	 * @param mixed  $value
	 * @param string $columns
	 *
	 * @return array
	 * @throws InvalidColumnException
	 */
	public function get_many_by( $column, $value, $columns = '*' ) {
		return $this->get_by_or_many_by_helper( $column, $value, $columns, 'get_results' );
	}

	/**
	 * Helper function for get_by and get_many_by methods.
	 *
	 * @param string $column
	 * @param mixed  $value
	 * @param string $columns
	 * @param string $method
	 *
	 * @return mixed
	 * @throws InvalidColumnException
	 */
	protected function get_by_or_many_by_helper( $column, $value, $columns = '*', $method ) {

		$builder = new Builder();

		$allowed_columns = $this->table->get_columns();

		if ( is_array( $columns ) ) {

			$select = new Select( null );

			foreach ( $columns as $col ) {

				if ( ! isset( $allowed_columns[ $col ] ) ) {
					throw new InvalidColumnException( "Invalid column." );
				}

				$select->also( "`$col`" );
			}
		} elseif ( $columns == Select::ALL ) {
			$select = new Select( $columns );
		} else {
			if ( ! isset( $allowed_columns[ $columns ] ) ) {
				throw new InvalidColumnException( "Invalid column" );
			}

			$select = new Select( "`$columns`" );
		}

		$builder->append( $select );
		$builder->append( new From( $this->table->get_table_name( $this->wpdb ) ) );
		$builder->append( new Where( "`$column`", true, $this->escape_value( $column, $value ) ) );

		return $this->wpdb->{$method}( trim( $builder->build() ) );
	}

	/**
	 * Retrieve the number of rows matching a certain where clause
	 *
	 * @since 1.0
	 *
	 * @param array $wheres
	 *
	 * @return int
	 */
	public function count( $wheres = array() ) {

		$builder = new Builder();

		$select = new Select( null );
		$select->expression( 'COUNT', '*' );
		$builder->append( $select );

		$builder->append( new From( $this->table->get_table_name( $this->wpdb ) ) );

		if ( ! empty( $wheres ) ) {

			foreach ( $wheres as $column => $value ) {
				if ( ! isset( $where ) ) {
					$where = new Where( "`$column`", true, $this->escape_value( $column, $value ) );
				} else {
					$where->qAnd( new Where( "`$column`", true, $this->escape_value( $column, $value ) ) );
				}
			}

			$builder->append( $where );
		}

		return (int) $this->wpdb->get_var( trim( $builder->build() ) );
	}

	/**
	 * Insert a new row
	 *
	 * @since 1.0
	 *
	 * @param array $data
	 *
	 * @return mixed Insert ID
	 *
	 * @throws Exception
	 */
	public function insert( $data ) {

		// Set default values
		$data = wp_parse_args( $data, $this->table->get_column_defaults() );

		$columns = $this->table->get_columns();

		// White list columns
		$data = array_intersect_key( $data, $columns );

		$null_columns = array();

		foreach ( $data as $col => $val ) {

			if ( $val === null ) {
				$null_columns[] = $col;
			}
		}

		foreach ( $null_columns as $null_column ) {
			unset( $data[ $null_column ] );
		}

		foreach ( $data as $name => $value ) {
			$data[ $name ] = $this->prepare_for_storage( $name, $value );
		}

		$formats = array_fill( 0, count( $data ), '%s' );

		$prev = $this->wpdb->show_errors( false );
		$this->wpdb->insert( $this->table->get_table_name( $this->wpdb ), $data, $formats );
		$this->wpdb->show_errors( $prev );

		if ( $this->wpdb->last_error ) {
			throw $this->generate_exception_from_db_error();
		}

		return $this->wpdb->insert_id;
	}

	/**
	 * Update a row
	 *
	 * @since 1.0
	 *
	 * @param string $row_key
	 * @param array  $data
	 * @param array  $where
	 *
	 * @return  bool
	 *
	 * @throws Exception
	 */
	public function update( $row_key, $data, $where = array() ) {

		if ( empty( $row_key ) ) {
			return false;
		}

		if ( empty( $where ) ) {
			$where = array( $this->table->get_primary_key() => $row_key );
		}

		$columns = $this->table->get_columns();

		// White list columns
		$data = array_intersect_key( $data, $columns );

		foreach ( $data as $name => $value ) {
			$data[ $name ] = $this->prepare_for_storage( $name, $value );
		}

		$formats      = array_fill( 0, count( $data ), '%s' );
		$where_format = array_fill( 0, count( $where ), '%s' );

		$prev   = $this->wpdb->show_errors( false );
		$result = $this->wpdb->update( $this->table->get_table_name( $this->wpdb ), $data, $where, $formats, $where_format );
		$this->wpdb->show_errors( $prev );

		if ( $this->wpdb->last_error ) {
			throw $this->generate_exception_from_db_error();
		}

		return (bool) $result;
	}

	/**
	 * Delete a row identified by the primary key
	 *
	 * @since 1.0
	 *
	 * @param string $row_key
	 *
	 * @return  bool
	 *
	 * @throws Exception
	 */
	public function delete( $row_key ) {

		if ( empty( $row_key ) ) {
			return false;
		}

		$row_key = $this->escape_value( $this->table->get_primary_key(), $row_key );

		$prev   = $this->wpdb->show_errors( false );
		$result = $this->wpdb->delete( $this->table->get_table_name( $this->wpdb ), array(
			$this->table->get_primary_key() => $row_key
		), '%s' );
		$this->wpdb->show_errors( $prev );

		if ( $this->wpdb->last_error ) {
			throw $this->generate_exception_from_db_error();
		}

		return (bool) $result;
	}

	/**
	 * Delete many rows.
	 *
	 * @since 1.0
	 *
	 * @param $wheres array
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function delete_many( $wheres ) {

		$format = array_fill( 0, count( $wheres ), '%s' );
		$prev   = $this->wpdb->show_errors( false );
		$result = $this->wpdb->delete( $this->table->get_table_name( $this->wpdb ), $wheres, $format );
		$this->wpdb->show_errors( $prev );

		if ( $this->wpdb->last_error ) {
			throw $this->generate_exception_from_db_error();
		}

		return (bool) $result;
	}

	/**
	 * Escape a value using sprintf.
	 *
	 * @param string $column
	 * @param mixed  $value
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 */
	public function escape_value( $column, $value ) {
		return esc_sql( $this->prepare_for_storage( $column, $value ) );
	}

	/**
	 * Prepare a value for storage.
	 *
	 * @since 2.0
	 *
	 * @param string $column
	 * @param mixed  $value
	 *
	 * @return mixed|string
	 * @throws InvalidColumnException
	 */
	public function prepare_for_storage( $column, $value ) {

		$columns = $this->table->get_columns();

		if ( ! isset( $columns[ $column ] ) ) {
			throw new InvalidColumnException( "Invalid database column '$column'." );
		}

		if ( is_null( $value ) ) {
			return null;
		}

		if ( empty( $value ) ) {
			return '';
		}

		return $columns[ $column ]->prepare_for_storage( $value );
	}

	/**
	 * Generate an Exception object from a DB error.
	 *
	 * @return Exception
	 */
	protected function generate_exception_from_db_error() {

		if ( ! $this->wpdb->last_error ) {
			return null;
		}

		if ( $this->is_mysqli() ) {
			$error_number = mysqli_errno( $this->get_dbh() );
		} else {
			$error_number = mysql_errno( $this->get_dbh() );
		}

		return new Exception( $this->wpdb->last_error, $error_number );
	}

	/**
	 * Check if wpdb is using mysqli
	 *
	 * @return bool
	 */
	protected function is_mysqli() {
		return $this->wpdb->use_mysqli;
	}

	/**
	 * Get the mysql dbh
	 *
	 * @return mixed
	 */
	protected function get_dbh() {
		return $this->wpdb->dbh;
	}
}
