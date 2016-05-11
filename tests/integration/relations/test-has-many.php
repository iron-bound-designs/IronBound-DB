<?php
/**
 * Test the HasMany relation.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Relations;

use IronBound\DB\Manager;
use IronBound\DB\Model;
use IronBound\DB\Table\Meta\BaseMetaTable;
use IronBound\DB\Tests\Stub\Models\Author;
use IronBound\DB\Tests\Stub\Models\Book;
use IronBound\DB\Tests\Stub\Tables\Authors;
use IronBound\DB\Tests\Stub\Tables\Books;
use IronBound\WPEvents\EventDispatcher;

/**
 * Class Test_HasMany
 * @package IronBound\DB\Tests
 */
class Test_HasMany extends \WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		Manager::register( new Authors() );
		Manager::register( new Books(), '', get_class( new Book() ) );
		Manager::register( new BaseMetaTable( new Books() ) );

		Manager::maybe_install_table( Manager::get( 'authors' ) );
		Manager::maybe_install_table( Manager::get( 'books' ) );
		Manager::maybe_install_table( Manager::get( 'books-meta' ) );

		Model::set_event_dispatcher( new EventDispatcher() );
	}

	public function test_has_many() {

		$author = Author::create( array( 'name' => 'John Smith' ) );

		$b1 = Book::create( array(
			'title'  => 'The Tales of John Smith',
			'author' => $author,
			'price'  => 19.95
		) );

		$books = $author->books;

		$this->assertNotNull( $books->get_model( $b1->get_pk() ) );

		$b2 = Book::create( array(
			'title'  => 'The Songs of John Smith',
			'author' => $author,
			'price'  => 14.95
		) );
		$this->assertNotNull( $books->get_model( $b2->get_pk() ) );
	}

	public function test_persist_new_models() {

		$author = Author::create( array(
			'name' => 'John Smith'
		) );

		$author->books->add( new Book( array( 'title' => 'Book 1' ) ) );
		$author->books->add( new Book( array( 'title' => 'Book 2' ) ) );
		$author->books->add( new Book( array( 'title' => 'Book 3' ) ) );
		$author->books->add( new Book( array( 'title' => 'Book 4' ) ) );

		$author->save();

		$this->assertEquals( 4, $author->books->count() );

		$b1 = null;
		$b2 = null;
		$b3 = null;
		$b4 = null;

		$this->assertTrue( $author->books->exists( function ( $key, Book $model ) use ( &$b1 ) {
			if ( $model->title === 'Book 1' ) {
				$b1 = $model->get_pk();

				return true;
			}

			return false;
		} ) );
		$this->assertTrue( $author->books->exists( function ( $key, Book $model ) use ( &$b2 ) {
			if ( $model->title === 'Book 2' ) {
				$b2 = $model->get_pk();

				return true;
			}

			return false;
		} ) );
		$this->assertTrue( $author->books->exists( function ( $key, Book $model ) use ( &$b3 ) {
			if ( $model->title === 'Book 3' ) {
				$b3 = $model->get_pk();

				return true;
			}

			return false;
		} ) );
		$this->assertTrue( $author->books->exists( function ( $key, Book $model ) use ( &$b4 ) {
			if ( $model->title === 'Book 4' ) {
				$b4 = $model->get_pk();

				return true;
			}

			return false;
		} ) );
	}

	public function test_loaded_relations_are_saved() {

		$author = Author::create( array( 'name' => 'John Smith' ) );

		$b1 = Book::create( array(
			'title'  => 'The Tales of John Smith',
			'author' => $author,
			'price'  => 19.95
		) );
		$b2 = Book::create( array(
			'title'  => 'The Songs of John Smith',
			'author' => $author,
			'price'  => 14.95
		) );

		foreach ( $author->books as $book ) {
			$book->price += 5.0;
		}

		$author->save();

		$this->assertEquals( 24.95, Book::get( $b1->get_pk() )->price );
		$this->assertEquals( 19.95, Book::get( $b2->get_pk() )->price );
	}

	public function test_eager_loading() {

		$author = Author::create( array( 'name' => 'John Smith' ) );

		$b1 = Book::create( array(
			'title'  => 'The Tales of John Smith',
			'author' => $author,
			'price'  => 5.00
		) );
		$b2 = Book::create( array(
			'title'  => 'The Songs of John Smith',
			'author' => $author,
			'price'  => 10.00
		) );
		$b3 = Book::create( array(
			'title'  => 'The Stories of John Smith',
			'author' => $author,
			'price'  => 10.00
		) );
		$b4 = Book::create( array(
			'title'  => 'The Epics of John Smith',
			'author' => $author,
			'price'  => 25.00
		) );

		$authors = Author::query()->with( 'books' )->results();

		$num_queries = $GLOBALS['wpdb']->num_queries;

		$this->assertEquals( 1, $authors->count() );
		$this->assertTrue( $authors->containsKey( $author->get_pk() ) );

		$books = $authors->get( $author->get_pk() )->books;
		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );

		$this->assertEquals( 4, $books->count() );
		$this->assertTrue( $books->containsKey( $b1->get_pk() ) );
		$this->assertTrue( $books->containsKey( $b2->get_pk() ) );
		$this->assertTrue( $books->containsKey( $b3->get_pk() ) );
		$this->assertTrue( $books->containsKey( $b4->get_pk() ) );
	}

	public function test_keep_synced() {

		$b1 = Book::create( array( 'title' => 'R+L=J' ) );

		$author = Author::create( array(
			'name' => 'Jon Snow'
		) );
		$author->books->add( $b1 );
		$author->save();

		$b2 = Book::create( array(
			'title'  => 'The Tower of Joy',
			'author' => $author
		) );

		$books = $author->books;

		$this->assertEquals( 2, $books->count() );
		$this->assertTrue( $books->contains( $b1 ) );
		$this->assertTrue( $books->contains( $b2 ) );
	}

	public function test_keep_synced_new_models() {

		$author = Author::create( array(
			'name' => 'Jon Snow'
		) );
		$author->books->add( new Book( array( 'title' => 'R+L=J' ) ) );
		$author->save();

		$b2 = Book::create( array(
			'title'  => 'The Tower of Joy',
			'author' => $author
		) );

		$books = $author->books;

		$this->assertEquals( 2, $books->count() );
		$this->assertTrue( $books->exists( function ( $key, Book $model ) {
			return $model->title === 'R+L=J';
		} ) );
		$this->assertTrue( $books->contains( $b2 ) );
	}
}