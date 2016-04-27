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

use Doctrine\Common\Collections\Collection;
use IronBound\Cache\Cacheable;
use IronBound\Cache\Cache;
use IronBound\DB\Relations\Relation;
use IronBound\DB\Table\Column\Contracts\Savable;
use IronBound\DB\Table\Table;
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

	/// Model Configuration

	/**
	 * Whether all attributes can be mass filled via the constructor
	 * or the magic create() method.
	 *
	 * @var bool
	 */
	protected static $_unguarded = false;

	/// Instance Configuration

	/**
	 * Whitelist of attributes that are automatically filled.
	 *
	 * @var array
	 */
	protected $_fillable = array();

	/**
	 * Blacklist of attributes that are not automatically filled.
	 *
	 * @var array
	 */
	protected $_guarded = array();

	/**
	 * Raw attribute data.
	 *
	 * @var array
	 */
	protected $_attributes = array();

	/**
	 * Original state of the model.
	 *
	 * Updated whenever the model is saved.
	 *
	 * @var array
	 */
	protected $_original = array();

	/**
	 * Cache of attribute values.
	 *
	 * @var array
	 */
	protected $_attribute_value_cache = array();

	/**
	 * Map of relation name to results.
	 *
	 * @var array
	 */
	protected $_relations = array();

	/**
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

		if ( ! isset( static::$_db_manager ) ) {
			static::$_db_manager = new Manager();
		}
	}

	/**
	 * Fill data on this model automatically.
	 *
	 * @since 2.0
	 *
	 * @param array $data
	 *
	 * @return $this
	 */
	protected function fill( array $data = array() ) {

		foreach ( $data as $column => $value ) {

			if ( $this->is_fillable( $column ) ) {
				$this->set_attribute( $column, $value );
			}
		}

		return $this;
	}

	/**
	 * Set the attribute data for this model, ignoring guarding.
	 *
	 * @since 2.0
	 *
	 * @param array $attributes
	 * @param bool  $sync
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
	 * Set an attribute.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function set_attribute( $attribute, $value ) {

		unset( $this->_attribute_value_cache[ $attribute ] );

		if ( $this->has_set_mutator( $attribute ) ) {
			return $this->call_set_mutator( $attribute, $value );
		}

		$this->_attributes[ $attribute ] = $value;

		return $this;
	}

	/**
	 * Get an attribute value.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute
	 *
	 * @return mixed|null
	 */
	public function get_attribute( $attribute ) {

		if ( array_key_exists( $attribute, $this->_attributes ) || $this->has_get_mutator( $attribute ) ) {
			return $this->get_attribute_value( $attribute );
		}

		if ( $this->has_relation( $attribute ) ) {
			return $this->get_relation_value( $attribute );
		}

		return null;
	}

	/**
	 * Get an attribute's value.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute
	 *
	 * @return mixed
	 */
	protected function get_attribute_value( $attribute ) {

		$value = $this->get_attribute_from_array( $attribute );

		if ( $this->has_get_mutator( $attribute ) ) {
			$value = $this->call_get_mutator( $attribute, $value );
		}

		return $value;
	}

	/**
	 * Get an attribute's value from the internal attributes array.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute
	 *
	 * @return mixed|null
	 */
	protected function get_attribute_from_array( $attribute ) {

		if ( array_key_exists( $attribute, $this->_attribute_value_cache ) ) {
			return $this->_attribute_value_cache[ $attribute ];
		} elseif ( array_key_exists( $attribute, $this->_attributes ) ) {
			$value = $this->_attributes[ $attribute ];

			// only update the attribute value cache if we have a raw value from the db
			if ( is_scalar( $value ) ) {
				$columns = static::get_table()->get_columns();
				$value   = $columns[ $attribute ]->convert_raw_to_value( $value );

				$this->_attribute_value_cache[ $attribute ] = $value;
			}

			return $value;
		} else {
			return null;
		}
	}

	/**
	 * Check if a get mutator exists for a given attribute.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute
	 *
	 * @return bool
	 */
	protected function has_get_mutator( $attribute ) {
		return method_exists( $this, "_get_{$attribute}" );
	}

	/**
	 * Call the get mutator for a given attribute.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	protected function call_get_mutator( $attribute, $value ) {
		$method = "_get_{$attribute}";

		return $this->{$method}( $value );
	}

	/**
	 * Check if a set mutator exists for a given attribute.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute
	 *
	 * @return bool
	 */
	protected function has_set_mutator( $attribute ) {
		return method_exists( $this, "_set_{$attribute}" );
	}

	/**
	 * Call the set mutator for a given attribute.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	protected function call_set_mutator( $attribute, $value ) {
		$method = "_set_{$attribute}";

		return $this->{$method}( $value );
	}

	/**
	 * Check if a relation exists.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute
	 *
	 * @return bool
	 */
	protected function has_relation( $attribute ) {
		return method_exists( $this, "_{$attribute}_relation" );
	}


	/**
	 * Get a relation value.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute
	 *
	 * @return Collection|Model|mixed
	 */
	protected function get_relation_value( $attribute ) {

		if ( array_key_exists( $attribute, $this->_relations ) ) {
			return $this->_relations[ $attribute ];
		}

		$relation = $this->get_relation_from_function( $attribute );

		$this->_relations[ $attribute ] = $relation->get_results();

		return $this->get_relation_value( $attribute );
	}

	/**
	 * Get a relation object by its function.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute
	 *
	 * @return Relation
	 */
	protected function get_relation_from_function( $attribute ) {

		$method   = "_{$attribute}_relation";
		$relation = $this->{$method}();

		if ( ! $relation instanceof Relation ) {
			throw new \UnexpectedValueException( 'Relation methods must return an IronBound\DB\Relations\Relation object.' );
		}

		return $relation;
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
	 * Sync the model's original attributes with its current state.
	 *
	 * @since 2.0
	 *
	 * @return $this
	 */
	public function sync_original() {
		$this->_original = $this->_attributes;

		return $this;
	}

	/**
	 * Sync an individual attribute.
	 *
	 * @since 2.0
	 *
	 * @param string $attribute
	 *
	 * @return $this
	 */
	public function sync_original_attribute( $attribute ) {
		$this->_original[ $attribute ] = $this->_attributes[ $attribute ];

		return $this;
	}


	/**
	 * Determine if the model or given attribute(s) have been modified.
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
	 * Get the attributes that have been changed since last sync.
	 *
	 * @since 2.0
	 *
	 * @return array
	 */
	public function get_dirty() {
		$dirty = array();

		foreach ( $this->_attributes as $key => $value ) {
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
	 * @since 2.0
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	protected function original_is_numerically_equivalent( $key ) {

		$current = $this->_attributes[ $key ];

		$original = $this->_original[ $key ];

		return is_numeric( $current ) && is_numeric( $original ) &&
		       strcmp( (string) $current, (string) $original ) === 0;
	}

	/**
	 * Retrieve this object.
	 *
	 * @since 1.0
	 *
	 * @param int|string $pk Primary key of this record.
	 *
	 * @returns static|null
	 */
	public static function get( $pk ) {

		$data = static::get_data_from_pk( $pk );

		if ( $data ) {

			$object = new static( new \stdClass() );
			$object->set_raw_attributes( (array) $data, true );
			$object->_exists = true;

			if ( ! static::is_data_cached( $pk ) ) {
				Cache::update( $object );
			}

			return $object;
		} else {
			return null;
		}
	}

	/**
	 * Create a new object from a list of attributes.
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

		if ( ! static::is_data_cached( $instance->get_pk() ) ) {
			Cache::update( $instance );
		}

		return $instance;
	}

	/**
	 * Convert an array of raw data to their corresponding values.
	 *
	 * @since 2.0
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected static function convert_raw_data_to_values( $data ) {

		$columns = static::get_table()->get_columns();
		$mapped  = array();

		foreach ( (array) $data as $column => $value ) {
			$mapped[ $column ] = $columns[ $column ]->convert_raw_to_value( $value, $data );
		}

		return $mapped;
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

		$data = Cache::get( $pk, static::get_cache_group() );

		if ( ! $data ) {
			$data = static::make_query_object()->get( $pk );
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

		$data = Cache::get( $pk, static::get_cache_group() );

		return ! empty( $data );
	}

	/**
	 * Init an object.
	 *
	 * @since 1.0
	 *
	 * @param \stdClass $data
	 */
	protected function init( \stdClass $data ) {

		$this->sync_original();
		$this->fill( (array) $data );
		$this->_exists = (bool) $this->get_pk();
	}

	/**
	 * Get the table object for this model.
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
	 * @since 1.0
	 *
	 * @param string $key   DB column to update.
	 * @param mixed  $value New value.
	 *
	 * @return bool
	 */
	protected function update( $key, $value ) {

		$columns = static::get_table()->get_columns();

		$data = array(
			$key => $columns[ $key ]->prepare_for_storage( $value )
		);

		$res = static::make_query_object()->update( $this->get_pk(), $data );

		if ( $res ) {
			Cache::update( $this );
		}

		return $res;
	}

	/**
	 * Does this model exist yet.
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
	 * @since 2.0
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function save() {

		$this->fire_model_event( 'saving' );

		$this->save_savable_attributes();
		$this->save_loaded_relations();

		if ( $this->exists() ) {
			$saved = $this->do_save_as_update();
		} else {
			$saved = $this->do_save_as_insert();
		}

		if ( $saved ) {
			$this->finish_save();
		}

		return $saved;
	}

	/**
	 * Save all savable attributes.
	 *
	 * @since 2.0
	 */
	protected function save_savable_attributes() {

		$columns = static::get_table()->get_columns();

		foreach ( $this->_attributes as $attribute => $value ) {

			$column = $columns[ $attribute ];

			$value = $this->get_attribute_from_array( $attribute );

			if ( $column instanceof Savable && is_object( $value ) ) {
				$this->_attributes[ $attribute ] = $column->save( $value );
			}
		}
	}

	/**
	 * Save loaded relations.
	 *
	 * @since 2.0
	 */
	protected function save_loaded_relations() {
		foreach ( $this->_relations as $relation ) {

			if ( $relation instanceof Model ) {
				$relation->save();
			} elseif ( $relation instanceof Collection ) {
				foreach ( $relation as $model ) {

					// safety, the Collection should always return Models
					if ( $model instanceof Model ) {
						$model->save();
					}
				}
			}
		}
	}

	/**
	 * Save model as an update query.
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

		$previous = array();

		foreach ( $dirty as $column => $value ) {

			if ( array_key_exists( $column, $this->_original ) ) {
				$previous[ $column ] = $this->_original[ $column ];
			}
		}

		$this->fire_model_event( 'updating', array(
			'changed' => $dirty,
			'from'    => $previous
		) );

		$result = static::make_query_object()->update( $this->get_pk(), $dirty );

		if ( $result ) {
			Cache::update( $this );

			$this->fire_model_event( 'updated', array(
				'changed' => $dirty,
				'from'    => $previous
			) );
		}

		return $result;
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

		$this->fire_model_event( 'creating' );

		$insert_id = static::make_query_object()->insert( $this->_attributes );

		if ( $insert_id ) {
			$this->set_attribute( static::get_table()->get_primary_key(), $insert_id );
		}

		$default_columns_to_fill = array();

		foreach ( static::get_table()->get_columns() as $name => $column ) {

			if ( ! array_key_exists( $name, $this->_attributes ) ) {
				$default_columns_to_fill[] = $name;
			}
		}

		$default_values = (array) static::make_query_object()->get(
			$this->get_pk(), $default_columns_to_fill
		);

		foreach ( $default_values as $column => $value ) {
			$this->set_attribute( $column, $value );
		}

		$this->_exists = true;

		Cache::update( $this );

		$this->fire_model_event( 'created' );

		return true;
	}

	/**
	 * Perform cleanup after a save has occurred.
	 *
	 * @since 2.0
	 */
	protected function finish_save() {

		$this->fire_model_event( 'saved' );

		$this->sync_original();
	}

	/**
	 * Delete this object.
	 *
	 * @since 1.0
	 *
	 * @throws Exception
	 */
	public function delete() {

		$this->fire_model_event( 'deleting' );

		static::make_query_object()->delete( $this->get_pk() );

		Cache::delete( $this );

		$this->fire_model_event( 'deleted' );
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
		return static::$_db_manager->make_simple_query_object( static::get_table()->get_slug() );
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

		$event = static::get_table()->get_slug() . ".$event";

		static::$_event_dispatcher->dispatch( $event, new GenericEvent( $this, $arguments ) );
	}

	/**
	 * Register a model event.
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
			$event = static::get_table()->get_slug() . ".$event";

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

		$data = $this->_attributes;

		$columns = static::get_table()->get_columns();

		foreach ( $data as $column => $value ) {
			$data[ $column ] = $columns[ $column ]->prepare_for_storage( $value );
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
		return static::get_table()->get_slug();
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
	 * Magic method to retrieve an attribute from the model.
	 *
	 * @since 2.0
	 *
	 * @param string $name
	 *
	 * @return mixed|null
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
		return $this->get_attribute( $name ) !== null;
	}

	/**
	 * Magic method to remove an attribute from the model.
	 *
	 * @since 2.0
	 *
	 * @param string $name
	 */
	public function __unset( $name ) {
		$this->_attributes[ $name ] = null;
	}

	/**
	 * @inheritDoc
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
	 * @param EventDispatcher $dispatcher
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
