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
		Manager::maybe_install_table( Manager::get( 'authors' ) );
		Manager::maybe_install_table( Manager::get( 'books' ) );

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

		$this->assertTrue( $books->containsKey( $b1->get_pk() ) );

		$b2 = Book::create( array(
			'title'  => 'The Songs of John Smith',
			'author' => $author,
			'price'  => 14.95
		) );
		$this->assertTrue( $books->containsKey( $b2->get_pk() ) );
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
}