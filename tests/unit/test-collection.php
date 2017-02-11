<?php
/**
 * Test the collection class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests;

use IronBound\DB\Collection;


/**
 * Class Test_Collection
 *
 * @package IronBound\DB\Tests
 */
class Test_Collection extends \IronBound\DB\Tests\TestCase {

	/**
	 * @var Collection
	 */
	public $collection;

	function setUp() {

		parent::setUp();

		$saver = $this->getMockBuilder( '\IronBound\DB\Saver\Saver' )
		              ->setMethods( array( 'get_pk', 'save' ) )
		              ->getMockForAbstractClass();
		$saver->method( 'get_pk' )->will( $this->returnCallback( function ( $model ) {
			return $model->pk;
		} ) );

		$saver->method( 'save' )->will( $this->returnCallback( function ( $model ) {
			$model->saved = true;

			return true;
		} ) );

		$this->collection = new Collection( array(), true, $saver );
	}

	public function test_add() {

		$this->assertTrue( $this->collection->add( (object) array( 'pk' => 1, 'name' => 'John' ) ) );
		$model = $this->collection->get_model( 1 );

		$this->assertEquals( 'John', $model->name );
	}

	public function test_add_many() {

		$m1 = (object) array( 'pk' => 1, 'name' => 'John' );
		$m2 = (object) array( 'pk' => 2, 'name' => 'Jane' );

		$many = array( $m1, $m2 );

		$this->collection->add_many( $many );

		$this->assertEquals( 2, $this->collection->count() );
		$this->assertEquals( 'John', $this->collection->get_model( 1 )->name );
		$this->assertEquals( 'Jane', $this->collection->get_model( 2 )->name );
	}

	public function test_set() {

		$this->collection->set( 5, (object) array( 'pk' => 1, 'name' => 'John' ) );
		$this->assertEquals( 'John', $this->collection->get( 5 )->name );
		$this->assertEquals( 'John', $this->collection->get_model( 1 )->name );
	}

	public function test_contains() {

		$model = (object) array( 'pk' => 1, 'name' => 'John' );

		$this->assertFalse( $this->collection->contains( $model ) );
		$this->collection->add( $model );
		$this->assertTrue( $this->collection->contains( $model ) );
	}

	public function test_contains_unsaved() {

		$model = (object) array( 'pk' => 0, 'name' => 'John' );

		$this->assertFalse( $this->collection->contains( $model ) );
		$this->collection->add( $model );
		$this->assertTrue( $this->collection->contains( $model ) );
	}

	public function test_is_empty() {

		$this->assertTrue( $this->collection->isEmpty() );
		$this->collection->add( (object) array( 'pk' => 1, 'name' => 'John' ) );
		$this->assertFalse( $this->collection->isEmpty() );
	}

	public function test_remove() {

		$model = (object) array( 'pk' => 1, 'name' => 'John' );
		$this->collection->set( 5, $model );

		$this->collection->remove( 5 );

		$this->assertFalse( $this->collection->containsKey( 5 ) );
		$this->assertNull( $this->collection->get_model( 1 ) );
	}

	public function test_remove_unsaved() {

		$model = (object) array( 'pk' => 0, 'name' => 'John' );
		$this->collection->set( 5, $model );

		$this->collection->remove( 5 );

		$this->assertFalse( $this->collection->containsKey( 5 ) );
	}

	public function test_remove_element() {

		$model = (object) array( 'pk' => 1, 'name' => 'John' );

		$this->collection->set( 5, $model );
		$this->collection->removeElement( $model );

		$this->assertFalse( $this->collection->contains( $model ) );
		$this->assertNull( $this->collection->get_model( 1 ) );
	}

	public function test_remove_element_unsaved() {

		$model = (object) array( 'pk' => 0, 'name' => 'John' );
		$this->collection->set( 5, $model );
		$this->collection->removeElement( $model );

		$this->assertFalse( $this->collection->contains( $model ) );
	}

	public function test_remove_element_unsaved_on_insert_than_saved() {

		$model = (object) array( 'pk' => 0, 'name' => 'John' );

		$this->collection->set( 5, $model );
		$model->pk = 1;

		$this->collection->removeElement( $model );

		$this->assertFalse( $this->collection->contains( $model ) );
		$this->assertNull( $this->collection->get_model( 1 ) );
		$this->assertNull( $this->collection->get( 5 ) );
	}

	public function test_contains_key() {

		$this->assertFalse( $this->collection->containsKey( 5 ) );

		$this->collection->set( 5, (object) array( 'pk' => 1, 'name' => 'John' ) );

		$this->assertTrue( $this->collection->containsKey( 5 ) );

		$this->collection->remove( 5 );

		$this->assertFalse( $this->collection->containsKey( 5 ) );
	}

	public function test_get() {

		$model = (object) array( 'pk' => 1, 'name' => 'John' );

		$this->assertNull( $this->collection->get( 5 ) );
		$this->collection->set( 5, $model );

		$this->assertEquals( $model, $this->collection->get( 5 ) );

		$this->collection->remove( 5 );

		$this->assertNull( $this->collection->get( 5 ) );
	}

	public function test_get_keys() {

		$this->assertEmpty( $this->collection->getKeys() );

		$this->collection->set( 5, (object) array( 'pk' => 1, 'name' => 'John' ) );
		$this->collection->set( 8, (object) array( 'pk' => 2, 'name' => 'Jane' ) );

		$this->assertEquals( array( 5, 8 ), $this->collection->getKeys() );
	}

	public function test_get_values() {

		$this->assertEmpty( $this->collection->getValues() );

		$m1 = (object) array( 'pk' => 1, 'name' => 'John' );
		$m2 = (object) array( 'pk' => 2, 'name' => 'Jane' );

		$this->collection->add( $m1 );
		$this->collection->add( $m2 );

		$this->assertEquals( array( $m1, $m2 ), $this->collection->getValues() );
	}

	public function test_to_array() {

		$m1 = (object) array( 'pk' => 1, 'name' => 'John' );
		$m2 = (object) array( 'pk' => 2, 'name' => 'Jane' );

		$this->collection->set( 5, $m1 );
		$this->collection->set( 8, $m2 );

		$this->assertEquals( array( 5 => $m1, 8 => $m2 ), $this->collection->toArray() );
	}

	public function test_first() {

		$m1 = (object) array( 'pk' => 1, 'name' => 'John' );
		$m2 = (object) array( 'pk' => 2, 'name' => 'Jane' );

		$this->collection->add( $m1 );
		$this->collection->add( $m2 );

		$this->assertEquals( $m1, $this->collection->first() );
	}

	public function test_last() {

		$m1 = (object) array( 'pk' => 1, 'name' => 'John' );
		$m2 = (object) array( 'pk' => 2, 'name' => 'Jane' );

		$this->collection->add( $m1 );
		$this->collection->add( $m2 );

		$this->assertEquals( $m2, $this->collection->last() );
	}

	public function test_key() {

		$m1 = (object) array( 'pk' => 1, 'name' => 'John' );
		$m2 = (object) array( 'pk' => 2, 'name' => 'Jane' );

		$this->collection->set( 5, $m1 );
		$this->collection->set( 8, $m2 );

		$this->assertEquals( 5, $this->collection->key() );
	}

	public function test_current() {

		$m1 = (object) array( 'pk' => 1, 'name' => 'John' );
		$m2 = (object) array( 'pk' => 2, 'name' => 'Jane' );

		$this->collection->set( 5, $m1 );
		$this->collection->set( 8, $m2 );

		$this->assertEquals( $m1, $this->collection->current() );
	}

	public function test_next() {

		$m1 = (object) array( 'pk' => 1, 'name' => 'John' );
		$m2 = (object) array( 'pk' => 2, 'name' => 'Jane' );

		$this->collection->set( 5, $m1 );
		$this->collection->set( 8, $m2 );

		$this->assertEquals( 5, $this->collection->key() );
		$this->collection->next();
		$this->assertEquals( 8, $this->collection->key() );
		$this->collection->next();
		$this->assertNull( $this->collection->key() );
	}

	public function test_exist() {

		$model = (object) array( 'pk' => 1, 'name' => 'John' );

		$this->collection->add( $model );

		$this->assertTrue( $this->collection->exists( function ( $i, $model ) {
			return $model->name === 'John';
		} ) );

		$this->assertFalse( $this->collection->exists( function ( $i, $model ) {
			return $model->name === 'Jane';
		} ) );
	}

	public function test_filter() {

		$m1 = (object) array( 'pk' => 1, 'name' => 'John' );
		$m2 = (object) array( 'pk' => 2, 'name' => 'Sara' );
		$m3 = (object) array( 'pk' => 3, 'name' => 'Jane' );

		$this->collection->add( $m1 );
		$this->collection->add( $m2 );
		$this->collection->add( $m3 );

		$filtered = $this->collection->filter( function ( $model ) {
			return $model->name[0] === 'J';
		} );

		$this->assertInstanceOf( '\IronBound\DB\Collection', $filtered );
		$this->assertEquals( array( $m1, $m3 ), $filtered->getValues() );

		$this->assertTrue( $filtered->keep_memory() );
		$this->assertEquals( $this->collection->get_saver(), $filtered->get_saver() );
	}

	public function test_for_all() {

		$m1 = (object) array( 'pk' => 1, 'name' => 'John' );
		$m2 = (object) array( 'pk' => 2, 'name' => 'Sara' );
		$m3 = (object) array( 'pk' => 3, 'name' => 'Jane' );

		$this->collection->add( $m1 );
		$this->collection->add( $m2 );
		$this->collection->add( $m3 );

		$this->assertFalse( $this->collection->forAll( function ( $i, $model ) {
			return $model->name[0] === 'J';
		} ) );

		$this->collection->remove_model( 2 );

		$this->assertTrue( $this->collection->forAll( function ( $i, $model ) {
			return $model->name[0] === 'J';
		} ) );
	}

	public function test_map() {

		$m1 = (object) array( 'pk' => 1, 'name' => 'John' );
		$m2 = (object) array( 'pk' => 2, 'name' => 'Sara' );

		$this->collection->add( $m1 );
		$this->collection->add( $m2 );

		$mapped = $this->collection->map( function ( $model ) {
			$model->name = strtoupper( $model->name );

			return $model;
		} );

		$this->assertInstanceOf( '\IronBound\DB\Collection', $mapped );
		$this->assertTrue( $mapped->keep_memory() );
		$this->assertEquals( $this->collection->get_saver(), $mapped->get_saver() );

		$this->assertEquals( 'JOHN', $mapped->get_model( 1 )->name );
		$this->assertEquals( 'SARA', $mapped->get_model( 2 )->name );
	}

	public function test_partition() {


		$m1 = (object) array( 'pk' => 1, 'name' => 'John' );
		$m2 = (object) array( 'pk' => 2, 'name' => 'Sara' );
		$m3 = (object) array( 'pk' => 3, 'name' => 'Jane' );

		$this->collection->add( $m1 );
		$this->collection->add( $m2 );
		$this->collection->add( $m3 );

		$partitioned = $this->collection->partition( function ( $i, $model ) {
			return $model->name[0] === 'J';
		} );

		$this->assertContainsOnlyInstancesOf( '\IronBound\DB\Collection', $partitioned );

		$this->assertEquals( array( $m1, $m3 ), $partitioned[0]->getValues() );
		$this->assertEquals( array( $m2 ), $partitioned[1]->getValues() );


		$this->assertTrue( $partitioned[0]->keep_memory() );
		$this->assertEquals( $this->collection->get_saver(), $partitioned[0]->get_saver() );
		$this->assertInstanceOf( '\IronBound\DB\Collection', $partitioned[0] );

		$this->assertTrue( $partitioned[1]->keep_memory() );
		$this->assertEquals( $this->collection->get_saver(), $partitioned[1]->get_saver() );
		$this->assertInstanceOf( '\IronBound\DB\Collection', $partitioned[1] );
	}

	public function test_index_of() {

		$model = (object) array( 'pk' => 1, 'name' => 'John' );

		$this->collection->add( $model );

		$this->assertEquals( 0, $this->collection->indexOf( $model ) );
	}

	public function test_index_of_unsaved() {

		$model = (object) array( 'pk' => 0, 'name' => 'John' );

		$this->collection->add( $model );

		$this->assertEquals( 0, $this->collection->indexOf( $model ) );
	}

	public function test_count() {

		$m1 = (object) array( 'pk' => 1, 'name' => 'John' );
		$m2 = (object) array( 'pk' => 2, 'name' => 'Sara' );

		$this->assertEquals( 0, $this->collection->count() );
		$this->collection->add( $m1 );
		$this->assertEquals( 1, $this->collection->count() );
		$this->collection->add( $m2 );
		$this->assertEquals( 2, $this->collection->count() );
	}

	public function test_clear() {

		$model = (object) array( 'pk' => 1, 'name' => 'John' );

		$this->collection->add( $model );
		$this->collection->clear();

		$this->assertEquals( 0, $this->collection->count() );
	}

	public function test_added() {

		$this->assertEquals( 0, $this->collection->get_added()->count() );

		$m1 = (object) array( 'pk' => 1, 'name' => 'John' );
		$m2 = (object) array( 'pk' => 2, 'name' => 'Sara' );

		$this->collection->add( $m1 );
		$this->assertEquals( array( $m1 ), $this->collection->get_added()->getValues() );

		$this->collection->add( $m2 );
		$this->assertEquals( array( $m1, $m2 ), $this->collection->get_added()->getValues() );
	}

	public function test_removed() {

		$this->assertEquals( 0, $this->collection->get_removed()->count() );

		$m1 = (object) array( 'pk' => 1, 'name' => 'John' );
		$m2 = (object) array( 'pk' => 2, 'name' => 'Sara' );

		$this->collection->add( $m1 );
		$this->collection->add( $m2 );

		$this->assertEquals( 0, $this->collection->get_removed()->count() );

		$this->collection->remove_model( 2 );
		$this->assertEquals( array( $m2 ), $this->collection->get_removed()->getValues() );

		$this->collection->remove_model( 1 );
		$this->assertEquals( array( $m2, $m1 ), $this->collection->get_removed()->getValues() );
	}

	public function test_clear_memory() {

		$m1 = (object) array( 'pk' => 1, 'name' => 'John' );
		$m2 = (object) array( 'pk' => 2, 'name' => 'Sara' );

		$this->collection->add( $m1 );
		$this->collection->add( $m2 );

		$this->collection->remove_model( 2 );

		$this->collection->clear_memory();

		$this->assertEquals( 0, $this->collection->get_added()->count() );
		$this->assertEquals( 0, $this->collection->get_removed()->count() );
	}

	public function test_dont_remember() {

		$self = $this;

		$m1 = (object) array( 'pk' => 1, 'name' => 'John' );
		$m2 = (object) array( 'pk' => 2, 'name' => 'Sara' );

		$this->collection->add( $m1 );

		$this->collection->dont_remember( function () use ( $self, $m2 ) {
			$self->collection->add( $m2 );
		} );

		$this->assertEquals( $m2, $this->collection->get_model( 2 ) );
		$this->assertEquals( array( $m1 ), $this->collection->get_added()->getValues() );

		$this->collection->remove_model( 1 );
		$this->collection->dont_remember( function () use ( $self ) {
			$self->collection->remove_model( 2 );
		} );

		$this->assertNull( $this->collection->get_model( 2 ) );
		$this->assertEquals( array( $m1 ), $this->collection->get_removed()->getValues() );
	}

	public function test_keep_memory() {
		$this->assertTrue( $this->collection->keep_memory( false ) );
		$this->assertFalse( $this->collection->keep_memory( true ) );
	}

	public function test_save() {

		$m1 = (object) array( 'pk' => 1, 'name' => 'John' );
		$m2 = (object) array( 'pk' => 2, 'name' => 'Sara' );

		$this->collection->add( $m1 );
		$this->collection->add( $m2 );

		$this->collection->save();

		$this->assertTrue( ! empty( $m1->saved ) );
		$this->assertTrue( ! empty( $m2->saved ) );
	}
}
