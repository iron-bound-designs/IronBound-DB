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
use IronBound\DB\Collections\Collection;
use IronBound\DB\Exception\InvalidColumnException;
use IronBound\DB\Exception\ModelNotFoundException;
use IronBound\DB\Model;
use IronBound\DB\Query\Tag\From;
use IronBound\DB\Query\Tag\Generic;
use IronBound\DB\Query\Tag\Group;
use IronBound\DB\Query\Tag\Having;
use IronBound\DB\Query\Tag\Join;
use IronBound\DB\Query\Tag\Limit;
use IronBound\DB\Query\Tag\Order;
use IronBound\DB\Query\Tag\Select;
use IronBound\DB\Query\Tag\Where;
use IronBound\DB\Query\Tag\Where_Date;
use IronBound\DB\Query\Tag\Where_Raw;
use IronBound\DB\Table\Meta\MetaTable;
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
	 * @var Join[]
	 */
	protected $joins = array();

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
	protected $meta_join;

	/**
	 * @var string
	 */
	protected $meta_type;

	/**
	 * @var MetaTable
	 */
	protected $meta_table;

	/**
	 * @var string
	 */
	protected $alias = 't1';

	/**
	 * @var int
	 */
	protected $alias_count = 1;

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
	 * @var bool
	 */
	protected $prime_meta_cache = true;

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
	 * @var Collection
	 */
	protected $results;

	/**
	 * @var int|null
	 */
	protected $total;

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

			$this->select->all( $this->alias );

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
	 * Select all results.
	 *
	 * @since 2.0
	 *
	 * @param bool $local_only Only retrieve results local to this table.
	 *
	 * @return $this
	 */
	public function select_all( $local_only = true ) {

		$this->select->all( $local_only ? $this->alias : null );

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
	 * @param string|array|Where $column
	 * @param string|bool        $equality
	 * @param mixed              $value
	 * @param Closure|null       $callback Called with $this as the first parameter. Setup for nesting on the new where tag.
	 * @param string             $boolean
	 *
	 * @return $this
	 */
	public function where( $column, $equality = '', $value = '', Closure $callback = null, $boolean = null ) {

		if ( is_array( $column ) ) {

			foreach ( $column as $col => $val ) {
				$this->and_where( $col, true, $val );
			}

			return $this;
		} elseif ( $column instanceof Where ) {
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

	/**
	 * Perform a date based where query.
	 *
	 * @since 2.0
	 *
	 * @param \WP_Date_Query $date
	 * @param Closure|null   $callback
	 * @param string         $boolean
	 *
	 * @return $this
	 * @throws InvalidColumnException
	 */
	public function where_date( \WP_Date_Query $date, Closure $callback = null, $boolean = 'and' ) {

		$date->column = $this->prepare_column( $date->column );

		return $this->where( new Where_Date( $date ), '', '', $callback, $boolean );
	}

	/**
	 * Perform a meta query.
	 *
	 * @since 2.0
	 *
	 * @param array|\WP_Meta_Query $query
	 * @param MetaTable|null       $table     Table metadata is stored in. If not specified, will be retrieved from the model.
	 * @param string               $meta_type Type of metadata. Will be determined from the model if not given.
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException If a MetaTable or $meta_type can't be determined.
	 */
	public function where_meta( $query, MetaTable $table = null, $meta_type = '' ) {

		if ( ! $table && $this->model && method_exists( $this->model, 'get_meta_table' ) ) {
			$table = call_user_func( array( $this->model, 'get_meta_table' ) );
		}

		if ( ! $meta_type && $this->model && method_exists( $this->model, 'get_meta_type' ) ) {
			$meta_type = call_user_func( array( $this->model, 'get_meta_type' ) );
		}

		if ( ! $table ) {
			throw new \InvalidArgumentException( "MetaTable can't be determined from the given arguments." );
		}

		if ( ! $meta_type ) {
			throw new \InvalidArgumentException( "\$meta_type can't be determined from the given arguments." );
		}

		if ( ! $query instanceof \WP_Meta_Query ) {
			$query = new \WP_Meta_Query( $query );
		}

		$fn = function ( $key, $original ) use ( $table, $meta_type ) {

			if ( $original === $meta_type . '_id' ) {
				$key = $table->get_primary_id_column();
			}

			return $key;
		};

		add_filter( 'sanitize_key', $fn, 10, 2 );

		$sql = $query->get_sql(
			$meta_type,
			$this->alias,
			$this->table->get_primary_key()
		);

		remove_filter( 'sanitize_key', $fn, 10 );

		$this->meta_join = $sql['join'];

		$where = $sql['where'];
		$where = substr( $where, 5 );

		if ( $this->where ) {
			$this->where->qAnd( new Where_Raw( $where ) );
		} else {
			$this->where = new Where_Raw( $where );
		}

		return $this;
	}

	/**
	 * Order the results by a given column.
	 *
	 * Can be called multiple times to add additional order by clauses.
	 *
	 * @since 2.0
	 *
	 * @param string $column
	 * @param string $direction
	 *
	 * @return $this
	 */
	public function order_by( $column, $direction = null ) {

		$column = "{$this->alias}.{$column}";

		if ( is_null( $this->order ) ) {
			$this->order = new Order( $column, $direction );
		} else {
			$this->order->then( $column, $direction );
		}

		return $this;
	}

	/**
	 * Group the results by a given column.
	 *
	 * Can be called multiple times to add additional group by columns.
	 *
	 * @since 2.0
	 *
	 * @param string $column
	 *
	 * @return $this
	 */
	public function group_by( $column ) {

		$column = "{$this->alias}.{$column}";

		if ( is_null( $this->group ) ) {
			$this->group = new Group( $column );
		} else {
			$this->group->then( $column );
		}

		return $this;
	}

	/**
	 * Simple join statement.
	 *
	 * @since 2.0
	 *
	 * @param Table       $table
	 * @param string      $this_column
	 * @param string      $other_column
	 * @param bool|string $operator
	 * @param callable    $callback Called with a FluentQuery object. Can be used to build additional where queries
	 *                              for the Join clause.
	 * @param string      $type     Join type. Defaults to 'INNER'.
	 *
	 * @return $this
	 * @throws InvalidColumnException
	 */
	public function join( Table $table, $this_column, $other_column, $operator = '=', $callback = null, $type = 'INNER' ) {

		$other_alias = 't' . ( $this->alias_count + 1 );

		$other_query              = new FluentQuery( $this->wpdb, $table );
		$other_query->alias       = $other_alias;
		$other_query->alias_count = $this->alias_count + 1;

		$from = new From( $table->get_table_name( $this->wpdb ), $other_alias );

		$where = new Where_Raw(
			"{$this->prepare_column( $this_column )} $operator {$other_query->prepare_column( $other_column )}"
		);

		if ( $callback ) {
			$callback( $other_query );

			if ( $other_query->where ) {
				$where->qAnd( $other_query->where );
			}
		}

		$this->joins[] = new Join( $from, $where, $type );

		return $this;
	}

	/**
	 * Only retrieve a certain number of results.
	 *
	 * @since 2.0
	 *
	 * @param int $number
	 *
	 * @return $this
	 */
	public function take( $number ) {
		$this->count = $number;

		return $this;
	}

	/**
	 * Offset the results retrieved by a certain amount.
	 *
	 * @since 2.0
	 *
	 * @param int $amount
	 *
	 * @return $this
	 */
	public function offset( $amount ) {
		$this->offset = $amount;

		return $this;
	}

	/**
	 * Paginate results.
	 *
	 * @since 2.0
	 *
	 * @param int $page
	 * @param int $per_page
	 *
	 * @return $this
	 */
	public function paginate( $page, $per_page ) {
		$this->count           = $per_page;
		$this->offset          = $per_page * ( $page - 1 );
		$this->calc_found_rows = true;

		return $this;
	}

	/**
	 * Execute a callback over every row, chunking by a certain number.
	 *
	 * @since 2.0
	 *
	 * @param int      $number
	 * @param callable $callback
	 *
	 * @return bool
	 */
	public function each( $number, $callback ) {

		$this->offset = 0;
		$this->count  = $number;

		$query = clone $this;

		do {

			$_query  = clone $query;
			$results = $query->results();

			foreach ( $results as $result ) {
				if ( $callback( $result ) === false ) {
					return false;
				}
			}

			$_query->offset += $number;
			$query = $_query;

		} while ( $results->count() === $number );

		return true;
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

		$default = function () {
		};

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
				$callback = $default;
			}

			if ( strpos( $relation, '.' ) !== false ) {
				$parsed = $this->parse_nested_with( $relation, $parsed );
			} else {
				$parsed[ $relation ] = $callback;
			}
		}

		$this->relations = $parsed;

		return $this;
	}

	/**
	 * Parse the nested relations.
	 *
	 * @since 2.0
	 *
	 * @param string $name
	 * @param array  $results
	 *
	 * @return array
	 */
	protected function parse_nested_with( $name, $results ) {

		$parts = explode( '.', $name );
		$first = $parts[0];

		$results[ $first ] = false;

		$this->assign_array_by_path( $results, $name, true );

		return $results;
	}

	/**
	 * Fill an array by array dot notation.
	 *
	 * @since 2.0
	 *
	 * @param array  $arr
	 * @param string $path
	 * @param mixed  $value
	 */
	protected function assign_array_by_path( &$arr, $path, $value ) {

		$keys = explode( '.', $path );

		foreach ( $keys as $key ) {
			$arr = &$arr[ $key ];
		}

		$arr = $value;
	}

	/**
	 * Prime the meta cache.
	 *
	 * @since 2.0
	 *
	 * @param bool $prime
	 *
	 * @return $this
	 */
	public function prime_meta_cache( $prime = true ) {
		$this->prime_meta_cache = $prime;

		return $this;
	}

	/**
	 * Make the limit tag for this query.
	 *
	 * @since 2.0
	 *
	 * @return $this
	 */
	protected function make_limit_tag() {

		if ( ! $this->count ) {
			return $this;
		}

		$this->limit = new Limit( $this->count, $this->offset );

		return $this;
	}

	/**
	 * Build the SQL statement.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	protected function build_sql() {

		$builder = new Builder();

		if ( ! $this->select->is_all() && ! $this->select->get_columns() ) {
			$this->select->all( $this->alias );
		}

		$builder->append( $this->select );
		$builder->append( $this->from );

		foreach ( $this->joins as $join ) {
			$builder->append( $join );
		}

		if ( $this->meta_join ) {
			$builder->append( new Generic( '', $this->meta_join ) );
		}

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
	 * @return Collection|DoctrineCollection
	 */
	public function results() {

		$this->make_limit_tag();
		$this->sql = $sql = $this->build_sql();

		$results = $this->wpdb->get_results( $sql, ARRAY_A );

		if ( ! $this->model ) {
			return new ArrayCollection( $results );
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

		$collection = new Collection( $models );

		$this->results = $collection;

		if ( $this->calc_found_rows ) {

			$count_results = $this->wpdb->get_results( "SELECT FOUND_ROWS() AS COUNT" );

			if ( empty( $count_results ) || empty( $count_results[0] ) ) {
				$this->total = 0;
			} else {
				$this->total = $count_results[0]->COUNT;
			}
		}

		if ( $this->prime_meta_cache && ( $this->meta_table || ( $this->model && method_exists( $this->model, 'get_meta_table' ) ) ) ) {
			$this->update_meta_cache();
		}

		return $collection;
	}

	/**
	 * Update the meta cache.
	 *
	 * @since 2.0
	 */
	protected function update_meta_cache() {

		$ids       = $this->results->getKeys();
		$table     = $this->meta_table ?: call_user_func( array( $this->model, 'get_meta_table' ) );
		$meta_type = $this->meta_type ?: call_user_func( array( $this->model, 'get_meta_type' ) );

		$fn = function ( $key, $original ) use ( $table, $meta_type ) {

			if ( $original === $meta_type . '_id' ) {
				$key = $table->get_primary_id_column();
			}

			return $key;
		};

		add_filter( 'sanitize_key', $fn, 10, 2 );

		update_meta_cache( $meta_type, $ids );

		remove_filter( 'sanitize_key', $fn );
	}

	/**
	 * Get the first result from the query.
	 *
	 * @since 2.0
	 *
	 * @return Model
	 */
	public function first() {

		if ( $this->results ) {
			return $this->results->first();
		} else {
			return $this->results()->first();
		}
	}

	/**
	 * Find a model by its primary key.
	 *
	 * @since 2.0
	 *
	 * @param string|int|array $primary_key
	 *
	 * @return Model|Collection
	 */
	public function find( $primary_key ) {

		if ( is_array( $primary_key ) ) {
			return $this->find_many( $primary_key );
		}

		return $this->where( $this->table->get_primary_key(), true, $primary_key )->first();
	}

	/**
	 * Find many models by their primary keys.
	 *
	 * @since 2.0
	 *
	 * @param array $primary_keys
	 *
	 * @return Collection
	 */
	public function find_many( array $primary_keys ) {
		return $this->where( $this->table->get_primary_key(), true, $primary_keys )->results();
	}

	/**
	 * Find a model by its primary key or throw an exception.
	 *
	 * @since 2.0
	 *
	 * @param string|int|array $primary_key
	 *
	 * @return Model|Collection
	 *
	 * @throws ModelNotFoundException
	 */
	public function find_or_fail( $primary_key ) {

		$result = $this->find( $primary_key );

		if ( is_array( $primary_key ) ) {
			if ( count( $result ) == count( array_unique( $primary_key ) ) ) {
				return $result;
			}
		} elseif ( $result ) {
			return $result;
		}

		throw new ModelNotFoundException( "No model found for '$primary_key'." );
	}

	/**
	 * Find a model by its primary key or create a new model.
	 *
	 * @since 2.0
	 *
	 * @param string|int $primary_key
	 *
	 * @return Model
	 */
	public function find_or_new( $primary_key ) {

		$model = $this->find( $primary_key );

		if ( is_null( $model ) ) {
			$model = new $this->model;
		}

		return $model;
	}

	/**
	 * Find the first model matching a given a set of attributes, or construct a new one.
	 *
	 * The newly constructed model follows the fillable rules for the model.
	 *
	 * @since 2.0
	 *
	 * @param array $attributes
	 *
	 * @return Model
	 */
	public function first_or_new( array $attributes ) {

		$model = $this->where( $attributes )->first();

		if ( $model ) {
			return $model;
		}

		return new $this->model( (object) $attributes );
	}

	/**
	 * Find the first model matching a given set of attributes, or construct and save a new one.
	 *
	 * The newly constructed model follows the fillable rules for the model.
	 *
	 * @since 2.0
	 *
	 * @param array $attributes
	 *
	 * @return Model
	 */
	public function first_or_create( array $attributes ) {

		$model = $this->where( $attributes )->first();

		if ( $model ) {
			return $model;
		}

		$model = new $this->model( (object) $attributes );
		$model->save();

		return $model;
	}

	/**
	 * Find a model matching a given set of attributes. If it does not exist,
	 * a new model is constructed with the given set of attributes.
	 *
	 * The model is then updated with the given set of values.
	 *
	 * @since 2.0
	 *
	 * @param array $attributes
	 * @param array $values
	 *
	 * @return Model
	 */
	public function update_or_create( array $attributes, array $values ) {

		$model = $this->first_or_new( $attributes );
		$model->fill( $values )->save();

		return $model;
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
			if ( is_array( $customize_callback ) ) {
				$loaded = $model->get_relation( $relation )->eager_load( $models );
				$this->do_nested_eager_load( $loaded, $relation, $customize_callback );
			} else {
				$model->get_relation( $relation )->eager_load( $models, $customize_callback );
			}
		}
	}

	/**
	 * Handle a nested eager loaded.
	 *
	 * @since 2.0
	 *
	 * @param Collection $loaded
	 * @param string     $relation
	 * @param array      $nested
	 */
	protected function do_nested_eager_load( Collection $loaded, $relation, $nested ) {

		$model = $loaded->first();

		foreach ( $nested as $value => $ignore ) {
			if ( is_string( $value ) ) {
				if ( $model instanceof Model ) {
					$model->get_relation( $value )->eager_load( $loaded->toArray() );
				}

				return;
			} else {
				if ( ! $model instanceof Model ) {
					return;
				}

				$loaded = $model->get_relation( $relation )->eager_load( $loaded->toArray() );

				$this->do_nested_eager_load( $loaded, $nested, $value );
			}
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