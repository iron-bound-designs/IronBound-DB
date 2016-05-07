<?php
/**
 * Collection class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Collections;

use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use IronBound\DB\Saver\ModelSaver;
use IronBound\DB\Saver\Saver;

/**
 * Class Collection
 * @package IronBound\DB\Collections
 */
class Collection implements DoctrineCollection, Selectable {

	/**
	 * @var \Doctrine\Common\Collections\Collection|Selectable
	 */
	protected $collection;

	/**
	 * @var array
	 */
	protected $removed = array();

	/**
	 * @var array
	 */
	protected $added = array();

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
	 * @param DoctrineCollection|Selectable|array $collection
	 * @param bool                                $keep_memory
	 * @param Saver                               $saver
	 */
	public function __construct( $collection = array(), $keep_memory = false, Saver $saver = null ) {

		if ( $collection instanceof DoctrineCollection && ! $collection instanceof Selectable ) {
			throw new \InvalidArgumentException( '$collection must implement Selectable and Collection.' );
		}

		if ( $collection instanceof Collection ) {
			$this->collection = $collection;
		} else {
			$this->collection = new ArrayCollection( $collection );
		}

		$this->keep_memory = $keep_memory;
		$this->saver       = $saver ?: new ModelSaver();
	}

	/**
	 * Clear the memory of added or removed elements.
	 *
	 * @since 2.0
	 */
	public function clear_memory() {
		$this->added   = array();
		$this->removed = array();
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
	 * @return array
	 */
	public function get_added() {
		return $this->added;
	}

	/**
	 * Get the models that have been removed from this collection.
	 *
	 * @since 2.0
	 *
	 * @return array
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
	 * @inheritDoc
	 */
	public function add( $element ) {

		if ( $this->saver->get_pk( $element ) ) {
			$this->set( $this->saver->get_pk( $element ), $element );
		} else {
			$this->collection->add( $element );
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function clear() {
		$this->collection->clear();
	}

	/**
	 * @inheritDoc
	 */
	public function contains( $element ) {

		if ( $this->saver->get_pk( $element ) ) {
			return $this->collection->containsKey( $this->saver->get_pk( $element ) );
		} else {
			return $this->collection->contains( $element );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function isEmpty() {
		return $this->collection->isEmpty();
	}

	/**
	 * @inheritDoc
	 */
	public function remove( $key ) {

		if ( $this->keep_memory ) {
			$this->removed[ $key ] = $this->get( $key );
		}

		return $this->collection->remove( $key );
	}

	/**
	 * @inheritDoc
	 */
	public function removeElement( $element ) {

		if ( $this->saver->get_pk( $element ) ) {
			return $this->collection->remove( $this->saver->get_pk( $element ) );
		} else {
			return $this->collection->removeElement( $element );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function containsKey( $key ) {
		return $this->collection->containsKey( $key );
	}

	/**
	 * @inheritDoc
	 */
	public function get( $key ) {
		return $this->collection->get( $key );
	}

	/**
	 * @inheritDoc
	 */
	public function getKeys() {
		return $this->collection->getKeys();
	}

	/**
	 * @inheritDoc
	 */
	public function getValues() {
		return $this->collection->getValues();
	}

	/**
	 * @inheritDoc
	 */
	public function set( $key, $value ) {

		if ( $this->keep_memory ) {
			$this->added[ $key ] = $value;
		}

		$this->collection->set( $key, $value );
	}

	/**
	 * @inheritDoc
	 */
	public function toArray() {
		return $this->collection->toArray();
	}

	/**
	 * @inheritDoc
	 */
	public function first() {
		return $this->collection->first();
	}

	/**
	 * @inheritDoc
	 */
	public function last() {
		return $this->collection->last();
	}

	/**
	 * @inheritDoc
	 */
	public function key() {
		return $this->collection->key();
	}

	/**
	 * @inheritDoc
	 */
	public function current() {
		return $this->collection->current();
	}

	/**
	 * @inheritDoc
	 */
	public function next() {
		return $this->collection->next();
	}

	/**
	 * @inheritDoc
	 */
	public function exists( Closure $p ) {
		return $this->collection->exists( $p );
	}

	/**
	 * @inheritDoc
	 */
	public function filter( Closure $p ) {
		return $this->collection->filter( $p );
	}

	/**
	 * @inheritDoc
	 */
	public function forAll( Closure $p ) {
		return $this->collection->forAll( $p );
	}

	/**
	 * @inheritDoc
	 */
	public function map( Closure $func ) {
		return $this->collection->map( $func );
	}

	/**
	 * @inheritDoc
	 */
	public function partition( Closure $p ) {
		return $this->collection->partition( $p );
	}

	/**
	 * @inheritDoc
	 */
	public function indexOf( $element ) {

		if ( $element->get_pk() ) {

			$keys = $this->getKeys();

			return array_search( $element->get_pk(), $keys, true );
		} else {
			return $this->collection->indexOf( $element );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function slice( $offset, $length = null ) {
		return $this->collection->slice( $offset, $length );
	}

	/**
	 * @inheritDoc
	 */
	public function getIterator() {
		return $this->collection->getIterator();
	}

	/**
	 * @inheritDoc
	 */
	public function offsetExists( $offset ) {
		return $this->collection->offsetExists( $offset );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetGet( $offset ) {
		return $this->collection->offsetGet( $offset );
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
		return $this->collection->count();
	}

	/**
	 * @inheritDoc
	 */
	public function matching( Criteria $criteria ) {
		return $this->collection->matching( $criteria );
	}
}

