<?php
/**
 * Test meta methods on a model.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Meta;

use IronBound\DB\Manager;
use IronBound\DB\Extensions\Meta\BaseMetaTable;
use IronBound\DB\Tests\Stub\Models\Book;
use IronBound\DB\Tests\Stub\Tables\Authors;
use IronBound\DB\Tests\Stub\Tables\Books;

/**
 * Class Test_Meta
 *
 * @package IronBound\DB\Tests
 */
class Test_Meta extends \IronBound\DB\Tests\TestCase {

	function setUp() {
		parent::setUp();

		Manager::register( new Authors() );
		Manager::register( new Books(), '', get_class( new Book() ) );
		Manager::register( new BaseMetaTable( new Books() ) );

		Manager::maybe_install_table( Manager::get( 'authors' ) );
		Manager::maybe_install_table( Manager::get( 'books' ) );
		Manager::maybe_install_table( Manager::get( 'books-meta' ) );
	}

	public function test_add() {

		$book = Book::create( array(
			'title' => 'My Title'
		) );

		$book->add_meta( 'basic', 'stuff' );
		$this->assertEquals( 'stuff', $book->get_meta( 'basic', true ) );
	}

	public function test_update() {

		$book = Book::create( array(
			'title' => 'My Title'
		) );

		$book->update_meta( 'basic', 'stuff' );
		$this->assertEquals( 'stuff', $book->get_meta( 'basic', true ) );
	}

	public function test_delete() {

		$book = Book::create( array(
			'title' => 'My Title'
		) );

		$book->add_meta( 'basic', 'stuff' );
		$book->delete_meta( 'basic' );

		$this->assertEmpty( $book->get_meta( 'basic' ) );
	}

	public function test_query() {

		$b1 = Book::create( array(
			'title' => 'My Title'
		) );
		$b1->add_meta( 'basic', 'stuff' );

		$b2 = Book::create( array(
			'title' => 'Another Title'
		) );
		$b2->add_meta( 'basic', 'other' );

		$books = Book::query()->where_meta( array(
			array(
				'key'   => 'basic',
				'value' => 'stuff'
			)
		) )->results();

		$this->assertEquals( 1, $books->count() );
		$this->assertTrue( $books->contains( $b1 ) );
	}

	public function test_meta_cache() {

		$book = Book::create( array(
			'title' => 'My Title'
		) );
		$book->add_meta( 'basic', 'stuff' );

		Book::all();

		$num_queries = $GLOBALS['wpdb']->num_queries;

		$this->assertEquals( 'stuff', $book->get_meta( 'basic', true ) );

		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );
	}

	public function test_slashing_add() {

		$meta = "O'neil";

		$book = Book::create( array(
			'title' => 'My Title'
		) );
		$book->add_meta( 'basic', $meta );

		$this->assertEquals( $meta, $book->get_meta( 'basic', true ) );
	}

	public function test_slashing_update() {

		$meta = "O'neil";

		$book = Book::create( array(
			'title' => 'My Title'
		) );
		$book->update_meta( 'basic', $meta );

		$this->assertEquals( $meta, $book->get_meta( 'basic', true ) );
	}

	public function test_slashing_delete() {

		$meta = "O'neil";

		$book = Book::create( array(
			'title' => 'My Title'
		) );
		$book->add_meta( 'basic', $meta );
		$book->add_meta( 'basic', 'other' );
		$this->assertEquals( $meta, $book->get_meta( 'basic', true ) );
		$this->assertTrue( $book->delete_meta( 'basic', $meta ) );

		$this->assertEquals( array( 'other' ), $book->get_meta( 'basic' ) );
	}
}