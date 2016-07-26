<?php
/**
 * Collection class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB;

use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\ClosureExpressionVisitor;
use Doctrine\Common\Collections\Selectable;
use IronBound\DB\Saver\ModelSaver;
use IronBound\DB\Saver\Saver;

/**
 * Class Collection
 * @package IronBound\DB\Collections
 */
class Collection implements DoctrineCollection, Selectable {

	/**
	 * @var array
	 */
	protected $elements = array();

	/**
	 * Map of primary keys to indexes in the $elements array.
	 *
	 * This exists to provide O(1) lookup for models by their PK.
	 *
	 * @var array
	 */
	protected $pk_map = array();

	/**
	 * @var DoctrineCollection
	 */
	protected $added;

	/**
	 * @var DoctrineCollection
	 */
	protected $removed;

	/**
	 * @var bool
	 */
	protected $keep_memory = false;

	/**
	 * @var Saver
	 */
	protected $saver;

	/**
	 * ModelCollection constructor.
	 *
	 * @param DoctrineCollection|array $collection
	 * @param bool                     $keep_memory
	 * @param Saver                    $saver
	 */
	public function __construct( $collection = array(), $keep_memory = false, Saver $saver = null ) {

		if ( $collection instanceof DoctrineCollection ) {
			$collection = $collection->toArray();
		}

		$this->elements    = $collection;
		$this->keep_memory = $keep_memory;
		$this->saver       = $saver ?: new ModelSaver();

		$this->added   = new ArrayCollection();
		$this->removed = new ArrayCollection();

		foreach ( $this->elements as $i => $element ) {
			if ( $pk = $this->saver->get_pk( $element ) ) {
				$this->pk_map[ $pk ] = $i;
			}
		}
	}

	/**
	 * Clear the memory of added or removed elements.
	 *
	 * @since 2.0
	 */
	public function clear_memory() {
		$this->added->clear();
		$this->removed->clear();
	}

	/**
	 * Configure the collection to keep memory of added or removed elements.
	 *
	 * @since 2.0
	 *
	 * @param bool $keep_memory
	 *
	 * @return bool
	 */
	public function keep_memory( $keep_memory = true ) {

		$prev = $this->keep_memory;

		$this->keep_memory = $keep_memory;

		return $prev;
	}

	/**
	 * Get the models that have been added to this collection.
	 *
	 * This does not include models that the collection were instantiated with.
	 *
	 * @since 2.0
	 *
	 * @return DoctrineCollection
	 */
	public function get_added() {
		return $this->added;
	}

	/**
	 * Get the models that have been removed from this collection.
	 *
	 * @since 2.0
	 *
	 * @return DoctrineCollection
	 */
	public function get_removed() {
		return $this->removed;
	}

	/**
	 * Perform operations on the collection without remembering them.
	 *
	 * @since 2.0
	 *
	 * @param Closure $callback This ModelCollection is passed as the first parameter.
	 */
	public function dont_remember( Closure $callback ) {

		$prev = $this->keep_memory( false );

		$callback( $this );

		$this->keep_memory( $prev );
	}

	/**
	 * Retrieve a model by its primary key.
	 *
	 * @since 2.0
	 *
	 * @param string|int $pk
	 *
	 * @return Model|mixed|null
	 */
	public function get_model( $pk ) {

		$i = $this->get_model_index( $pk );

		if ( $i === - 1 ) {
			return null;
		}

		return $this->elements[ $i ];
	}

	/**
	 * Remove a model from the collection.
	 *
	 * @since 2.0
	 *
	 * @param string|int $pk
	 *
	 * @return bool
	 */
	public function remove_model( $pk ) {

		$i = $this->get_model_index( $pk );

		if ( $i === - 1 ) {
			return false;
		}

		$this->remove( $i );

		return true;
	}

	/**
	 * Get the index of a model.
	 *
	 * @since 2.0
	 *
	 * @param string|int $pk
	 *
	 * @return int
	 */
	protected function get_model_index( $pk ) {

		if ( isset( $this->pk_map[ $pk ] ) ) {
			return $this->pk_map[ $pk ];
		}

		foreach ( $this->elements as $i => $element ) {

			if ( $this->saver->get_pk( $element ) === $pk ) {

				$this->pk_map[ $pk ] = $i;

				return $i;
			}
		}

		return - 1;
	}

	/**
	 * Save all items in the collection.
	 *
	 * @since 2.0
	 *
	 * @param array $options
	 */
	public function save( array $options = array() ) {

		foreach ( $this->elements as $element ) {
			$this->saver->save( $element, $options );
		}
	}

	/**
	 * Get the saver backing this collection.
	 *
	 * @since 2.0
	 *
	 * @return Saver
	 */
	public function get_saver() {
		return $this->saver;
	}

	/**
	 * @inheritDoc
	 */
	public function add( $element ) {

		$this->elements[] = $element;

		if ( $this->keep_memory ) {
			$this->added[] = $element;
		}

		if ( $pk = $this->saver->get_pk( $element ) ) {
			$keys = $this->getKeys();

			$this->pk_map[ $pk ] = end( $keys );
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function clear() {

		$this->removed  = $this->elements;
		$this->elements = array();
		$this->pk_map   = array();
	}

	/**
	 * @inheritDoc
	 */
	public function contains( $element ) {

		if ( $pk = $this->saver->get_pk( $element ) ) {
			return (bool) $this->get_model( $pk );
		} else {
			return in_array( $element, $this->elements, true );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function isEmpty() {
		return $this->count() === 0;
	}

	/**
	 * @inheritDoc
	 */
	public function remove( $key ) {

		$model = $this->get( $key );

		if ( $model && $pk = $this->saver->get_pk( $model ) ) {
			unset( $this->pk_map[ $pk ] );
		}

		if ( $this->keep_memory ) {
			$this->removed[] = $model;
		}

		unset( $this->elements[ $key ] );
	}

	/**
	 * @inheritDoc
	 */
	public function removeElement( $element ) {

		if ( $pk = $this->saver->get_pk( $element ) ) {

			if ( $this->keep_memory ) {
				$this->removed[] = $element;
			}

			if ( isset( $this->pk_map[ $pk ] ) ) {
				$i = $this->pk_map[ $pk ];

				unset( $this->elements[ $i ] );
				unset( $this->pk_map[ $pk ] );
			} else {

				foreach ( $this->elements as $i => $element ) {

					if ( $this->saver->get_pk( $element ) === $pk ) {
						unset( $this->elements[ $i ] );

						return;
					}
				}
			}
		} else {
			$i = array_search( $element, $this->elements, true );

			if ( $i !== false ) {
				unset( $this->elements[ $i ] );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function containsKey( $key ) {
		return isset( $this->elements[ $key ] ) ? true : false;
	}

	/**
	 * @inheritDoc
	 */
	public function get( $key ) {
		return isset( $this->elements[ $key ] ) ? $this->elements[ $key ] : null;
	}

	/**
	 * @inheritDoc
	 */
	public function getKeys() {
		return array_keys( $this->elements );
	}

	/**
	 * @inheritDoc
	 */
	public function getValues() {
		return array_values( $this->elements );
	}

	/**
	 * @inheritDoc
	 */
	public function set( $key, $value ) {

		$this->elements[ $key ] = $value;

		if ( $this->keep_memory ) {
			$this->added[] = $value;
		}

		if ( $pk = $this->saver->get_pk( $value ) ) {
			$this->pk_map[ $pk ] = $key;
		} else {
			$i = array_search( $key, $this->pk_map, true );

			if ( $i !== false ) {
				unset( $this->pk_map[ $i ] );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function toArray() {
		return $this->elements;
	}

	/**
	 * @inheritDoc
	 */
	public function first() {
		return reset( $this->elements );
	}

	/**
	 * @inheritDoc
	 */
	public function last() {
		return end( $this->elements );
	}

	/**
	 * @inheritDoc
	 */
	public function key() {
		return key( $this->elements );
	}

	/**
	 * @inheritDoc
	 */
	public function current() {
		return current( $this->elements );
	}

	/**
	 * @inheritDoc
	 */
	public function next() {
		return next( $this->elements );
	}

	/**
	 * @inheritDoc
	 */
	public function exists( Closure $p ) {

		foreach ( $this->elements as $key => $element ) {
			if ( $p( $key, $element ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @inheritDoc
	 *
	 * @return static
	 */
	public function filter( Closure $p ) {
		return new static( array_filter( $this->elements, $p ), $this->keep_memory, $this->saver );
	}

	/**
	 * @inheritDoc
	 */
	public function forAll( Closure $p ) {
		foreach ( $this->elements as $key => $element ) {
			if ( ! $p( $key, $element ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @inheritDoc
	 *
	 * @return static
	 */
	public function map( Closure $func ) {
		return new static( array_map( $func, $this->elements ), $this->keep_memory, $this->saver );
	}

	/**
	 * @inheritDoc
	 *
	 * @return static[]
	 */
	public function partition( Closure $p ) {
		$matches = $noMatches = array();

		foreach ( $this->elements as $key => $element ) {
			if ( $p( $key, $element ) ) {
				$matches[ $key ] = $element;
			} else {
				$noMatches[ $key ] = $element;
			}
		}

		return array(
			new static( $matches, $this->keep_memory, $this->saver ),
			new static( $noMatches, $this->keep_memory, $this->saver )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function indexOf( $element ) {

		if ( $pk = $this->saver->get_pk( $element ) ) {

			$i = $this->get_model_index( $pk );

			if ( $i !== - 1 ) {
				return $i;
			}
		}

		return array_search( $element, $this->elements, true );
	}

	/**
	 * @inheritDoc
	 */
	public function slice( $offset, $length = null ) {
		return array_slice( $this->elements, $offset, $length, true );
	}

	/**
	 * @inheritDoc
	 */
	public function getIterator() {
		return new \ArrayIterator( $this->elements );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetExists( $offset ) {
		return $this->containsKey( $offset );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetGet( $offset ) {
		return $this->get( $offset );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetSet( $offset, $value ) {

		if ( ! isset( $offset ) ) {
			return $this->add( $value );
		}

		$this->set( $offset, $value );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetUnset( $offset ) {
		return $this->remove( $offset );
	}

	/**
	 * @inheritDoc
	 */
	public function count() {
		return count( $this->elements );
	}

	/**
	 * @inheritDoc
	 */
	public function matching( Criteria $criteria ) {

		$expr     = $criteria->getWhereExpression();
		$filtered = $this->elements;

		if ( $expr ) {
			$visitor  = new ClosureExpressionVisitor();
			$filter   = $visitor->dispatch( $expr );
			$filtered = array_filter( $filtered, $filter );
		}

		if ( $orderings = $criteria->getOrderings() ) {
			foreach ( array_reverse( $orderings ) as $field => $ordering ) {
				$next = ClosureExpressionVisitor::sortByField( $field, $ordering == Criteria::DESC ? - 1 : 1 );
			}

			uasort( $filtered, $next );
		}

		$offset = $criteria->getFirstResult();
		$length = $criteria->getMaxResults();

		if ( $offset || $length ) {
			$filtered = array_slice( $filtered, (int) $offset, $length );
		}

		return new static( $filtered, $this->keep_memory, $this->saver );
	}
}

