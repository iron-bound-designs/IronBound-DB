<?php
/**
 * Contains tests for the ManyToMany relation.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Relations;

use IronBound\DB\Manager;
use IronBound\DB\Model;
use IronBound\DB\Table\Association\ModelAssociationTable;
use IronBound\DB\Table\Association\PostAssociationTable;
use IronBound\DB\Tests\Stub\Models\Actor;
use IronBound\DB\Tests\Stub\Models\Gallery;
use IronBound\DB\Tests\Stub\Models\Movie;
use IronBound\DB\Tests\Stub\Tables\Actors;
use IronBound\DB\Tests\Stub\Tables\Galleries;
use IronBound\DB\Tests\Stub\Tables\Movies;
use IronBound\WPEvents\EventDispatcher;

/**
 * Class Test_ManyToMany
 * @package IronBound\DB\Tests\Relations
 */
class Test_ManyToMany extends \WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		Manager::register( new Actors() );
		Manager::register( new Movies() );
		Manager::register( new ModelAssociationTable( new Actors(), new Movies() ) );
		Manager::maybe_install_table( Manager::get( 'actors' ) );
		Manager::maybe_install_table( Manager::get( 'movies' ) );
		Manager::maybe_install_table( Manager::get( 'actors-movies' ) );

		Manager::register( new Galleries() );
		Manager::register( new PostAssociationTable( new Galleries() ) );
		Manager::maybe_install_table( Manager::get( 'galleries' ) );
		Manager::maybe_install_table( Manager::get( 'galleries-posts' ) );

		Model::set_event_dispatcher( new EventDispatcher() );
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

		$actor->movies->add( $m1 );
		$actor->movies->add( $m2 );
		$actor->save();

		$this->assertEquals( 1, Movie::get( $m2->get_pk() )->actors->count() );

		$actor->movies->remove_model( $m2->get_pk() );
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

		$actor->movies->remove_model( $m2->get_pk() );
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

		$this->assertNotNull( $actors->get_model( $a1->get_pk() ) );
		$this->assertNotNull( $actors->get_model( $a2->get_pk() ) );

		$num_queries = $GLOBALS['wpdb']->num_queries;

		/** @var Actor $a1 */
		$a1        = $actors->get_model( $a1->get_pk() );
		$a1_movies = $a1->movies;
		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );

		$this->assertEquals( 2, $a1_movies->count() );
		$this->assertNotNull( $a1_movies->get_model( $m1->get_pk() ) );
		$this->assertNotNull( $a1_movies->get_model( $m2->get_pk() ) );

		/** @var Actor $a2 */
		$a2        = $actors->get_model( $a2->get_pk() );
		$a2_movies = $a2->movies;
		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );

		$this->assertEquals( 2, $a2_movies->count() );
		$this->assertNotNull( $a2_movies->get_model( $m2->get_pk() ) );
		$this->assertNotNull( $a2_movies->get_model( $m3->get_pk() ) );

		// --- Movies --- //

		$movies = Movie::with( 'actors' )->results();
		$this->assertEquals( 4, $movies->count() );

		$num_queries = $GLOBALS['wpdb']->num_queries;

		/** @var Movie $m1 */
		$m1 = $movies->get_model( $m1->get_pk() );
		$this->assertEquals( 1, $m1->actors->count() );
		$this->assertNotNull( $m1->actors->containsKey( $a1->get_pk() ) );
		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );

		/** @var Movie $m2 */
		$m2 = $movies->get_model( $m2->get_pk() );
		$this->assertEquals( 2, $m2->actors->count() );
		$this->assertNotNull( $m2->actors->get_model( $a1->get_pk() ) );
		$this->assertNotNull( $m2->actors->get_model( $a2->get_pk() ) );
		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );

		/** @var Movie $m3 */
		$m3 = $movies->get_model( $m3->get_pk() );
		$this->assertEquals( 1, $m3->actors->count() );
		$this->assertNotNull( $m3->actors->get_model( $a2->get_pk() ) );
		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );

		/** @var Movie $m4 */
		$m4 = $movies->get_model( $m4->get_pk() );
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

	public function test_many_to_many_posts() {

		$gallery = Gallery::create( array(
			'title' => 'Pablo Picasso'
		) );

		$gallery->art->add(
			new \WP_Post( (object) array(
				'post_type'  => 'attachment',
				'post_title' => 'The Weeping Woman'
			) )
		);

		$gallery->art->add(
			new \WP_Post( (object) array(
				'post_type'  => 'attachment',
				'post_title' => 'Three Musicians'
			) )
		);

		$gallery->art->add( self::factory()->post->create_and_get( array(
			'post_type'  => 'attachment',
			'post_title' => 'The Old Guitarist'
		) ) );

		$gallery->save();

		$gallery = Gallery::get( $gallery->get_pk() );

		$this->assertEquals( 3, $gallery->art->count() );

		$found = 0;

		foreach ( $gallery->art as $post ) {
			if ( in_array( $post->post_title, array( 'The Weeping Woman', 'Three Musicians', 'The Old Guitarist' ) ) ) {
				$found ++;
			}

			$this->assertNotEmpty( $post->ID, 'Post ID not saved.' );
		}

		$this->assertEquals( 3, $found, 'Incorrect titles.' );
	}

	public function test_many_to_many_posts_eager_load() {

		/** @var \wpdb $wpdb */
		global $wpdb;

		$g1 = Gallery::create( array(
			'title' => 'The Best'
		) );
		$g2 = Gallery::create( array(
			'title' => 'The Great'
		) );

		$a1 = self::factory()->post->create_and_get( array(
			'post_type'  => 'attachment',
			'post_title' => 'Piece 1'
		) );
		$a2 = self::factory()->post->create_and_get( array(
			'post_type'  => 'attachment',
			'post_title' => 'Piece 2'
		) );
		$a3 = self::factory()->post->create_and_get( array(
			'post_type'  => 'attachment',
			'post_title' => 'Piece 3'
		) );
		$a4 = self::factory()->post->create_and_get( array(
			'post_type'  => 'attachment',
			'post_title' => 'Piece 4'
		) );

		$g1->art->add( $a1 );
		$g1->art->add( $a2 );
		$g1->save();

		$g2->art->add( $a2 );
		$g2->art->add( $a3 );
		$g2->save();

		$galleries = Gallery::with( 'art' )->results();

		$this->assertNotNull( $galleries->get_model( $g1->get_pk() ) );
		$this->assertNotNull( $galleries->get_model( $g2->get_pk() ) );

		$num_queries = $wpdb->num_queries;

		/** @var Gallery $g1 */
		$g1     = $galleries->get_model( $g1->get_pk() );
		$g1_art = $g1->art;
		$this->assertEquals( $num_queries, $wpdb->num_queries );

		$this->assertEquals( 2, $g1_art->count() );
		$this->assertNotNull( $g1_art->get_model( $a1->ID ) );
		$this->assertNotNull( $g1_art->get_model( $a2->ID ) );

		/** @var Gallery $g2 */
		$g2     = $galleries->get_model( $g2->get_pk() );
		$g2_art = $g2->art;
		$this->assertEquals( $num_queries, $wpdb->num_queries );

		$this->assertEquals( 2, $g2_art->count() );
		$this->assertNotNull( $g2_art->get_model( $a2->ID ) );
		$this->assertNotNull( $g2_art->get_model( $a3->ID ) );
	}

	public function test_many_to_many_posts_caching() {

		$art = self::factory()->post->create_and_get( array(
			'post_type'  => 'attachment',
			'post_title' => 'Piece 1'
		) );

		$gallery = Gallery::create( array(
			'title' => 'The Best'
		) );
		$gallery->art->add( $art );
		$gallery->save();

		add_post_meta( $art->ID, 'test', 'value' );

		$this->flush_cache();

		$gallery = Gallery::get( $gallery->get_pk() );
		$gallery->art;

		$num_queries = $GLOBALS['wpdb']->num_queries;

		$this->assertEquals( 'value', get_post_meta( $art->ID, 'test', true ) );
		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries, 'Postmeta caches not updated.' );

		$this->assertEquals( 'Piece 1', get_post( $art->ID )->post_title );
		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries, 'Post caches not updated.' );

		$this->flush_cache();

		Gallery::with( 'art' )->results();

		$num_queries = $GLOBALS['wpdb']->num_queries;

		$this->assertEquals( 'value', get_post_meta( $art->ID, 'test', true ) );
		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries, 'Postmeta caches not updated during eager-load.' );

		$this->assertEquals( 'Piece 1', get_post( $art->ID )->post_title );
		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries, 'Post caches not updated during eager-load.' );
	}
}