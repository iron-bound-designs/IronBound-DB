<?php
/**
 * Test delete constraints.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Foreign_Key;

use IronBound\DB\Manager;
use IronBound\DB\Model;
use IronBound\DB\Table\ForeignKey\DeleteConstrained;
use IronBound\DB\Tests\Stub\Models\Author;
use IronBound\DB\Tests\Stub\Models\Book;
use IronBound\DB\Tests\Stub\Tables\Authors;
use IronBound\DB\Tests\Stub\Tables\Books;
use IronBound\WPEvents\EventDispatcher;

/**
 * Class Test_Delete_Constraints
 * @package IronBound\DB\Tests\Foreign_Key
 */
class Test_Delete_Constraints extends \WP_UnitTestCase {

	function setUp() {
		parent::setUp();

		Model::set_event_dispatcher( new EventDispatcher() );

		Manager::register( new Authors(), '', get_class( new Author() ) );
		Manager::maybe_install_table( Manager::get( 'authors' ) );
	}

	public function test_cascade() {

		Manager::register( new Books( DeleteConstrained::CASCADE ), '', get_class( new Book() ) );
		Manager::maybe_install_table( Manager::get( 'books' ) );

		$author = Author::create( array(
			'name' => 'John Smith'
		) );

		$book = Book::create( array(
			'title'  => 'My Book',
			'author' => $author
		) );

		$author->delete();

		$this->assertNull( Book::get( $book->get_pk() ) );
	}

	/**
	 * @expectedException \IronBound\DB\Exception\DeleteRestrictedException
	 */
	public function test_restrict() {

		Manager::register( new Books( DeleteConstrained::RESTRICT ), '', get_class( new Book() ) );
		Manager::maybe_install_table( Manager::get( 'books' ) );

		$author = Author::create( array(
			'name' => 'John Smith'
		) );

		Book::create( array(
			'title'  => 'My Book',
			'author' => $author
		) );

		$author->delete();
	}

	public function test_set_default() {

		Manager::register( new Books( DeleteConstrained::SET_DEFAULT ), '', get_class( new Book() ) );
		Manager::maybe_install_table( Manager::get( 'books' ) );

		$author = Author::create( array(
			'name' => 'John Smith'
		) );

		$book = Book::create( array(
			'title'  => 'My Book',
			'author' => $author
		) );

		$author->delete();

		$this->assertEmpty( Book::get( $book->get_pk() )->author );
	}
}