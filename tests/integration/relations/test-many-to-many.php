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
use IronBound\DB\Table\Association\CommentAssociationTable;
use IronBound\DB\Table\Association\ModelAssociationTable;
use IronBound\DB\Table\Association\PostAssociationTable;
use IronBound\DB\Table\Association\TermAssociationTable;
use IronBound\DB\Table\Association\UserAssociationTable;
use IronBound\DB\Tests\Stub\Models\Actor;
use IronBound\DB\Tests\Stub\Models\Gallery;
use IronBound\DB\Tests\Stub\Models\Movie;
use IronBound\DB\Tests\Stub\Tables\Actors;
use IronBound\DB\Tests\Stub\Tables\Galleries;
use IronBound\DB\Tests\Stub\Tables\Movies;
use IronBound\WPEvents\EventDispatcher;

/**
 * Class Test_ManyToMany
 *
 * @package IronBound\DB\Tests\Relations
 */
class Test_ManyToMany extends \IronBound\DB\Tests\TestCase {

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
		Manager::register( new UserAssociationTable( new Galleries() ) );
		Manager::register( new CommentAssociationTable( new Galleries() ) );
		Manager::register( new TermAssociationTable( new Galleries() ) );
		Manager::maybe_install_table( Manager::get( 'galleries' ) );
		Manager::maybe_install_table( Manager::get( 'galleries-posts' ) );
		Manager::maybe_install_table( Manager::get( 'galleries-users' ) );
		Manager::maybe_install_table( Manager::get( 'galleries-comments' ) );
		Manager::maybe_install_table( Manager::get( 'galleries-terms' ) );
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

	public function test_caching() {

		$a1 = Actor::create( array(
			'name' => 'John Doe',
		) );
		$a2 = Actor::create( array(
			'name' => 'Jane Doe'
		) );

		$m1 = Movie::create( array(
			'title' => 'Movie 1'
		) );
		$m2 = Movie::create( array(
			'title' => 'Movie 2'
		) );

		$a1->movies->add( $m1 );
		$a1->save();

		$a2->movies->add( $m2 );
		$a2->save();

		$num_queries = $GLOBALS['wpdb']->num_queries;

		$a1 = Actor::get( $a1->get_pk() );
		$a2 = Actor::get( $a2->get_pk() );

		$this->assertNotNull( $a1->movies->get_model( $m1->get_pk() ) );
		$this->assertNotNull( $a2->movies->get_model( $m2->get_pk() ) );

		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );
	}

	public function test_cache_updated_when_model_added_to_relation() {

		$actor = Actor::create( array(
			'name' => 'John Doe'
		) );

		$m1 = Movie::create( array(
			'title' => 'Movie 1'
		) );
		$m2 = Movie::create( array(
			'title' => 'Movie 2'
		) );

		$actor->movies->add( $m1 );
		$actor->save();

		$actor = Actor::get( $actor->get_pk() );
		$this->assertNotNull( $actor->movies->get_model( $m1->get_pk() ) );

		$actor->movies->add( $m2 );
		$actor->save();

		$num_queries = $GLOBALS['wpdb']->num_queries;

		$actor = Actor::get( $actor->get_pk() );
		$this->assertNotNull( $actor->movies->get_model( $m1->get_pk() ) );
		$this->assertNotNull( $actor->movies->get_model( $m2->get_pk() ) );
		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );
	}

	public function test_cache_updated_when_model_removed_from_relation() {

		$actor = Actor::create( array(
			'name' => 'John Doe'
		) );

		$m1 = Movie::create( array(
			'title' => 'Movie 1'
		) );
		$m2 = Movie::create( array(
			'title' => 'Movie 2'
		) );

		$actor->movies->add( $m1 );
		$actor->movies->add( $m2 );
		$actor->save();

		$actor = Actor::get( $actor->get_pk() );
		$this->assertNotNull( $actor->movies->get_model( $m1->get_pk() ) );
		$this->assertNotNull( $actor->movies->get_model( $m2->get_pk() ) );

		$actor->movies->remove_model( $m2->get_pk() );
		$actor->save();

		$num_queries = $GLOBALS['wpdb']->num_queries;

		$actor = Actor::get( $actor->get_pk() );
		$this->assertNotNull( $actor->movies->get_model( $m1->get_pk() ) );
		$this->assertNull( $actor->movies->get_model( $m2->get_pk() ) );
		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );
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

		$a1 = self::factory()->post->create_and_get( array(
			'post_type'  => 'attachment',
			'post_title' => 'Piece 1'
		) );
		$a2 = self::factory()->post->create_and_get( array(
			'post_type'  => 'attachment',
			'post_title' => 'Piece 2'
		) );

		$g1 = Gallery::create( array(
			'title' => 'Gallery 1'
		) );
		$g2 = Gallery::create( array(
			'title' => 'Gallery 2'
		) );

		$g1->art->add( $a1 );
		$g1->save();

		$g2->art->add( $a2 );
		$g2->save();

		$num_queries = $GLOBALS['wpdb']->num_queries;

		$g1 = Gallery::get( $g1->get_pk() );
		$g2 = Gallery::get( $g2->get_pk() );

		$this->assertNotNull( $g1->art->get_model( $a1->ID ) );
		$this->assertNotNull( $g2->art->get_model( $a2->ID ) );

		if ( version_compare( $GLOBALS['wp_version'], '4.4', '<=' ) ) {
			// Account update_post_caches(). One for each post
			$this->assertEquals( $num_queries + 2, $GLOBALS['wpdb']->num_queries );
		} else {
			$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );
		}
	}

	public function test_many_to_many_posts_cache_updated_when_model_added_to_relation() {

		$a1 = self::factory()->post->create_and_get( array(
			'post_type'  => 'attachment',
			'post_title' => 'Piece 1'
		) );
		$a2 = self::factory()->post->create_and_get( array(
			'post_type'  => 'attachment',
			'post_title' => 'Piece 2'
		) );

		$gallery = Gallery::create( array(
			'title' => 'Gallery 1'
		) );

		$gallery->art->add( $a1 );
		$gallery->save();

		$gallery = Gallery::get( $gallery->get_pk() );
		$gallery->art->add( $a2 );
		$gallery->save();

		$num_queries = $GLOBALS['wpdb']->num_queries;

		$gallery = Gallery::get( $gallery->get_pk() );
		$this->assertNotNull( $gallery->art->get_model( $a1->ID ) );
		$this->assertNotNull( $gallery->art->get_model( $a2->ID ) );

		if ( version_compare( $GLOBALS['wp_version'], '4.4', '<=' ) ) {
			// Account update_post_caches(). One for each post
			$this->assertEquals( $num_queries + 1, $GLOBALS['wpdb']->num_queries );
		} else {
			$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );
		}
	}

	public function test_many_to_many_posts_cache_updated_when_model_removed_from_relation() {

		$a1 = self::factory()->post->create_and_get( array(
			'post_type'  => 'attachment',
			'post_title' => 'Piece 1'
		) );
		$a2 = self::factory()->post->create_and_get( array(
			'post_type'  => 'attachment',
			'post_title' => 'Piece 2'
		) );

		$gallery = Gallery::create( array(
			'title' => 'Gallery 1'
		) );

		$gallery->art->add( $a1 );
		$gallery->art->add( $a2 );
		$gallery->save();

		$gallery = Gallery::get( $gallery->get_pk() );
		$gallery->art->remove_model( $a2->ID );
		$gallery->save();

		$num_queries = $GLOBALS['wpdb']->num_queries;

		$gallery = Gallery::get( $gallery->get_pk() );
		$this->assertNotNull( $gallery->art->get_model( $a1->ID ) );
		$this->assertNull( $gallery->art->get_model( $a2->ID ) );

		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );
	}

	public function test_many_to_many_posts_wp_post_caching() {

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

	public function test_many_to_many_users() {

		$gallery = Gallery::create( array(
			'title' => 'Pablo Picasso'
		) );

		$gallery->attendees->add(
			new \WP_User( (object) array(
				'ID'         => 0,
				'user_login' => 'User 1'
			) )
		);

		$gallery->attendees->add(
			new \WP_User( (object) array(
				'ID'         => 0,
				'user_login' => 'User 2'
			) )
		);

		$gallery->attendees->add( self::factory()->user->create_and_get( array( 'user_login' => 'User 3' ) ) );

		$gallery->save();

		$gallery = Gallery::get( $gallery->get_pk() );

		$this->assertEquals( 3, $gallery->attendees->count() );

		$found = 0;

		foreach ( $gallery->attendees as $user ) {
			if ( in_array( $user->user_login, array( 'User 1', 'User 2', 'User 3' ) ) ) {
				$found ++;
			}

			$this->assertNotEmpty( $user->ID, 'User ID not saved.' );
		}

		$this->assertEquals( 3, $found, 'Incorrect logins.' );
	}

	public function test_many_to_many_comments() {

		$gallery = Gallery::create( array(
			'title' => 'Pablo Picasso'
		) );

		$gallery->comments->add(
			new \WP_Comment( (object) array(
				'comment_content' => 'Comment 1',
				'comment_post_ID' => self::factory()->post->create()
			) )
		);

		$gallery->comments->add(
			new \WP_Comment( (object) array(
				'comment_content' => 'Comment 2',
				'comment_post_ID' => self::factory()->post->create()
			) )
		);

		$gallery->comments->add( self::factory()->comment->create_and_get( array(
			'comment_content' => 'Comment 3',
			'comment_post_ID' => self::factory()->post->create()
		) ) );

		$gallery->save();

		$gallery = Gallery::get( $gallery->get_pk() );

		$this->assertEquals( 3, $gallery->comments->count() );

		$found = 0;

		foreach ( $gallery->comments as $comment ) {
			if ( in_array( $comment->comment_content, array( 'Comment 1', 'Comment 2', 'Comment 3' ) ) ) {
				$found ++;
			}

			$this->assertNotEmpty( $comment->comment_ID, 'Comment ID not saved.' );
		}

		$this->assertEquals( 3, $found, 'Incorrect comments.' );
	}

	public function test_many_to_many_terms() {

		$gallery = Gallery::create( array(
			'title' => 'Pablo Picasso'
		) );

		$gallery->terms->add(
			new \WP_Term( (object) array(
				'name'        => 'Term 1',
				'description' => 'Description 1',
				'taxonomy'    => \WP_UnitTest_Factory_For_Term::DEFAULT_TAXONOMY
			) )
		);

		$gallery->terms->add(
			new \WP_Term( (object) array(
				'name'        => 'Term 2',
				'description' => 'Description 2',
				'taxonomy'    => \WP_UnitTest_Factory_For_Term::DEFAULT_TAXONOMY
			) )
		);

		$gallery->terms->add( self::factory()->term->create_and_get( array(
			'name'        => 'Term 3',
			'description' => 'Description 3'
		) ) );

		$gallery->save();

		$gallery = Gallery::get( $gallery->get_pk() );

		$this->assertEquals( 3, $gallery->terms->count() );

		$found = 0;

		foreach ( $gallery->terms as $term ) {
			if ( in_array( $term->name, array( 'Term 1', 'Term 2', 'Term 3' ) ) ) {
				$found ++;
			}

			if ( in_array( $term->description, array( 'Description 1', 'Description 2', 'Description 3' ) ) ) {
				$found ++;
			}

			$this->assertNotEmpty( $term->term_id, 'Term ID not saved.' );
		}

		$this->assertEquals( 6, $found, 'Incorrect logins.' );
	}
}