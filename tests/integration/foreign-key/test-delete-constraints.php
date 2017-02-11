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
use IronBound\DB\Extensions\Meta\BaseMetaTable;
use IronBound\DB\Tests\Stub\Models\Author;
use IronBound\DB\Tests\Stub\Models\Book;
use IronBound\DB\Tests\Stub\Models\ModelWithAllForeign;
use IronBound\DB\Tests\Stub\Models\ModelWithForeignPost;
use IronBound\DB\Tests\Stub\Tables\Authors;
use IronBound\DB\Tests\Stub\Tables\AuthorSessions;
use IronBound\DB\Tests\Stub\Tables\Books;
use IronBound\DB\Tests\Stub\Tables\Reviews;
use IronBound\DB\Tests\Stub\Tables\TableWithAllForeign;
use IronBound\DB\Tests\Stub\Tables\TableWithForeignPost;
use IronBound\WPEvents\EventDispatcher;

/**
 * Class Test_Delete_Constraints
 *
 * @package IronBound\DB\Tests\Foreign_Key
 */
class Test_Delete_Constraints extends \IronBound\DB\Tests\TestCase {

	function setUp() {
		parent::setUp();

		Model::set_event_dispatcher( new EventDispatcher() );

		Manager::register( new Authors(), '', 'IronBound\DB\Tests\Stub\Models\Author' );
		Manager::register( new AuthorSessions(), '', 'IronBound\DB\Tests\Stub\Models\AuthorSession' );
		Manager::register( new BaseMetaTable( new Books() ) );
		Manager::register( new Reviews(), '', 'IronBound\DB\Tests\Stub\Models\Review' );

		Manager::maybe_install_table( Manager::get( 'authors' ) );
		Manager::maybe_install_table( Manager::get( 'author-sessions' ) );
		Manager::maybe_install_table( Manager::get( 'books-meta' ) );

		if ( Manager::get( 'books' ) ) {
			Manager::maybe_install_table( Manager::get( 'reviews' ) );
		}
	}

	public function test_cascade_model() {

		Manager::register( new Books( DeleteConstrained::CASCADE ), '', 'IronBound\DB\Tests\Stub\Models\Book' );
		Manager::maybe_install_table( Manager::get( 'books' ) );
		Manager::maybe_install_table( Manager::get( 'reviews' ) );

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

		Manager::register( new Books( DeleteConstrained::RESTRICT ), '', 'IronBound\DB\Tests\Stub\Models\Book' );
		Manager::maybe_install_table( Manager::get( 'books' ) );
		Manager::maybe_install_table( Manager::get( 'reviews' ) );

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

		Manager::register( new Books( DeleteConstrained::SET_DEFAULT ), '', 'IronBound\DB\Tests\Stub\Models\Book' );
		Manager::maybe_install_table( Manager::get( 'books' ) );
		Manager::maybe_install_table( Manager::get( 'reviews' ) );

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

		Manager::register( new TableWithForeignPost(), '', 'IronBound\DB\Tests\Stub\Models\ModelWithForeignPost' );
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

		Manager::register( new TableWithForeignPost( DeleteConstrained::RESTRICT ), '', 'IronBound\DB\Tests\Stub\Models\ModelWithForeignPost' );
		Manager::maybe_install_table( Manager::get( 'with-foreign-post' ) );

		$post = self::factory()->post->create_and_get( array(
			'post_title' => 'The Title'
		) );

		ModelWithForeignPost::create( array(
			'post'  => $post,
			'price' => '9.99'
		) );

		wp_delete_post( $post->ID, true );
	}

	public function test_set_default_post() {

		Manager::register( new TableWithForeignPost( DeleteConstrained::SET_DEFAULT ), '', 'IronBound\DB\Tests\Stub\Models\ModelWithForeignPost' );
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

	public function test_cascade_comment() {

		Manager::register( new TableWithAllForeign(), '', 'IronBound\DB\Tests\Stub\Models\ModelWithAllForeign' );
		Manager::maybe_install_table( Manager::get( 'with-all-foreign' ) );

		$c1 = self::factory()->comment->create_and_get( array(
			'comment_content' => 'My Comment'
		) );
		$c2 = self::factory()->comment->create_and_get( array(
			'comment_content' => 'My Other Comment'
		) );

		$m1 = ModelWithAllForeign::create( array(
			'comment' => $c1,
		) );
		$m2 = ModelWithAllForeign::create( array(
			'comment' => $c2,
		) );

		wp_delete_comment( $c1->comment_ID, true );

		$this->assertNull( ModelWithAllForeign::get( $m1->get_pk() ) );
		$this->assertNotNull( ModelWithAllForeign::get( $m2->get_pk() ) );
	}

	/**
	 * @expectedException \IronBound\DB\Exception\DeleteRestrictedException
	 */
	public function test_restrict_comment() {

		Manager::register( new TableWithAllForeign( DeleteConstrained::RESTRICT ), '', 'IronBound\DB\Tests\Stub\Models\ModelWithAllForeign' );
		Manager::maybe_install_table( Manager::get( 'with-all-foreign' ) );

		$comment = self::factory()->comment->create_and_get( array(
			'comment_content' => 'My Comment'
		) );

		ModelWithAllForeign::create( array(
			'comment' => $comment,
		) );

		wp_delete_comment( $comment->comment_ID, true );
	}

	public function test_set_default_comment() {

		Manager::register( new TableWithAllForeign( DeleteConstrained::SET_DEFAULT ), '', 'IronBound\DB\Tests\Stub\Models\ModelWithAllForeign' );
		Manager::maybe_install_table( Manager::get( 'with-all-foreign' ) );

		$c1 = self::factory()->comment->create_and_get( array(
			'comment_content' => 'My Comment'
		) );
		$c2 = self::factory()->comment->create_and_get( array(
			'comment_content' => 'My Other Comment'
		) );

		$m1 = ModelWithAllForeign::create( array(
			'comment' => $c1,
		) );
		$m2 = ModelWithAllForeign::create( array(
			'comment' => $c2,
		) );

		wp_delete_comment( $c1->comment_ID, true );

		$this->assertEmpty( ModelWithAllForeign::get( $m1->get_pk() )->comment );
		$this->assertEquals( $c2->comment_ID, ModelWithAllForeign::get( $m2->get_pk() )->comment->comment_ID );
	}

	public function test_cascade_user() {

		Manager::register( new TableWithAllForeign(), '', 'IronBound\DB\Tests\Stub\Models\ModelWithAllForeign' );
		Manager::maybe_install_table( Manager::get( 'with-all-foreign' ) );

		$u1 = self::factory()->user->create_and_get();
		$u2 = self::factory()->user->create_and_get();

		$m1 = ModelWithAllForeign::create( array(
			'user' => $u1,
		) );
		$m2 = ModelWithAllForeign::create( array(
			'user' => $u2,
		) );

		wp_delete_user( $u1->ID );

		$this->assertNull( ModelWithAllForeign::get( $m1->get_pk() ) );
		$this->assertNotNull( ModelWithAllForeign::get( $m2->get_pk() ) );
	}

	/**
	 * @expectedException \IronBound\DB\Exception\DeleteRestrictedException
	 */
	public function test_restrict_user() {

		Manager::register( new TableWithAllForeign( DeleteConstrained::RESTRICT ), '', 'IronBound\DB\Tests\Stub\Models\ModelWithAllForeign' );
		Manager::maybe_install_table( Manager::get( 'with-all-foreign' ) );

		$user = self::factory()->user->create_and_get();

		ModelWithAllForeign::create( array(
			'user' => $user,
		) );

		wp_delete_user( $user->ID );
	}

	public function test_set_default_user() {

		Manager::register( new TableWithAllForeign( DeleteConstrained::SET_DEFAULT ), '', 'IronBound\DB\Tests\Stub\Models\ModelWithAllForeign' );
		Manager::maybe_install_table( Manager::get( 'with-all-foreign' ) );

		$u1 = self::factory()->user->create_and_get();
		$u2 = self::factory()->user->create_and_get();

		$m1 = ModelWithAllForeign::create( array(
			'user' => $u1,
		) );
		$m2 = ModelWithAllForeign::create( array(
			'user' => $u2,
		) );

		wp_delete_user( $u1->ID );

		$this->assertEmpty( ModelWithAllForeign::get( $m1->get_pk() )->user );
		$this->assertEquals( $u2->ID, ModelWithAllForeign::get( $m2->get_pk() )->user->ID );
	}

	public function test_cascade_term() {

		Manager::register( new TableWithAllForeign(), '', 'IronBound\DB\Tests\Stub\Models\ModelWithAllForeign' );
		Manager::maybe_install_table( Manager::get( 'with-all-foreign' ) );

		$t1 = self::factory()->term->create_and_get();
		$t2 = self::factory()->term->create_and_get();

		$m1 = ModelWithAllForeign::create( array(
			'term' => $t1,
		) );
		$m2 = ModelWithAllForeign::create( array(
			'term' => $t2,
		) );

		wp_delete_term( $t1->term_id, $t1->taxonomy );

		$this->assertNull( ModelWithAllForeign::get( $m1->get_pk() ) );
		$this->assertNotNull( ModelWithAllForeign::get( $m2->get_pk() ) );
	}

	/**
	 * @expectedException \IronBound\DB\Exception\DeleteRestrictedException
	 */
	public function test_restrict_term() {

		Manager::register( new TableWithAllForeign( DeleteConstrained::RESTRICT ), '', 'IronBound\DB\Tests\Stub\Models\ModelWithAllForeign' );
		Manager::maybe_install_table( Manager::get( 'with-all-foreign' ) );

		$term = self::factory()->term->create_and_get();

		ModelWithAllForeign::create( array(
			'term' => $term,
		) );

		wp_delete_term( $term->term_id, $term->taxonomy );
	}

	public function test_set_default_term() {

		Manager::register( new TableWithAllForeign( DeleteConstrained::SET_DEFAULT ), '', 'IronBound\DB\Tests\Stub\Models\ModelWithAllForeign' );
		Manager::maybe_install_table( Manager::get( 'with-all-foreign' ) );

		$t1 = self::factory()->term->create_and_get();
		$t2 = self::factory()->term->create_and_get();

		$m1 = ModelWithAllForeign::create( array(
			'term' => $t1,
		) );
		$m2 = ModelWithAllForeign::create( array(
			'term' => $t2,
		) );

		wp_delete_term( $t1->term_id, $t1->taxonomy );

		$this->assertEmpty( ModelWithAllForeign::get( $m1->get_pk() )->term );
		$this->assertEquals( $t2->term_id, ModelWithAllForeign::get( $m2->get_pk() )->term->term_id );
	}
}