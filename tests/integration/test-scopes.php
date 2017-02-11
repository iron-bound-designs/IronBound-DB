<?php
/**
 * Test the query scopes.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests;

use IronBound\DB\Manager;
use IronBound\DB\Query\FluentQuery;
use IronBound\DB\Extensions\Meta\BaseMetaTable;
use IronBound\DB\Tests\Stub\Models\Author;
use IronBound\DB\Tests\Stub\Models\Book;
use IronBound\DB\Tests\Stub\PublishedScope;
use IronBound\DB\Tests\Stub\Tables\Authors;
use IronBound\DB\Tests\Stub\Tables\Books;

/**
 * Class Test_Scopes
 *
 * @package IronBound\DB\Tests
 */
class Test_Scopes extends \IronBound\DB\Tests\TestCase {

	public function setUp() {
		parent::setUp();

		Manager::register( new Authors(), '', 'IronBound\DB\Tests\Stub\Models\Author' );
		Manager::register( new Books(), '', 'IronBound\DB\Tests\Stub\Models\Book' );
		Manager::register( new BaseMetaTable( new Books() ) );

		Manager::maybe_install_table( Manager::get( 'books' ) );
		Manager::maybe_install_table( Manager::get( 'authors' ) );
		Manager::maybe_install_table( Manager::get( 'books-meta' ) );
	}

	public function test_closure_scope() {

		Book::register_global_scope( 'published', function ( FluentQuery $query ) {
			$query->where( 'published', false, null );
		} );

		$b1 = Book::create( array(
			'title' => 'Book 1'
		) );

		$b1->published = null;
		$b1->save();

		$this->assertNull( Book::get( $b1->get_pk() )->published );

		$b2 = Book::create( array(
			'title'     => 'Book 2',
			'published' => new \DateTime()
		) );

		$books = Book::all();

		$this->assertEquals( 1, $books->count() );
		$this->assertNull( $books->get_model( $b1->get_pk() ) );
		$this->assertNotNull( $books->get_model( $b2->get_pk() ) );
	}

	public function test_scope_object() {

		Book::register_global_scope( new PublishedScope() );

		$b1 = Book::create( array(
			'title' => 'Book 1'
		) );

		$b1->published = null;
		$b1->save();

		$this->assertNull( Book::get( $b1->get_pk() )->published );

		$b2 = Book::create( array(
			'title'     => 'Book 2',
			'published' => new \DateTime()
		) );

		$books = Book::all();

		$this->assertEquals( 1, $books->count() );
		$this->assertNull( $books->get_model( $b1->get_pk() ) );
		$this->assertNotNull( $books->get_model( $b2->get_pk() ) );
	}
}
