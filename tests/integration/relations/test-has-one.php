<?php
/**
 * Test the HasOne relation.
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
use IronBound\DB\Tests\Stub\Models\AuthorSession;
use IronBound\DB\Tests\Stub\Models\Book;
use IronBound\DB\Tests\Stub\Tables\Authors;
use IronBound\DB\Tests\Stub\Tables\AuthorSessions;
use IronBound\DB\Tests\Stub\Tables\Books;
use IronBound\WPEvents\EventDispatcher;

/**
 * Class TestHasOne
 * @package IronBound\DB\Tests\Relations
 */
class TestHasOne extends \IronBound\DB\Tests\TestCase {

	function setUp() {
		parent::setUp();

		Manager::register( new Authors(), '', 'IronBound\DB\Tests\Stub\Models\Author' );
		Manager::register( new Books(), '', 'IronBound\DB\Tests\Stub\Models\Book' );
		Manager::register( new BaseMetaTable( new Books() ) );
		Manager::register( new AuthorSessions() );

		Manager::maybe_install_table( Manager::get( 'authors' ) );
		Manager::maybe_install_table( Manager::get( 'books' ) );
		Manager::maybe_install_table( Manager::get( 'books-meta' ) );
		Manager::maybe_install_table( Manager::get( 'author-sessions' ) );

		Model::set_event_dispatcher( new EventDispatcher() );
	}

	public function test() {

		$author = Author::create( array(
			'name' => 'John'
		) );

		AuthorSession::create( array(
			'author' => $author
		) );

		$author->session->set_value( 'sample', 'data' );

		$author  = Author::get( $author->get_pk() );
		$session = $author->session;

		$this->assertArrayHasKey( 'sample', $session->data );
		$this->assertEquals( 'data', $session->data['sample'] );
	}

	public function test_foreign_attribute_automatically_assigned() {

		$author = Author::create( array(
			'name' => 'John'
		) );

		$author->set_relation_value( 'session', AuthorSession::create( array(
			'data' => array( 'test' => 1 )
		) ) );
		$author->save();

		$author = Author::get( $author->get_pk() );
		$this->assertNotNull( $author->session );
		$this->assertEquals( array( 'test' => 1 ), $author->session->data );
	}

	public function test_eager_load() {

		$a1 = Author::create( array(
			'name' => 'John'
		) );
		AuthorSession::create( array(
			'author' => $a1,
			'data'   => array( 'test' => 1 )
		) );

		$a2 = Author::create( array(
			'name' => 'Jane'
		) );
		AuthorSession::create( array(
			'author' => $a2,
			'data'   => array( 'test' => 2 )
		) );

		$authors = Author::with( 'session' )->results();

		$num_queries = $GLOBALS['wpdb']->num_queries;

		/** @var Author $a1 */
		$a1 = $authors->get_model( $a1->get_pk() );
		$this->assertEquals( 1, $a1->session->get_value( 'test' ) );

		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );

		/** @var Author $a2 */
		$a2 = $authors->get_model( $a2->get_pk() );
		$this->assertEquals( 2, $a2->session->get_value( 'test' ) );

		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );
	}

	public function test_caching() {

		$a1 = Author::create( array(
			'name' => 'John'
		) );
		$s1 = AuthorSession::create( array(
			'author' => $a1,
			'data'   => array( 'test' => 1 )
		) );

		$a2 = Author::create( array(
			'name' => 'Jane'
		) );
		$s2 = AuthorSession::create( array(
			'author' => $a2,
			'data'   => array( 'test' => 2 )
		) );

		// load the relation
		$a1->session;
		$a2->session;

		$num_queries = $GLOBALS['wpdb']->num_queries;

		$a1 = Author::get( $a1->get_pk() );
		$a2 = Author::get( $a2->get_pk() );

		$this->assertEquals( $a1->session->get_pk(), $s1->get_pk() );
		$this->assertEquals( $a2->session->get_pk(), $s2->get_pk() );

		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );
	}

	public function test_cache_updated_when_model_updated() {

		$author = Author::create( array(
			'name' => 'John'
		) );

		$s1 = AuthorSession::create( array(
			'author' => $author,
			'data'   => array( 'test' => 1 )
		) );

		$this->assertEquals( $s1->get_pk(), $author->session->get_pk() );

		$s2 = AuthorSession::create( array(
			'author' => $author,
			'data'   => array( 'test' => 2 )
		) );

		$num_queries = $GLOBALS['wpdb']->num_queries;

		$author = Author::get( $author->get_pk() );
		$this->assertEquals( $s2->get_pk(), $author->session->get_pk() );

		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );
	}

}