<?php
/**
 * Make queries against a custom db table.
 *
 * @author Iron Bound Designs
 * @since  1.0
 */

namespace IronBound\DB\Query;

use IronBound\DB\Exception;
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

		$builder = new Builder();

		$allowed_columns = $this->table->get_columns();

		if ( is_array( $columns ) ) {

			$select = new Select( null );

			foreach ( $columns as $col ) {

				if ( ! isset( $allowed_columns[ $col ] ) ) {
					throw new Exception( "Invalid column." );
				}

				$select->also( $col );
			}
		} elseif ( $columns == Select::ALL ) {
			$select = new Select( $columns );
		} else {
			if ( ! isset( $allowed_columns[ $columns ] ) ) {
				throw new Exception( "Invalid column" );
			}

			$select = new Select( $columns );
		}

		$builder->append( $select );
		$builder->append( new From( $this->table->get_table_name( $this->wpdb ) ) );
		$builder->append( new Where( $column, true, $this->escape_value( $column, $value ) ) );

		return $this->wpdb->get_row( $builder->build() );
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
			throw new Exception( "Invalid column." );
		}

		$builder->append( new Select( $column ) );
		$builder->append( new From( $this->table->get_table_name( $this->wpdb ) ) );
		$builder->append( new Where( $where, true, $this->escape_value( $where, $value ) ) );

		return $this->wpdb->get_var( $builder->build() );
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
					$where = new Where( $column, true, $this->escape_value( $column, $value ) );
				} else {
					$where->qAnd( new Where( $column, true, $this->escape_value( $column, $value ) ) );
				}
			}

			$builder->append( $where );
		}

		return (int) $this->wpdb->get_var( $builder->build() );
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

		// Initialise column format array
		$column_formats = $this->table->get_columns();

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		$null_columns = array();

		foreach ( $data as $col => $val ) {

			if ( $val == null ) {
				$null_columns[] = $col;
			}
		}

		foreach ( $null_columns as $null_column ) {
			unset( $data[ $null_column ] );
			unset( $column_formats[ $null_column ] );
		}

		$prev = $this->wpdb->show_errors( false );
		$this->wpdb->insert( $this->table->get_table_name( $this->wpdb ), $data, $column_formats );
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

		// Initialise column format array
		$column_formats = $this->table->get_columns();

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		$prev   = $this->wpdb->show_errors( false );
		$result = $this->wpdb->update( $this->table->get_table_name( $this->wpdb ), $data, $where, $column_formats );
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
		$result = $this->wpdb->delete( $this->table->get_table_name( $this->wpdb ), array( $this->table->get_primary_key() => $row_key ) );
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

		$prev   = $this->wpdb->show_errors( false );
		$result = $this->wpdb->delete( $this->table->get_table_name( $this->wpdb ), $wheres );
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

		$columns = $this->table->get_columns();

		if ( ! isset( $columns[ $column ] ) ) {
			throw new Exception( "Invalid database column." );
		}

		if ( empty( $value ) ) {
			return '';
		}

		$column_format = $columns[ $column ];

		if ( $value[0] == '%' ) {
			$value = '%' . $value;
		}

		if ( $value[ strlen( $value ) - 1 ] == '%' ) {
			$value = $value . '%';
		}

		return esc_sql( sprintf( $column_format, $value ) );
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
