<?php
/**
 * Test belongs to many.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Relations;

use IronBound\DB\Manager;
use IronBound\DB\Model;
use IronBound\DB\Extensions\Meta\BaseMetaTable;
use IronBound\DB\Tests\Stub\Models\Author;
use IronBound\DB\Tests\Stub\Models\Book;
use IronBound\DB\Tests\Stub\Tables\Authors;
use IronBound\DB\Tests\Stub\Tables\Books;
use IronBound\WPEvents\EventDispatcher;

class Test_Belongs_To_Many extends \IronBound\DB\Tests\TestCase {

	public function setUp() {
		parent::setUp();

		Manager::register( new Authors() );
		Manager::register( new Books(), '', 'IronBound\DB\Tests\Stub\Models\Book' );
		Manager::register( new BaseMetaTable( new Books() ) );

		Manager::maybe_install_table( Manager::get( 'authors' ) );
		Manager::maybe_install_table( Manager::get( 'books' ) );
		Manager::maybe_install_table( Manager::get( 'books-meta' ) );

		Model::set_event_dispatcher( new EventDispatcher() );
	}

	public function test_eager_load() {

		$a1 = Author::create( array(
			'name' => 'John Doe'
		) );
		$a2 = Author::create( array(
			'name' => 'Jane Doe'
		) );

		$b1 = Book::create( array(
			'title'  => "John's Book",
			'author' => $a1
		) );
		$b2 = Book::create( array(
			'title'  => "Jane's Book",
			'author' => $a2
		) );

		$books = Book::with( 'author' )->results();

		$num_queries = $GLOBALS['wpdb']->num_queries;

		/** @var Book $b1 */
		$b1 = $books->get_model( $b1->get_pk() );
		$this->assertEquals( $a1->get_pk(), $b1->author->get_pk() );

		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );
		$this->assertFalse( $b1->is_dirty() );

		/** @var Book $b2 */
		$b2 = $books->get_model( $b2->get_pk() );
		$this->assertEquals( $a2->get_pk(), $b2->author->get_pk() );

		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );
		$this->assertFalse( $b2->is_dirty() );
	}

	public function test_eager_load_posts() {

		$a1 = Author::create( array(
			'name'    => 'Author 1',
			'picture' => new \WP_Post( (object) array(
				'post_type'  => 'attachment',
				'post_title' => 'Picture 1'
			) )
		) );

		$a2 = Author::create( array(
			'name'    => 'Author 2',
			'picture' => new \WP_Post( (object) array(
				'post_type'  => 'attachment',
				'post_title' => 'Picture 2'
			) )
		) );

		$authors = Author::with( 'picture' )->results();

		$num_queries = $GLOBALS['wpdb']->num_queries;

		/** @var Author $a1 */
		$a1 = $authors->get_model( $a1->get_pk() );
		$this->assertEquals( 'Picture 1', $a1->picture->post_title );

		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );

		/** @var Author $a2 */
		$a2 = $authors->get_model( $a2->get_pk() );
		$this->assertEquals( 'Picture 2', $a2->picture->post_title );

		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );
	}
}