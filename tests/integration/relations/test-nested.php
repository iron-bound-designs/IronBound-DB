<?php
/**
 * Test nested relations.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Relations;

use IronBound\DB\Manager;
use IronBound\DB\Table\Association\ModelAssociationTable;
use IronBound\DB\Extensions\Meta\BaseMetaTable;
use IronBound\DB\Tests\Stub\Models\Author;
use IronBound\DB\Tests\Stub\Models\Book;
use IronBound\DB\Tests\Stub\Models\Library;
use IronBound\DB\Tests\Stub\Models\Review;
use IronBound\DB\Tests\Stub\Tables\Authors;
use IronBound\DB\Tests\Stub\Tables\Books;
use IronBound\DB\Tests\Stub\Tables\Libraries;
use IronBound\DB\Tests\Stub\Tables\Reviews;

/**
 * Class Test_Nested
 * @package IronBound\DB\Tests\Relations
 */
class Test_Nested extends \IronBound\DB\Tests\TestCase {

	function setUp() {
		parent::setUp();

		Manager::register( new Authors(), '', 'IronBound\DB\Tests\Stub\Models\Author' );
		Manager::register( new Reviews(), '', 'IronBound\DB\Tests\Stub\Models\Review' );
		Manager::register( new Books(), '', 'IronBound\DB\Tests\Stub\Models\Book' );
		Manager::register( new BaseMetaTable( new Books() ) );
		Manager::register( new Libraries(), '', 'IronBound\DB\Tests\Stub\Models\Library' );
		Manager::register( new ModelAssociationTable( new Books(), new Libraries() ) );

		Manager::maybe_install_table( Manager::get( 'authors' ) );
		Manager::maybe_install_table( Manager::get( 'reviews' ) );
		Manager::maybe_install_table( Manager::get( 'books' ) );
		Manager::maybe_install_table( Manager::get( 'books-meta' ) );
		Manager::maybe_install_table( Manager::get( 'libraries' ) );
		Manager::maybe_install_table( Manager::get( 'books-libraries' ) );
	}

	public function test() {

		/** @var \wpdb $wpdb */
		global $wpdb;

		$a1 = Author::create( array(
			'name' => 'Amy Smith'
		) );
		$a2 = Author::create( array(
			'name' => 'John Smith'
		) );

		$b1 = Book::create( array(
			'title'  => "Amy's Book",
			'author' => $a1
		) );
		$b2 = Book::create( array(
			'title'  => "Amy's Other Book",
			'author' => $a1
		) );
		$b3 = Book::create( array(
			'title'  => "John's Book",
			'author' => $a2
		) );

		$r1 = Review::create( array(
			'stars'   => 4,
			'content' => 'This is awesome!',
			'book'    => $b1
		) );
		$r2 = Review::create( array(
			'stars'   => 1,
			'content' => 'This Sucked',
			'book'    => $b1
		) );
		$r3 = Review::create( array(
			'content' => 'This was ok',
			'book'    => $b2
		) );

		$l1 = Library::create( array(
			'name' => 'A Great Library'
		) );
		$l1->books->add( $b1 );
		$l1->books->add( $b3 );
		$l1->save();

		$l2 = Library::create( array(
			'name' => 'An Ok Library'
		) );
		$l2->books->add( $b2 );
		$l2->books->add( $b3 );
		$l2->save();

		$libraries = Library::with( 'books.reviews' )->results();

		$num_queries = $wpdb->num_queries;

		/** @var Library $library */
		foreach ( $libraries as $library ) {
			/** @var Book $book */
			foreach ( $library->books as $book ) {
				/** @var Review $review */
				foreach ( $book->reviews as $review ) {

				}
			}
		}

		$this->assertEquals( $num_queries, $wpdb->num_queries );

		// --- Libraries --- //

		$this->assertEquals( 2, $libraries->count() );
		$this->assertTrue( $libraries->contains( $l1 ) );
		$this->assertTrue( $libraries->contains( $l2 ) );

		/** @var Library $l1 */
		$l1 = $libraries->get_model( $l1->get_pk() );

		$this->assertEquals( 2, $l1->books->count() );
		$this->assertTrue( $l1->books->contains( $b1 ) );
		$this->assertTrue( $l1->books->contains( $b3 ) );

		/** @var Library $l2 */
		$l2 = $libraries->get_model( $l2->get_pk() );

		$this->assertEquals( 2, $l2->books->count() );
		$this->assertTrue( $l2->books->contains( $b2 ) );
		$this->assertTrue( $l2->books->contains( $b3 ) );

		// --- Books --- //

		/** @var Book $b1 */
		$b1 = $l1->books->get_model( $b1->get_pk() );
		$this->assertEquals( 2, $b1->reviews->count() );
		$this->assertTrue( $b1->reviews->contains( $r1 ) );
		$this->assertTrue( $b1->reviews->contains( $r2 ) );

		/** @var Book $b2 */
		$b2 = $l2->books->get_model( $b2->get_pk() );
		$this->assertEquals( 1, $b2->reviews->count() );
		$this->assertTrue( $b2->reviews->contains( $r3 ) );

		/** @var Book $b3 */
		$b3 = $l2->books->get_model( $b3->get_pk() );
		$this->assertEquals( 0, $b3->reviews->count() );
	}
}