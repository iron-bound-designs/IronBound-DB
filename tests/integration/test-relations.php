<?php
/**
 * Test the model realtions.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests;

use IronBound\DB\Manager;
use IronBound\DB\Model;
use IronBound\DB\Table\AssociationTable;
use IronBound\DB\Tests\Stub\Models\Actor;
use IronBound\DB\Tests\Stub\Models\Author;
use IronBound\DB\Tests\Stub\Models\Book;
use IronBound\DB\Tests\Stub\Models\Movie;
use IronBound\DB\Tests\Stub\Tables\Actors;
use IronBound\DB\Tests\Stub\Tables\Authors;
use IronBound\DB\Tests\Stub\Tables\Books;
use IronBound\DB\Tests\Stub\Tables\Movies;
use IronBound\WPEvents\EventDispatcher;

/**
 * Class Test_Relations
 * @package IronBound\DB\Tests
 */
class Test_Relations extends \WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		Manager::register( new Authors() );
		Manager::register( new Books() );
		Manager::maybe_install_table( Manager::get( 'authors' ) );
		Manager::maybe_install_table( Manager::get( 'books' ) );

		Manager::register( new Actors() );
		Manager::register( new Movies() );
		Manager::register( new AssociationTable( new Actors(), new Movies() ) );
		Manager::maybe_install_table( Manager::get( 'actors' ) );
		Manager::maybe_install_table( Manager::get( 'movies' ) );
		Manager::maybe_install_table( Manager::get( 'actors-movies' ) );

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

	public function test_many_to_many_adding() {

		$actor = Actor::create( array(
			'name'       => 'James Smith',
			'birth_date' => new \DateTime( '1959-02-24' )
		) );

		$m1 = Movie::create( array(
			'title'        => 'The Best Movie',
			'release_date' => new \DateTime( '2013-06-24' )
		) );
		$m2 = Movie::create( array(
			'title'        => 'The Great Movie',
			'release_date' => new \DateTime( '2011-10-31' )
		) );

		$actor->movies->add( $m1 );
		$actor->movies->add( $m2 );
		$actor->save();

		$this->assertEquals( 1, $m1->actors->count() );
		$this->assertEquals( 1, $m2->actors->count() );
	}

	public function test_many_to_many_removing() {

		$actor = Actor::create( array(
			'name'       => 'James Smith',
			'birth_date' => new \DateTime( '1959-02-24' )
		) );

		$m1 = Movie::create( array(
			'title'        => 'The Best Movie',
			'release_date' => new \DateTime( '2013-06-24' )
		) );
		$m2 = Movie::create( array(
			'title'        => 'The Great Movie',
			'release_date' => new \DateTime( '2011-10-31' )
		) );

		$actor->movies->set( $m1->get_pk(), $m1 );
		$actor->movies->set( $m2->get_pk(), $m2 );
		$actor->save();

		$this->assertEquals( 1, Movie::get( $m2->get_pk() )->actors->count() );

		$actor->movies->remove( $m2->get_pk() );
		$this->assertEquals( 1, $actor->movies->count() );
		$actor->save();

		$this->assertEquals( 0, Movie::get( $m2->get_pk() )->actors->count() );
	}

	public function test_many_to_many_keep_synced() {

		$actor = Actor::create( array(
			'name'       => 'James Smith',
			'birth_date' => new \DateTime( '1959-02-24' )
		) );

		$m1 = Movie::create( array(
			'title'        => 'The Best Movie',
			'release_date' => new \DateTime( '2013-06-24' )
		) );
		$m2 = Movie::create( array(
			'title'        => 'The Great Movie',
			'release_date' => new \DateTime( '2011-10-31' )
		) );

		$this->assertEquals( 0, $m1->actors->count() );
		$this->assertEquals( 0, $m2->actors->count() );

		$actor->movies->add( $m1 );
		$actor->save();

		$this->assertEquals( 1, $m1->actors->count() );
		$this->assertEquals( 0, $m2->actors->count() );

		$actor->movies->add( $m2 );
		$actor->save();

		$this->assertEquals( 1, $m1->actors->count() );
		$this->assertEquals( 1, $m2->actors->count() );

		$actor->movies->remove( $m2->get_pk() );
		$actor->save();

		$this->assertEquals( 1, $m1->actors->count() );
		$this->assertEquals( 0, $m2->actors->count() );
	}

	public function test_many_to_many_eager_load() {

		$a1 = Actor::create( array(
			'name'       => 'James Smith',
			'birth_date' => new \DateTime( '1959-02-24' )
		) );
		$a2 = Actor::create( array(
			'name'       => 'Amy Goodman',
			'birth_date' => new \DateTime( '1963-09-24' )
		) );

		$m1 = Movie::create( array(
			'title'        => 'The Best Movie',
			'release_date' => new \DateTime( '2013-06-24' )
		) );
		$m2 = Movie::create( array(
			'title'        => 'The Great Movie',
			'release_date' => new \DateTime( '2011-10-31' )
		) );
		$m3 = Movie::create( array(
			'title'        => 'A Fantastic Movie',
			'release_date' => new \DateTime( '2015-10-31' )
		) );
		$m4 = Movie::create( array(
			'title'        => 'Without Actors',
			'release_date' => new \DateTime( '2016-01-10' )
		) );

		$a1->movies->add( $m1 );
		$a1->movies->add( $m2 );
		$a1->save();

		$a2->movies->add( $m2 );
		$a2->movies->add( $m3 );
		$a2->save();

		$actors = Actor::with( 'movies' )->results();

		$this->assertTrue( $actors->containsKey( $a1->get_pk() ) );
		$this->assertTrue( $actors->containsKey( $a2->get_pk() ) );

		$num_queries = $GLOBALS['wpdb']->num_queries;

		/** @var Actor $a1 */
		$a1        = $actors->get( $a1->get_pk() );
		$a1_movies = $a1->movies;
		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );

		$this->assertEquals( 2, $a1_movies->count() );
		$this->assertTrue( $a1_movies->containsKey( $m1->get_pk() ) );
		$this->assertTrue( $a1_movies->containsKey( $m2->get_pk() ) );

		/** @var Actor $a2 */
		$a2        = $actors->get( $a2->get_pk() );
		$a2_movies = $a2->movies;
		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );

		$this->assertEquals( 2, $a2_movies->count() );
		$this->assertTrue( $a2_movies->containsKey( $m2->get_pk() ) );
		$this->assertTrue( $a2_movies->containsKey( $m3->get_pk() ) );

		// --- Movies --- //

		$movies = Movie::with( 'actors' )->results();
		$this->assertEquals( 4, $movies->count() );

		$num_queries = $GLOBALS['wpdb']->num_queries;

		/** @var Movie $m1 */
		$m1 = $movies->get( $m1->get_pk() );
		$this->assertEquals( 1, $m1->actors->count() );
		$this->assertTrue( $m1->actors->containsKey( $a1->get_pk() ) );
		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );

		/** @var Movie $m2 */
		$m2 = $movies->get( $m2->get_pk() );
		$this->assertEquals( 2, $m2->actors->count() );
		$this->assertTrue( $m2->actors->containsKey( $a1->get_pk() ) );
		$this->assertTrue( $m2->actors->containsKey( $a2->get_pk() ) );
		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );

		/** @var Movie $m3 */
		$m3 = $movies->get( $m3->get_pk() );
		$this->assertEquals( 1, $m3->actors->count() );
		$this->assertTrue( $m3->actors->containsKey( $a2->get_pk() ) );
		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );

		/** @var Movie $m4 */
		$m4 = $movies->get( $m4->get_pk() );
		$this->assertEquals( 0, $m4->actors->count() );
		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );
	}

	public function test_many_to_many_relation_deleted_when_model_deleted() {

		$movie = Movie::create( array(
			'title' => 'The Great Movie'
		) );

		$a1 = Actor::create( array(
			'name' => 'John Doe'
		) );
		$a2 = Actor::create( array(
			'name' => 'Amy Smith'
		) );

		$movie->actors->add( $a1 );
		$movie->actors->add( $a2 );
		$movie->save();

		$a1->delete();

		$movie  = Movie::get( $movie->get_pk() );
		$actors = $movie->actors;

		$this->assertEquals( 1, $actors->count() );
		$this->assertEquals( $a2->get_pk(), $actors->containsKey( $a2->get_pk() ) );
	}
}