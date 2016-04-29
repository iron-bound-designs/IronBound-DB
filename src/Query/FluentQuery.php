<?php
/**
 * Contains the FluentQuery class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Query;

use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use IronBound\DB\Exception\InvalidColumnException;
use IronBound\DB\Model;
use IronBound\DB\Query\Tag\From;
use IronBound\DB\Query\Tag\Group;
use IronBound\DB\Query\Tag\Having;
use IronBound\DB\Query\Tag\Limit;
use IronBound\DB\Query\Tag\Order;
use IronBound\DB\Query\Tag\Select;
use IronBound\DB\Query\Tag\Where;
use IronBound\DB\Query\Tag\Where_Date;
use IronBound\DB\Table\Table;

/**
 * Class FluentQuery
 * @package IronBound\DB\Query
 */
class FluentQuery {

	/**
	 * @var \wpdb
	 */
	protected $wpdb;

	/**
	 * @var Table
	 */
	protected $table;

	/**
	 * @var string
	 */
	protected $model;

	/**
	 * @var Select
	 */
	protected $select;

	/**
	 * @var From
	 */
	protected $from;

	/**
	 * @var Where
	 */
	protected $where;

	/**
	 * @var Order
	 */
	protected $order;

	/**
	 * @var Group
	 */
	protected $group;

	/**
	 * @var Limit
	 */
	protected $limit;

	/**
	 * @var Having
	 */
	protected $having;

	/**
	 * @var string
	 */
	protected $alias = 't1';

	/**
	 * @var int
	 */
	protected $count;

	/**
	 * @var int
	 */
	protected $offset;

	/**
	 * @var bool
	 */
	protected $calc_found_rows = false;

	/**
	 * @var string
	 */
	protected $sql;

	/**
	 * Map of relation attribute name to callback.
	 *
	 * @var array
	 */
	protected $relations = array();

	/**
	 * FluentQuery constructor.
	 *
	 * @param \wpdb $wpdb
	 * @param Table $table
	 */
	public function __construct( \wpdb $wpdb, Table $table ) {
		$this->wpdb  = $wpdb;
		$this->table = $table;

		$this->select = new Select( null );
		$this->from   = new From( $this->table->get_table_name( $wpdb ), $this->alias );
	}

	/**
	 * Create a new FluentQuery object from a model.
	 *
	 * @since 2.0
	 *
	 * @param string $model
	 *
	 * @return static
	 */
	public static function from_model( $model ) {

		$query        = new static( $GLOBALS['wpdb'], $model::table() );
		$query->model = $model;

		return $query;
	}

	/**
	 * Select certain columns.
	 *
	 * @since 2.0
	 *
	 * @param string $columns,...
	 *
	 * @return $this
	 */
	public function select( $columns ) {

		if ( $columns === Select::ALL ) {

			$as = "{$this->alias}.*";

			$this->select->all( $as );

			return $this;
		}

		if ( ! is_array( $columns ) ) {
			$columns = func_get_args();
		}

		foreach ( $columns as $column ) {
			$this->select->also( $this->prepare_column( $column ) );
		}

		return $this;
	}

	/**
	 * Return only distinct values.
	 *
	 * @since 2.0
	 *
	 * @return $this
	 */
	public function distinct() {
		$this->select->filter_distinct();

		return $this;
	}

	/**
	 * Filter results by a condition.
	 *
	 * @since 2.0
	 *
	 * @param string|Where $column
	 * @param string|bool  $equality
	 * @param mixed        $value
	 * @param Closure|null $callback Called with $this as the first parameter. Setup for nesting on the new where tag.
	 * @param string       $boolean
	 *
	 * @return $this
	 */
	public function where( $column, $equality = '', $value = '', Closure $callback = null, $boolean = null ) {

		if ( $column instanceof Where ) {
			$where = $column;
		} else {

			if ( is_array( $value ) ) {
				$self  = $this;
				$value = array_map( function ( $value ) use ( $column, $self ) {
					return $self->escape_value( $column, $value );
				}, $value );
			} else {
				$value = $this->escape_value( $column, $value );
			}
			
			$column = $this->prepare_column( $column );

			$where = new Where( $column, $equality, $value );
		}

		if ( is_null( $this->where ) ) {
			$this->where = $where;
		}

		if ( $callback ) {
			$_where      = $this->where;
			$this->where = $where;
			$callback( $this );
			$this->where = $_where;
		}

		if ( ! $boolean ) {
			$this->where = $where;
		} else {
			$boolean = 'q' . ucfirst( $boolean );
			$this->where->{$boolean}( $where );
		}

		return $this;
	}

	/**
	 * Add a OR where clause.
	 *
	 * @since 2.0
	 *
	 * @param string       $column
	 * @param string|bool  $equality
	 * @param mixed        $value
	 * @param Closure|null $callback Called with $this as the first parameter. Setup for nesting on the new where tag.
	 *
	 * @return $this
	 */
	public function or_where( $column, $equality = '', $value = '', Closure $callback = null ) {
		return $this->where( $column, $equality, $value, $callback, 'or' );
	}

	/**
	 * Add an AND where clause.
	 *
	 * @since 2.0
	 *
	 * @param string       $column
	 * @param string|bool  $equality
	 * @param mixed        $value
	 * @param Closure|null $callback Called with $this as the first parameter. Setup for nesting on the new where tag.
	 *
	 * @return $this
	 */
	public function and_where( $column, $equality = '', $value = '', Closure $callback = null ) {
		return $this->where( $column, $equality, $value, $callback, 'and' );
	}

	/**
	 * Add a XOR where caluse.
	 *
	 * @since 2.0
	 *
	 * @param string       $column
	 * @param string|bool  $equality
	 * @param mixed        $value
	 * @param Closure|null $callback Called with $this as the first parameter. Setup for nesting on the new where tag.
	 *
	 * @return $this
	 */
	public function xor_where( $column, $equality, $value, Closure $callback = null ) {
		return $this->where( $column, $equality, $value, $callback, 'xor' );
	}

	public function where_date( \WP_Date_Query $date, Closure $callback = null, $boolean = 'and' ) {

		$date->column = $this->prepare_column( $date->column );

		return $this->where( new Where_Date( $date ), '', '', $callback, $boolean );
	}

	public function order_by( $column, $direction = null ) {

		$column = "{$this->alias}.{$column}";

		if ( is_null( $this->order ) ) {
			$this->order = new Order( $column, $direction );
		} else {
			$this->order->then( $column, $direction );
		}

		return $this;
	}

	public function group_by( $column ) {

		$column = "{$this->alias}.{$column}";

		if ( is_null( $this->group ) ) {
			$this->group = new Group( $column );
		} else {
			$this->group->then( $column );
		}

		return $this;
	}

	public function take( $number ) {
		$this->count = $number;

		return $this;
	}

	public function offset( $amount ) {
		$this->offset = $amount;

		return $this;
	}

	public function paginate( $page, $per_page ) {
		$this->count           = $per_page;
		$this->offset          = $per_page * ( $page - 1 );
		$this->calc_found_rows = true;

		return $this;
	}

	public function chunk( $number, $callback ) {

		$this->count = $number;


		return $this;
	}

	/**
	 * Perform this query while loading a set of relations.
	 *
	 * @param string|array $relations,... An array of relations to Closure callbacks
	 * @param Closure      $callback      The callback for the relation. Called with a FluentQuery object to allow for
	 *                                    customizing which models are eager loaded.
	 *
	 * @return $this
	 */
	public function with( $relations, $callback = null ) {

		if ( is_string( $relations ) ) {
			$relations = func_get_args();

			if ( func_num_args() === 2 && $relations[1] instanceof Closure ) {
				$relations = array(
					$relations[0] => $relations[1]
				);
			}
		}

		$parsed = array();

		foreach ( $relations as $relation => $callback ) {

			if ( ! $callback instanceof Closure ) {
				$relation = $callback;
				$callback = function () {
				};
			}

			$parsed[ $relation ] = $callback;

		}

		$this->relations = $parsed;

		return $this;
	}

	protected function make_limit_tag() {

		if ( ! $this->count ) {
			return $this;
		}

		$this->limit = new Limit( $this->count, $this->offset );

		return $this;
	}

	protected function build_sql() {

		$builder = new Builder();

		if ( (string) $this->select === (string) new Select( null ) ) {
			$this->select = new Select( Select::ALL );
		}

		$builder->append( $this->select );
		$builder->append( $this->from );

		if ( $this->where ) {
			$builder->append( $this->where );
		}

		if ( $this->group ) {
			$builder->append( $this->group );
		}

		if ( $this->having ) {
			$builder->append( $this->having );
		}

		if ( $this->order ) {
			$builder->append( $this->order );
		}

		if ( $this->limit ) {
			$builder->append( $this->limit );
		}

		return $builder->build();
	}

	/**
	 * Retrieve results.
	 *
	 * @since 2.0
	 *
	 * @return Collection
	 */
	public function results() {

		$this->make_limit_tag();
		$this->sql = $sql = $this->build_sql();

		$results = $this->wpdb->get_results( $sql, ARRAY_A );

		if ( ! $this->model ) {

			$primary_key = $this->table->get_primary_key();
			$collection  = array();

			foreach ( $results as $result ) {
				$collection[ $result->{$primary_key} ] = $result;
			}

			return new ArrayCollection( $collection );
		}

		$model_class  = $this->model;
		$models       = array();
		$primary_keys = array();

		foreach ( $results as $result ) {
			$model = $model_class::from_query( $result );

			if ( $model ) {
				$models[ $model->get_pk() ] = $model;

				$primary_keys[] = $model->get_pk();
			}
		}

		if ( ! empty( $this->relations ) ) {
			$this->handle_eager_loading( $models );
		}

		return new ArrayCollection( $models );
	}

	/**
	 * Handle eager loading of relations.
	 *
	 * @since 2.0
	 *
	 * @param Model[] $models
	 */
	protected function handle_eager_loading( $models ) {

		/** @var Model $model */
		$model = new $this->model;

		foreach ( $this->relations as $relation => $customize_callback ) {
			$model->get_relation( $relation )->eager_load( $models, $relation, $customize_callback );
		}
	}

	/**
	 * Get the SQL statement executed.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function _get_sql() {
		return $this->sql;
	}

	/**
	 * Prepare a column.
	 *
	 * @since 2.0
	 *
	 * @param $column
	 *
	 * @return string
	 * @throws InvalidColumnException
	 */
	public function prepare_column( $column ) {

		$columns = $this->table->get_columns();

		if ( ! isset( $columns[ $column ] ) ) {
			throw new InvalidColumnException( "Invalid database column '$column'." );
		}

		return "{$this->alias}.{$column}";
	}

	/**
	 * Escape a value.
	 *
	 * @since 2.0
	 *
	 * @param string $column
	 * @param mixed  $value
	 *
	 * @return mixed
	 *
	 * @throws InvalidColumnException
	 */
	public function escape_value( $column, $value ) {

		$columns = $this->table->get_columns();

		if ( ! isset( $columns[ $column ] ) ) {
			throw new InvalidColumnException( "Invalid database column '$column'." );
		}

		if ( empty( $value ) ) {
			return '';
		}

		return esc_sql( $columns[ $column ]->prepare_for_storage( $value ) );
	}
}