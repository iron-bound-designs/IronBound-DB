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
use IronBound\DB\Tests\Stub\Models\ModelWithForeignPost;
use IronBound\DB\Tests\Stub\Tables\Authors;
use IronBound\DB\Tests\Stub\Tables\Books;
use IronBound\DB\Tests\Stub\Tables\TableWithForeignPost;
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

	public function test_cascade_model() {

		Manager::register( new Books( DeleteConstrained::CASCADE ), '', get_class( new Book() ) );
		Manager::maybe_install_table( Manager::get( 'books' ) );

		$author = Author::create( array(
			'name' => 'John Smith'
		) );

		$b1 = Book::create( array(
			'title'  => 'My Book',
			'author' => $author
		) );
		$b2 = Book::create( array(
			'title' => 'Anonymous Novel'
		) );

		$author->delete();

		$this->assertNull( Book::get( $b1->get_pk() ) );
		$this->assertNotNull( Book::get( $b2->get_pk() ) );
	}

	/**
	 * @expectedException \IronBound\DB\Exception\DeleteRestrictedException
	 */
	public function test_restrict_model() {

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

	public function test_set_default_model() {

		Manager::register( new Books( DeleteConstrained::SET_DEFAULT ), '', get_class( new Book() ) );
		Manager::maybe_install_table( Manager::get( 'books' ) );

		$a1 = Author::create( array(
			'name' => 'John Smith'
		) );
		$a2 = Author::create( array(
			'name' => 'Amy Smith'
		) );

		$b1 = Book::create( array(
			'title'  => 'My Book',
			'author' => $a1
		) );
		$b2 = Book::create( array(
			'title'  => 'Another Book',
			'author' => $a2
		) );

		$a1->delete();

		$this->assertEmpty( Book::get( $b1->get_pk() )->author );
		$this->assertEquals( $a2->get_pk(), Book::get( $b2->get_pk() )->author->get_pk() );
	}

	public function test_cascade_post() {

		Manager::register( new TableWithForeignPost(), '', get_class( new ModelWithForeignPost() ) );
		Manager::maybe_install_table( Manager::get( 'with-foreign-post' ) );

		$p1 = self::factory()->post->create_and_get( array(
			'post_title' => 'My Post'
		) );
		$p2 = self::factory()->post->create_and_get( array(
			'post_title' => 'Other Post'
		) );

		$m1 = ModelWithForeignPost::create( array(
			'post'  => $p1,
			'price' => '20.00'
		) );
		$m2 = ModelWithForeignPost::create( array(
			'post'  => $p2,
			'price' => '15.00'
		) );
		$m3 = ModelWithForeignPost::create( array(
			'price' => '10.00'
		) );

		wp_delete_post( $p1->ID, true );

		$this->assertNull( ModelWithForeignPost::get( $m1->get_pk() ) );
		$this->assertNotNull( ModelWithForeignPost::get( $m2->get_pk() ) );
		$this->assertNotNull( ModelWithForeignPost::get( $m3->get_pk() ) );
	}

	/**
	 * @expectedException \IronBound\DB\Exception\DeleteRestrictedException
	 */
	public function test_restrict_post() {

		Manager::register( new TableWithForeignPost( DeleteConstrained::RESTRICT ), '', get_class( new ModelWithForeignPost() ) );
		Manager::maybe_install_table( Manager::get( 'with-foreign-post' ) );

		$post = self::factory()->post->create_and_get( array(
			'post_title' => 'The Title'
		) );

		$model = ModelWithForeignPost::create( array(
			'post'  => $post,
			'price' => '9.99'
		) );

		wp_delete_post( $post->ID, true );
	}

	public function test_set_default_post() {

		Manager::register( new TableWithForeignPost( DeleteConstrained::SET_DEFAULT ), '', get_class( new ModelWithForeignPost() ) );
		Manager::maybe_install_table( Manager::get( 'with-foreign-post' ) );

		$p1 = self::factory()->post->create_and_get( array(
			'post_title' => 'The Title'
		) );
		$p2 = self::factory()->post->create_and_get( array(
			'post_title' => 'My Title'
		) );

		$m1 = ModelWithForeignPost::create( array(
			'post'  => $p1,
			'price' => '20.00'
		) );
		$m2 = ModelWithForeignPost::create( array(
			'post'  => $p2,
			'price' => '15.00'
		) );

		wp_delete_post( $p1->ID, true );

		$this->assertEmpty( ModelWithForeignPost::get( $m1->get_pk() )->post );
		$this->assertEquals( $p2->ID, ModelWithForeignPost::get( $m2->get_pk() )->post->ID );
	}
}