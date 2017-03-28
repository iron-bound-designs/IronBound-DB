<?php
/**
 * Abstract model class for models built upon our DB table.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\DB;

use Closure;
use IronBound\Cache\Cacheable;
use IronBound\Cache\Cache;
use IronBound\DB\Query\FluentQuery;
use IronBound\DB\Query\Scope;
use IronBound\DB\Relations\HasForeign;
use IronBound\DB\Relations\Relation;
use IronBound\DB\Table\Table;
use IronBound\DB\Table\TimestampedTable;
use IronBound\WPEvents\EventDispatcher;
use IronBound\WPEvents\GenericEvent;

/**
 * Class Model
 *
 * @package IronBound\DB
 *
 * @method static $this create( $attributes ) Create a new instance of this model.
 * @method static creating( callable $listener, int $priority = 10, int $args = 3 ) Listen for the creating event.
 * @method static created( callable $listener, int $priority = 10, int $args = 3 ) Listen for the created event.
 * @method static updating( callable $listener, int $priority = 10, int $args = 3 ) Listen for the updating event.
 * @method static updated( callable $listener, int $priority = 10, int $args = 3 ) Listen for the updated event.
 * @method static saving( callable $listener, int $priority = 10, int $args = 3 ) Listen for the saving event.
 * @method static saved( callable $listener, int $priority = 10, int $args = 3 ) Listen for the saved event.
 * @method static deleting( callable $listener, int $priority = 10, int $args = 3 ) Listen for the deleting event.
 * @method static deleted( callable $listener, int $priority = 10, int $args = 3 ) Listen for the deleted event.
 */
abstract class Model implements Cacheable, \Serializable {

	/// Global Configuration

	/**
	 * @var Manager
	 */
	protected static $_db_manager;

	/**
	 * @var EventDispatcher
	 */
	protected static $_event_dispatcher;

	/**
	 * @var array
	 */
	protected static $_booted = array();

	/// Model Configuration

	/**
	 * Whether all attributes can be mass filled via the constructor
	 * or the magic create() method.
	 *
	 * @var bool
	 */
	protected static $_unguarded = false;

	/**
	 * List of relations to be eager loaded.
	 *
	 * @var array
	 */
	protected static $_eager_load = array();

	/**
	 * Whether to cache this Model's attributes in `WP_Object_Cache`.
	 *
	 * It is recommended to keep this true for most use-cases.
	 *
	 * To disable it, add `protected static $_cache = false;` to your Model subclass.
	 *
	 * @var bool
	 */
	protected static $_cache = true;

	/**
	 * Cache of all relation attribute names.
	 *
	 * @var array
	 */
	protected static $_relation_attribute_cache = array();

	/**
	 * Array of global query scopes for this model. Keyed by their string identifier.
	 *
	 * @var array
	 */
	protected static $_scopes = array();

	/// Instance Configuration

	/**
	 * Storage for the Model's attributes.
	 *
	 * May contain both the "raw" form of an attribute,
	 * i.e. a post ID, or the value form, i.e. a `WP_Post` object.
	 *
	 * @var array
	 */
	private $_attributes = array();

	/**
	 * A snapshot of the model's attributes when it is instantiated.
	 *
	 * Updated whenever the model is saved.
	 *
	 * @var array
	 */
	private $_original = array();

	/**
	 * Whitelist of attributes that are automatically filled.
	 *
	 * @see $_unguarded
	 *
	 * @var array
	 */
	protected $_fillable = array();

	/**
	 * Blacklist of attributes that are not automatically filled.
	 *
	 * @see $_unguarded
	 *
	 * @var array
	 */
	protected $_guarded = array();

	/**
	 * Map of relation name to results.
	 *
	 * @var array
	 */
	protected $_relations = array();

	/**
	 * Whether this model exists in the database.
	 *
	 * @var bool
	 */
	protected $_exists = false;

	/**
	 * Model constructor.
	 *
	 * @since 2.0
	 *
	 * @param array|object $data
	 */
	public function __construct( $data = array() ) {
		$this->init( (object) $data );
	}

	/**
	 * Initialize an object.
	 *
	 * @since 1.0
	 *
	 * @param \stdClass $data
	 */
	protected function init( \stdClass $data ) {

		static::boot_if_not_booted();

		$this->sync_original();
		$this->fill( (array) $data );
	}

	/**
	 * Boot this model if it has not been previously booted.
	 *
	 * Fires two model events, 'booting' and 'booted'.
	 *
	 * @since 2.0
	 */
	protected static function boot_if_not_booted() {

		if ( isset( static::$_booted[ get_called_class() ] ) ) {
			return;
		}

		static::$_booted[ get_called_class() ] = true;

		$instance = new static();

		$instance->fire_model_event( 'booting' );
		static::boot();
		$instance->fire_model_event( 'booted' );
	}

	/**
	 * Boot this model.
	 *
	 * This should perform any one time initialization code.
	 *
	 * @since 2.0
	 */
	protected static function boot() {
		static::boot_traits();
	}

	/**
	 * Boot any traits this model uses.
	 *
	 * This looks for and executes static methods named 'boot_TraitName'.
	 *
	 * @since 2.0
	 */
	protected static function boot_traits() {

		$class = get_called_class();

		// this method will return an empty array on PHP < 5.4
		$uses = class_uses_recursive( get_called_class() );

		foreach ( $uses as $trait ) {
			if ( method_exists( $class, $method = 'boot_' . class_basename( $trait ) ) ) {
				forward_static_call( array( $class, $method ) );
			}
		}
	}

	/**
	 * Fill this model's attributes.
	 *
	 * Filling a model in this way is used to prevent sensitive properties from being mass-assigned.
	 * For example when creating a new model like so: `MyModel::create( $_POST['model'] )`.
	 *
	 * If the model is unguarded, all attributes can be assigned via this method.
	 * Otherwise, the attribute can be filled according to {$_fillable} or {$_guarded}.
	 *
	 * @since   2.0
	 *
	 * @used-by IronBound\DB\Model::__construct()
	 * @used-by IronBound\DB\Model::create()
	 * @used-by IronBound\DB\Model::init()
	 * @used-by IronBound\DB\Query\FluentQuery::update_or_create()
	 *
	 * @param array $data
	 *
	 * @return $this
	 */
	public function fill( array $data = array() ) {

		foreach ( $data as $column => $value ) {

			if ( $this->is_fillable( $column ) ) {
				$this->set_attribute( $column, $value );
			}
		}

		return $this;
	}

	/**
	 * Make a function call with a given attribute guarded.
	 *
	 * @since 2.1.0
	 *
	 * @param string|array $attribute,...
	 * @param callable     $callback
	 */
	public function with_guarded( $attribute, $callback ) {

		if ( is_array( $attribute ) ) {
			$attributes = $attribute;
		} else {
			$attributes = func_get_args();
			$callback   = array_pop( $attributes );
		}

		$unguarded = static::$_unguarded;

		if ( $unguarded ) {
			static::$_unguarded = false;
		}

		$_fillable = $this->_fillable;
		$_guarded  = $this->_guarded;

		if ( $this->_fillable ) {
			$this->_fillable = array_diff( $this->_fillable, $attributes );

			// If the diff removes all fillable attributes we need to use _guarded.
			if ( ! $this->_fillable ) {
				$this->_guarded = $attributes;
			}
		} else {
			$this->_guarded = array_unique( array_merge( $this->_guarded, $attributes ) );
		}

		$callback( $this );

		$this->_fillable = $_fillable;
		$this->_guarded  = $_guarded;

		static::$_unguarded = $unguarded;
	}

	/**
	 * Make a function call with a given attribute unguarded.
	 *
	 * @since 2.1.0
	 *
	 * @param string   $attribute,...
	 * @param callable $callback
	 */
	public function with_unguarded( $attribute, $callback ) {

		if ( static::$_unguarded ) {
			$callback( $this );

			return;
		}

		if ( is_array( $attribute ) ) {
			$attributes = $attribute;
		} else {
			$attributes = func_get_args();
			$callback   = array_pop( $attributes );
		}

		$_fillable = $this->_fillable;
		$_guarded  = $this->_guarded;

		if ( $this->_fillable ) {
			$this->_fillable = array_merge( $this->_fillable, $attributes );
		} else {
			$this->_guarded = array_diff( $this->_guarded, $attributes );
		}

		$callback( $this );

		$this->_fillable = $_fillable;
		$this->_guarded  = $_guarded;

	}

	/**
	 * Determine if a given attribute is fillable.
	 *
	 * @since 2.0
	 *
	 * @param string $column
	 *
	 * @return bool
	 */
	protected function is_fillable( $column ) {

		if ( static::$_unguarded ) {
			return true;
		}

		if ( empty( $this->_fillable ) ) {
			return ! in_array( $column, $this->_guarded );
		} else {
			return in_array( $column, $this->_fillable );
		}
	}

	/**
	 * Set an attribute's value.
	 *
	 * This will call any defined mutators.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute Attribute name to update.
	 * @param mixed  $value     New value. In most cases this should be working form of the data.
	 *                          Not the storage version. For example a `WP_Post` object instead of post ID or a
	 *                          `DateTime` object instead of a 'Y-m-d H:i:s' string.
	 *
	 * @return $this
	 *
	 * @throws \OutOfBoundsException If the requested attribute does not exist.
	 */
	public function set_attribute( $attribute, $value ) {

		if ( $this->has_relation( $attribute ) ) {
			if ( is_object( $value ) ) {
				$this->set_relation_value( $attribute, $value );

				return $this;
			}

			unset( $this->_relations[ $attribute ] );
		} elseif ( ! array_key_exists( $attribute, static::table()->get_columns() ) ) {
			throw new \OutOfBoundsException(
				sprintf( "Requested attribute '%s' does not exist for '%s'.", $attribute, get_class( $this ) )
			);
		} elseif ( method_exists( $this, $this->get_mutator_method_for_attribute( $attribute ) ) ) {
			$value = call_user_func( array( $this, $this->get_mutator_method_for_attribute( $attribute ) ), $value );
		}

		$columns = static::table()->get_columns();
		$this->set_raw_attribute( $attribute, $columns[ $attribute ]->prepare_for_storage( $value ) );

		return $this;
	}

	/**
	 * Set an attribute's value in the attributes storage.
	 *
	 * @since   2.0
	 *
	 * @used-by IronBound\DB\Model::set_attribute()
	 *
	 * @param string $attribute Attribute name to update.
	 * @param mixed  $value     New value.
	 *
	 * @return $this
	 */
	public function set_raw_attribute( $attribute, $value ) {

		$attributes = $this->get_raw_attributes();

		$attributes[ $attribute ] = $value;
		$this->set_raw_attributes( $attributes );

		return $this;
	}

	/**
	 * Overwrite all of the attributes for this model.
	 *
	 * Guarding is ignored and mutators are not called.
	 *
	 * @since 2.0
	 *
	 * @see   IronBound\DB\Model::sync_original() for further documentation on the `$sync` parameter.
	 *
	 * @param array $attributes All attributes.
	 * @param bool  $sync       Whether to sync the model so it isn't seen as dirty.
	 *
	 * @return $this
	 */
	public function set_raw_attributes( array $attributes = array(), $sync = false ) {

		$this->_attributes = $attributes;

		if ( $sync ) {
			$this->sync_original();
		}

		return $this;
	}

	/**
	 * Get an attribute's or relation's value.
	 *
	 * If it is an attribute, any accessors will be called.
	 *
	 * If it is a relation, its results will be loaded and returned. The relation's results are cached.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute Attribute name.
	 *
	 * @return mixed
	 *
	 * @throws \OutOfBoundsException If the given attribute does not exist.
	 */
	public function get_attribute( $attribute ) {

		if ( $this->has_relation( $attribute ) ) {
			return $this->get_relation_value( $attribute );
		}

		if ( array_key_exists( $attribute, static::table()->get_columns() ) ) {
			return $this->get_attribute_value( $attribute );
		}

		throw new \OutOfBoundsException(
			sprintf( "Requested attribute '%s' does not exist for '%s'.", $attribute, get_class( $this ) )
		);
	}

	/**
	 * Get an attribute's value from storage.
	 *
	 * @since   2.0
	 *
	 * @used-by IronBound\DB\Model::get_attribute()
	 *
	 * @param string $attribute
	 *
	 * @return mixed
	 */
	public function get_raw_attribute( $attribute ) {

		$attributes = $this->get_raw_attributes();

		return isset( $attributes[ $attribute ] ) ? $attributes[ $attribute ] : null;
	}

	/**
	 * Get all of the model's attributes.
	 *
	 * Accessors will not be called. Relations are not included.
	 *
	 * @since 2.0
	 *
	 * @return array
	 */
	public function get_raw_attributes() {
		return $this->_attributes;
	}

	/**
	 * Retrieves an attribute's value and calls the accessor method if it exists.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute
	 *
	 * @return mixed
	 */
	protected function get_attribute_value( $attribute ) {

		$value = $this->get_attribute_from_array( $attribute );

		if ( method_exists( $this, $this->get_accessor_method_for_attribute( $attribute ) ) ) {
			$value = call_user_func( array( $this, $this->get_accessor_method_for_attribute( $attribute ) ), $value );
		}

		return $value;
	}

	/**
	 * Get an attribute's value from the internal attributes array.
	 *
	 * Will preferentially retrieve the value from the attribute value cache,
	 * and update the attribute value cache if the raw value is scalar.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute
	 *
	 * @return mixed|null
	 */
	protected function get_attribute_from_array( $attribute ) {

		$raw     = $this->get_raw_attribute( $attribute );
		$columns = static::table()->get_columns();

		return $columns[ $attribute ]->convert_raw_to_value( $raw );
	}

	/**
	 * Get the accessor method name for an attribute.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute
	 *
	 * @return string
	 */
	protected function get_accessor_method_for_attribute( $attribute ) {
		return "_access_{$attribute}";
	}

	/**
	 * Get the mutator method for an attribute.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute
	 *
	 * @return string
	 */
	protected function get_mutator_method_for_attribute( $attribute ) {
		return "_mutate_{$attribute}";
	}

	/**
	 * Check if a relation exists without loading the relation.
	 *
	 * This is only checking for a unique method name. The return value is not validated
	 * until it is ued by `get_relation()`.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute Relation name.
	 *
	 * @return bool
	 */
	protected function has_relation( $attribute ) {
		return method_exists( $this, "_{$attribute}_relation" );
	}

	/**
	 * Get all relations this model has.
	 *
	 * Will be cached on subsequent runs.
	 *
	 * @since 2.0
	 *
	 * @return array
	 */
	public function get_all_relations() {

		if ( isset( static::$_relation_attribute_cache[ get_class( $this ) ] ) ) {
			return static::$_relation_attribute_cache[ get_class( $this ) ];
		}

		$relations = array();
		$methods   = get_class_methods( $this );

		foreach ( $methods as $method ) {
			preg_match( '/^_(\S+)_relation$/', $method, $matches );

			if ( empty( $matches[1] ) ) {
				continue;
			}

			$relations[] = $matches[1];
			$matches     = null;
		}

		static::$_relation_attribute_cache[ get_class( $this ) ] = $relations;

		return $relations;
	}

	/**
	 * Get a relation's controller object.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute Relation name.
	 *
	 * @return Relation
	 *
	 * @throws \OutOfBoundsException If no relation exists by the given name.
	 * @throws \UnexpectedValueException If relation method returns an invalid value.
	 */
	public function get_relation( $attribute ) {

		if ( ! $this->has_relation( $attribute ) ) {
			throw new \OutOfBoundsException(
				sprintf( "Requested relation '%s' does not exist for '%s'.", $attribute, get_class( $this ) )
			);
		}

		$method   = "_{$attribute}_relation";
		$relation = $this->{$method}();

		if ( ! $relation instanceof Relation ) {
			throw new \UnexpectedValueException( 'Relation methods must return an IronBound\DB\Relations\Relation object.' );
		}

		return $relation;
	}

	/**
	 * Set the value for a relation.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute Relation name.
	 * @param mixed  $value     Relation value.
	 *
	 * @return $this
	 *
	 * @throws \OutOfBoundsException If no relation exists by the given name.
	 */
	public function set_relation_value( $attribute, $value ) {

		if ( ! $this->has_relation( $attribute ) ) {
			throw new \OutOfBoundsException(
				sprintf( "Requested relation '%s' does not exist for '%s'.", $attribute, get_class( $this ) )
			);
		}

		$this->_relations[ $attribute ] = $value;

		return $this;
	}

	/**
	 * Get a relation's value.
	 *
	 * Will load from the cache if possible. Otherwise, will get the relation controller and retrieve and
	 * cache the results.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute Relation name.
	 *
	 * @return Collection|Model|mixed
	 */
	protected function get_relation_value( $attribute ) {

		if ( array_key_exists( $attribute, $this->_relations ) ) {
			return $this->_relations[ $attribute ];
		}

		$relation = $this->get_relation( $attribute );
		$this->set_relation_value( $attribute, $relation->get_results() );

		return $this->get_relation_value( $attribute );
	}

	/**
	 * Check if a relation has already been loaded.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute Relation name.
	 *
	 * @return bool
	 */
	public function is_relation_loaded( $attribute ) {
		return array_key_exists( $attribute, $this->_relations );
	}

	/**
	 * Refresh the attributes on this model.
	 *
	 * This will fetch the latest data from either the cache or DB.
	 *
	 * @param bool $destroy_changes If true, will destroy local changes.
	 *
	 * @since 2.0.0
	 */
	public function refresh( $destroy_changes = false ) {

		if ( ! $this->exists() ) {
			return;
		}

		$data = (array) static::get_data_from_pk( $this->get_pk() );

		$this->_original = $data;

		if ( $destroy_changes ) {
			$this->set_raw_attributes( $data );
		}
	}

	/**
	 * Sync the model's original attributes with its current state.
	 *
	 * This will prevent any updates attributes as showing up as dirty.
	 *
	 * @since 2.0
	 *
	 * @return $this
	 */
	public function sync_original() {
		$this->_original = $this->get_raw_attributes();

		return $this;
	}

	/**
	 * Sync an individual attribute's value.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute
	 *
	 * @return $this
	 */
	public function sync_original_attribute( $attribute ) {
		$this->_original[ $attribute ] = $this->get_raw_attribute( $attribute );

		return $this;
	}

	/**
	 * Determine if the model as a whole or given attribute(s) have been modified.
	 *
	 * @since 2.0
	 *
	 * @param array|string...|null $attributes
	 *
	 * @return bool
	 */
	public function is_dirty( $attributes = null ) {

		$dirty = $this->get_dirty();

		if ( is_null( $attributes ) ) {
			return count( $dirty ) > 0;
		}

		if ( ! is_array( $attributes ) ) {
			$attributes = func_get_args();
		}

		foreach ( $attributes as $attribute ) {
			if ( array_key_exists( $attribute, $dirty ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the attributes that have been changed since the last sync.
	 *
	 * @since 2.0
	 *
	 * @return array Array of attribute names to changed values.
	 */
	public function get_dirty() {
		$dirty = array();

		foreach ( $this->get_raw_attributes() as $key => $value ) {
			if ( ! array_key_exists( $key, $this->_original ) ) {
				$dirty[ $key ] = $value;
			} elseif ( $value !== $this->_original[ $key ] && ! $this->original_is_numerically_equivalent( $key ) ) {
				$dirty[ $key ] = $value;
			}
		}

		return $dirty;
	}

	/**
	 * Determine if the new and old values for a given key are numerically equivalent.
	 *
	 * {@internal Helper method used for determining which attributes are dirty.}
	 *
	 * @since 2.0
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	protected function original_is_numerically_equivalent( $key ) {

		$current = $this->get_raw_attribute( $key );

		$original = $this->_original[ $key ];

		return is_numeric( $current ) && is_numeric( $original ) &&
		       strcmp( (string) $current, (string) $original ) === 0;
	}

	/**
	 * Retrieve an instance of this model by its primary key.
	 *
	 * @since 1.0
	 *
	 * @param int|string $pk Primary key of this record.
	 *
	 * @returns static|null
	 *
	 * @throws \InvalidArgumentException If `$pk` is not scalar.
	 */
	public static function get( $pk ) {

		if ( ! $pk ) {
			return null;
		}

		if ( ! is_scalar( $pk ) ) {
			throw new \InvalidArgumentException( 'Primary key must be scalar.' );
		}

		$data = static::get_data_from_pk( $pk );

		if ( $data ) {

			$object = new static( new \stdClass() );
			$object->set_raw_attributes( (array) $data, true );
			$object->_exists = true;

			if ( static::$_cache && ! static::is_data_cached( $pk ) ) {
				Cache::update( $object );
			}

			foreach ( static::$_eager_load as $eager_load ) {
				if ( ! $object->is_relation_loaded( $eager_load ) ) {
					$object->get_relation( $eager_load )->eager_load( array( $object ) );
				}
			}

			return $object;
		} else {
			return null;
		}
	}

	/**
	 * Create a new object from a query builder.
	 *
	 * This is used to bypass guarding when retrieve model data from the database.
	 *
	 * @since 2.0
	 *
	 * @param array $attributes
	 *
	 * @return static
	 */
	public static function from_query( array $attributes = array() ) {

		$instance = new static( new \stdClass() );
		$instance->set_raw_attributes( $attributes, true );
		$instance->_exists = true;

		if ( static::$_cache && ! static::is_data_cached( $instance->get_pk() ) ) {
			Cache::update( $instance );
		}

		return $instance;
	}

	/**
	 * Get data for a primary key.
	 *
	 * @since 1.0
	 *
	 * @param int|string $pk Primary key for this record.
	 *
	 * @return \stdClass|null
	 */
	protected static function get_data_from_pk( $pk ) {

		$data = static::$_cache ? Cache::get( $pk, static::get_cache_group() ) : null;

		if ( ! $data ) {
			$query = new FluentQuery( static::table() );
			$data  = $query->where( static::table()->get_primary_key(), '=', $pk )->first();
		}

		return $data ? (object) $data : null;
	}

	/**
	 * Check if data is cached.
	 *
	 * @since 1.0
	 *
	 * @param int|string $pk Primary key for this record.
	 *
	 * @return bool
	 */
	protected static function is_data_cached( $pk ) {

		if ( ! static::$_cache ) {
			return false;
		}

		$data = Cache::get( $pk, static::get_cache_group() );

		return ! empty( $data );
	}

	/**
	 * Get the table object for this model.
	 *
	 * This must be overwritten by sub-classes.
	 *
	 * @since 1.0
	 *
	 * @returns Table
	 */
	protected static function get_table() {
		// override this in child classes.
		throw new \UnexpectedValueException();
	}

	/**
	 * Update a certain value.
	 *
	 * @deprecated 2.0 This bypasses the attributes layer and as such is deprecated in 2.0.
	 *
	 * @since      1.0
	 *
	 * @param string $key   DB column to update.
	 * @param mixed  $value New value.
	 *
	 * @return bool
	 */
	protected function update( $key, $value ) {

		$columns = static::table()->get_columns();

		$data = array(
			$key => $columns[ $key ]->prepare_for_storage( $value )
		);

		$res = static::make_query_object()->update( $this->get_pk(), $data );

		if ( $res && static::$_cache ) {
			Cache::update( $this );
		}

		return $res;
	}

	/**
	 * Does this model exist in the database..
	 *
	 * @since 2.0
	 *
	 * @return bool
	 */
	public function exists() {
		return $this->_exists;
	}

	/**
	 * Persist this model's changes to the database.
	 *
	 * This will save all savable attributes like WP_Post objects or Models,
	 * and persist any loaded relations.
	 *
	 * The `saving` and `saved` events will be fired. If this model does not yet exist, the `creating` and `created`
	 * events will fire as well. Otherwise, the `updating` and `updated` events will fire.
	 *
	 * @since 2.0
	 *
	 * @param array $options Configure how the saving should be performed.
	 *                       Accepts 'exclude_relations' to allow for given relations to be excluded from persistence.
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function save( array $options = array() ) {

		if ( isset( $options['exclude_relations'] ) && ! is_array( $options['exclude_relations'] ) ) {
			$options['exclude_relations'] = (array) $options['exclude_relations'];
		}

		$columns = static::table()->get_columns();

		$options = wp_parse_args( $options, array(
			'exclude_relations' => array_filter( $this->get_all_relations(), function ( $attribute ) use ( $columns ) {
				return array_key_exists( $attribute, $columns );
			} )
		) );

		$this->fire_model_event( 'saving' );

		$this->save_has_foreign_relations();

		if ( $this->exists() ) {
			$saved = $this->do_save_as_update();
		} else {
			$saved = $this->do_save_as_insert();
		}

		$this->save_loaded_relations( $options['exclude_relations'] );

		if ( $saved ) {
			$this->finish_save();
		}

		return $saved;
	}

	/**
	 * Save the has foreign relations.
	 *
	 * These need to be saved early because otherwise they will be persisted to both
	 * the attribute value cache, and the relation cache.
	 *
	 * @since 2.0
	 */
	protected function save_has_foreign_relations() {

		foreach ( $this->_relations as $attribute => $value ) {
			$relation = $this->get_relation( $attribute );

			if ( $relation instanceof HasForeign && $value ) {

				$value = $relation->persist( $value );
				$pk    = $relation->get_pk_for_value( $value );

				$this->set_raw_attribute( $attribute, $pk );
				$this->_relations[ $attribute ] = $value;
			}
		}
	}

	/**
	 * Save loaded relations.
	 *
	 * @since 2.0
	 *
	 * @param array $exclude Relations to exclude from being saved.
	 */
	protected function save_loaded_relations( array $exclude = array() ) {
		foreach ( $this->_relations as $relation => $values ) {

			if ( in_array( $relation, $exclude ) ) {
				continue;
			}

			$relation_controller = $this->get_relation( $relation );
			$relation_controller->persist( $values );
		}
	}

	/**
	 * Save model as an insert query.
	 *
	 * @since 2.0
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	protected function do_save_as_insert() {

		if ( static::table() instanceof TimestampedTable ) {
			$time = $this->fresh_timestamp();

			$this->set_attribute( static::table()->get_created_at_column(), $time );
			$this->set_attribute( static::table()->get_updated_at_column(), $time );
		}

		$this->fire_model_event( 'creating' );

		$columns = static::table()->get_columns();
		$insert  = array();

		foreach ( $this->get_raw_attributes() as $attribute => $value ) {
			$insert[ $attribute ] = $columns[ $attribute ]->prepare_for_storage( $value );
		}

		$insert_id = static::make_query_object()->insert( $insert );

		if ( $insert_id ) {
			$this->set_raw_attribute( static::table()->get_primary_key(), $insert_id );
		}

		$default_columns_to_fill = array();

		foreach ( static::table()->get_columns() as $name => $column ) {

			if ( ! array_key_exists( $name, $this->get_raw_attributes() ) ) {
				$default_columns_to_fill[] = $name;
			}
		}

		if ( $default_columns_to_fill ) {

			$query          = new FluentQuery( static::table() );
			$default_values = $query->where( static::table()->get_primary_key(), '=', $this->get_pk() )
			                        ->select( $default_columns_to_fill )->first();

			if ( $default_values ) {
				foreach ( $default_values as $column => $value ) {
					$this->set_raw_attribute( $column, $value );
				}
			}
		}

		$this->_exists = true;

		if ( static::$_cache ) {
			Cache::update( $this );
		}

		$this->fire_model_event( 'created' );

		return true;
	}

	/**
	 * Save this model as an update query.
	 *
	 * @since 2.0
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	protected function do_save_as_update() {

		$dirty = $this->get_dirty();

		if ( ! $dirty ) {
			return true;
		}

		if ( static::table() instanceof TimestampedTable ) {
			$this->set_attribute( static::table()->get_updated_at_column(), $this->fresh_timestamp() );
			$dirty = $this->get_dirty();
		}

		$columns = static::table()->get_columns();

		$previous = array();
		$update   = array();

		foreach ( $dirty as $column => $value ) {

			if ( array_key_exists( $column, $this->_original ) ) {
				$previous[ $column ] = $this->_original[ $column ];
			}

			$update[ $column ] = $columns[ $column ]->prepare_for_storage( $value );
		}

		$this->fire_model_event( 'updating', array(
			'changed' => $dirty,
			'from'    => $previous
		) );

		$result = static::make_query_object()->update( $this->get_pk(), $update );

		if ( $result ) {

			if ( static::$_cache ) {
				Cache::update( $this );
			}

			$this->fire_model_event( 'updated', array(
				'changed' => $dirty,
				'from'    => $previous
			) );
		}

		return $result;
	}

	/**
	 * Perform cleanup after a save has occurred.
	 *
	 * @since 2.0
	 */
	protected function finish_save() {

		$this->fire_model_event( 'saved' );

		foreach ( $this->_relations as $attribute => $relation ) {
			if ( $relation instanceof Collection ) {
				$relation->clear_memory();
			}
		}

		$this->sync_original();
	}

	/**
	 * Delete this object from the database.
	 *
	 * Will fire `deleting` and `deleted` events.
	 *
	 * @since 1.0
	 *
	 * @throws Exception
	 */
	public function delete() {

		$this->fire_model_event( 'deleting' );

		foreach ( $this->get_all_relations() as $relation ) {
			$this->get_relation( $relation )->on_delete( $this );
		}

		static::make_query_object()->delete( $this->get_pk() );

		if ( static::$_cache ) {
			Cache::delete( $this );
		}

		$this->_exists = false;

		$this->fire_model_event( 'deleted' );
	}

	/**
	 * Create multiple models at once.
	 *
	 * @since 2.0.0
	 *
	 * @param array[]|static[] $to_insert Records to be inserted.
	 *
	 * @return static[]
	 *
	 * @throws \IronBound\DB\Exception
	 */
	public static function create_many( array $to_insert ) {

		$models = array();
		$data   = array();

		foreach ( $to_insert as $model ) {
			$model = $model instanceof static ? $model : new static ( $model );
			$model->fire_model_event( 'saving' );
			$model->fire_model_event( 'creating' );

			$models[] = $model;
			$data[]   = $model->get_raw_attributes();
		}

		$rows = static::make_query_object()->insert_many( $data );

		foreach ( $rows as $i => $row ) {
			$model = $models[ $i ];

			$model->set_raw_attributes( $row );
			$model->fire_model_event( 'created' );
			$model->fire_model_event( 'saved' );
			$model->sync_original();
		}

		return $models;
	}

	/**
	 * Create a fresh UTC timestamp.
	 *
	 * @since 2.0
	 *
	 * @return \DateTime
	 */
	protected function fresh_timestamp() {
		return new \DateTime( 'now', new \DateTimeZone( 'UTC' ) );
	}

	/**
	 * Create a new instance of this model.
	 *
	 * @since 2.0
	 *
	 * @param array $attributes
	 *
	 * @return static
	 */
	protected static function _do_create( array $attributes = array() ) {

		$instance = new static( $attributes );
		$instance->save();

		return $instance;
	}

	/**
	 * Make a query object.
	 *
	 * @since 1.2
	 *
	 * @return Query\Simple_Query|null
	 */
	protected static function make_query_object() {
		return static::$_db_manager->make_simple_query_object( static::table()->get_slug() );
	}

	/**
	 * Fire a model event.
	 *
	 * Will bail early if no event dispatcher is available.
	 *
	 * @since 2.0
	 *
	 * @param string $event     Event name.
	 * @param array  $arguments Additional arguments passed to the GenericEvent object.
	 */
	protected function fire_model_event( $event, $arguments = array() ) {

		if ( ! static::$_event_dispatcher ) {
			return;
		}

		$event = static::table()->get_slug() . ".$event";

		static::$_event_dispatcher->dispatch( $event, new GenericEvent( $this, $arguments ) );
	}

	/**
	 * Register a model event listener.
	 *
	 * @since 2.0
	 *
	 * @param string   $event
	 * @param callable $callback
	 * @param int      $priority
	 * @param int      $accepted_args
	 */
	public static function register_model_event( $event, $callback, $priority = 10, $accepted_args = 3 ) {

		if ( isset( static::$_event_dispatcher ) ) {
			$event = static::table()->get_slug() . ".$event";

			static::$_event_dispatcher->add_listener( $event, $callback, $priority, $accepted_args );
		}
	}

	/**
	 * Get the data we'd like to cache.
	 *
	 * This is a bit magical. It iterates through all of the table columns,
	 * and checks if a getter for that method exists. If so, it pulls in that
	 * value. Otherwise, it will pull in the default value. If you'd like to
	 * customize this you should override this function in your child model
	 * class.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_data_to_cache() {

		$data = $this->get_raw_attributes();

		$columns = static::table()->get_columns();

		foreach ( $data as $column => $value ) {
			if ( isset( $columns[ $column ] ) ) {
				$data[ $column ] = $columns[ $column ]->prepare_for_storage( $value );
			}
		}

		return $data;
	}

	/**
	 * Get the cache group for this record.
	 *
	 * By default this returns a string in the following format
	 * "df-{$table_slug}".
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public static function get_cache_group() {
		return static::table()->get_slug();
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * String representation of object
	 *
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 */
	public function serialize() {
		return serialize( array(
			'pk'       => $this->get_pk(),
			'fillable' => $this->_fillable,
			'original' => $this->_original,
		) );
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Constructs the object
	 *
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 *
	 * @param string $serialized <p>
	 *                           The string representation of the object.
	 *                           </p>
	 *
	 * @return void
	 */
	public function unserialize( $serialized ) {
		$data = unserialize( $serialized );

		$this->init( static::get_data_from_pk( $data['pk'] ) );
		$this->_fillable = $data['fillable'];
		$this->_original = $data['original'];
	}

	/**
	 * Get the table object.
	 *
	 * @since 2.0
	 *
	 * @return Table
	 */
	public static function table() {

		if ( ! $table = static::get_table() ) {
			throw new \UnexpectedValueException( sprintf( '%s::get_table() returned null.', get_called_class() ) );
		}

		return $table;
	}

	/**
	 * Register a global query scope.
	 *
	 * Global query scopes are automatically applied whenever the query() method is called.
	 *
	 * @since 2.0
	 *
	 * @param Scope|string $scope_or_identifier Either a Scope object or an identifier for an accompanying closure.
	 * @param Closure|null $closure             When not using a Scope object, an implementation for the scope.
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function register_global_scope( $scope_or_identifier, Closure $closure = null ) {

		if ( $scope_or_identifier instanceof Scope ) {
			self::$_scopes[ get_called_class() ][ get_class( $scope_or_identifier ) ] = $scope_or_identifier;
		} elseif ( is_string( $scope_or_identifier ) && $closure ) {
			self::$_scopes[ get_called_class() ][ $scope_or_identifier ] = $closure;
		} else {
			throw new \InvalidArgumentException();
		}
	}

	/**
	 * Unregister a global scope.
	 *
	 * @since 2.0
	 *
	 * @param string $identifier
	 */
	public static function unregister_global_scope( $identifier ) {
		unset( static::$_scopes[ get_called_class() ][ $identifier ] );
	}

	/**
	 * Get all registered global scopes for this Model.
	 *
	 * @since 2.0
	 *
	 * @return Scope[]|Closure[]
	 */
	public static function get_global_scopes() {
		return isset( self::$_scopes[ get_called_class() ] ) ? self::$_scopes[ get_called_class() ] : array();
	}

	/**
	 * Create a new query builder without any scopes.
	 *
	 * @since 2.0
	 *
	 * @return FluentQuery
	 */
	public static function query_with_no_global_scopes() {

		static::boot_if_not_booted();

		return FluentQuery::from_model( get_called_class() )->with( static::$_eager_load );
	}

	/**
	 * Create a new query builder without certain global scopes applied.
	 *
	 * @since 2.0
	 *
	 * @param array $scopes
	 *
	 * @return FluentQuery
	 */
	public static function without_global_scopes( array $scopes ) {

		$query = static::query_with_no_global_scopes();

		foreach ( static::get_global_scopes() as $id => $scope ) {

			if ( in_array( $id, $scopes ) ) {
				continue;
			}

			if ( $scope instanceof Scope ) {
				$scope->apply( $query );
			} else {
				$scope( $query );
			}
		}

		return $query;
	}

	/**
	 * Create a new query builder without a given scope.
	 *
	 * @since 2.0
	 *
	 * @param string $scope
	 *
	 * @return FluentQuery
	 */
	public static function without_global_scope( $scope ) {
		return static::without_global_scopes( array( $scope ) );
	}

	/**
	 * Initialize a new query object with the configured eager loaded relations.
	 *
	 * @since 2.0
	 *
	 * @return FluentQuery
	 */
	public static function query() {
		return static::without_global_scopes( array() );
	}

	/**
	 * Retrieve all instances of this model.
	 *
	 * @since 2.0
	 *
	 * @return Collection
	 */
	public static function all() {
		return static::query()->results();
	}

	/**
	 * Perform a query with a given set of relations.
	 *
	 * @since 2.0
	 *
	 * @param $relations,...
	 *
	 * @return FluentQuery
	 */
	public static function with( $relations ) {

		$query = FluentQuery::from_model( get_called_class() );

		call_user_func_array( array( $query, 'with' ), func_get_args() );

		return $query;
	}

	/**
	 * Convert the model to an array.
	 *
	 * @since 2.0
	 *
	 * @return array
	 */
	public function to_array() {

		$attributes = array();

		foreach ( static::table()->get_columns() as $attribute => $column ) {
			$attributes[ $attribute ] = $this->get_attribute( $attribute );
		}

		return $attributes;
	}

	/**
	 * Magic method to retrieve an attribute from the model.
	 *
	 * @since 2.0
	 *
	 * @param string $name
	 *
	 * @return mixed|null
	 *
	 * @throws \OutOfBoundsException If no attribute exists with the given name.
	 */
	public function __get( $name ) {
		return $this->get_attribute( $name );
	}

	/**
	 * Magic method to set an attribute on the model.
	 *
	 * @since 2.0
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function __set( $name, $value ) {
		$this->set_attribute( $name, $value );
	}

	/**
	 * Magic method to determine if an attribute exists on the model.
	 *
	 * @since 2.0
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset( $name ) {
		try {
			return $this->get_attribute( $name ) !== null;
		} catch ( \OutOfBoundsException $e ) {
			return false;
		}
	}

	/**
	 * Magic method to remove an attribute from the model.
	 *
	 * @since 2.0
	 *
	 * @param string $name
	 */
	public function __unset( $name ) {
		$this->set_attribute( $name, null );
	}

	/**
	 * Magic method to register event listeners or create a new model.
	 *
	 * @since 2.0
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return mixed
	 *
	 * @throws \BadMethodCallException If invalid method name.
	 */
	public static function __callStatic( $name, $arguments ) {

		if ( in_array( $name, array(
			'saved',
			'saving',
			'updated',
			'updating',
			'created',
			'creating',
			'deleted',
			'deleting'
		) ) ) {
			$instance = new static;

			array_unshift( $arguments, $name );

			return call_user_func_array( array( $instance, 'register_model_event' ), $arguments );
		}

		if ( $name === 'create' ) {

			$instance = new static;

			return call_user_func_array( array( $instance, '_do_create' ), $arguments );
		}

		throw new \BadMethodCallException( "__callStatic() failed. No method found for name '$name'." );
	}

	/**
	 * Set the DB Manager to use for this model.
	 *
	 * @since 1.2
	 *
	 * @param Manager $manager
	 */
	public static function set_db_manager( Manager $manager ) {
		static::$_db_manager = $manager;
	}

	/**
	 * Set the EventDispatcher globally.
	 *
	 * @since 2.0
	 *
	 * @param EventDispatcher $dispatcher MUST be configured without a prefix.
	 */
	public static function set_event_dispatcher( EventDispatcher $dispatcher ) {
		static::$_event_dispatcher = $dispatcher;
	}

	/**
	 * Get the EventDispatcher.
	 *
	 * @since 2.0
	 *
	 * @return EventDispatcher
	 */
	public static function get_event_dispatcher() {
		return static::$_event_dispatcher;
	}
}
