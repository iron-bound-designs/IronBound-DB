<?php
/**
 * Test the with foreign post model.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests;

use IronBound\DB\Manager;
use IronBound\DB\Extensions\Meta\BaseMetaTable;
use IronBound\DB\Tests\Stub\Models\Author;
use IronBound\DB\Tests\Stub\Models\Book;
use IronBound\DB\Tests\Stub\Models\ModelWithAllForeign;
use IronBound\DB\Tests\Stub\Models\ModelWithForeignPost;
use IronBound\DB\Tests\Stub\Tables\Authors;
use IronBound\DB\Tests\Stub\Tables\Books;
use IronBound\DB\Tests\Stub\Tables\TableWithAllForeign;
use IronBound\DB\Tests\Stub\Tables\TableWithForeignPost;

/**
 * Class Test_Crud
 *
 * @package IronBound\DB\Tests
 */
class Test_Crud extends \IronBound\DB\Tests\TestCase {

	public function setUp() {
		parent::setUp();

		Manager::register( new TableWithForeignPost(), '', 'IronBound\DB\Tests\Stub\Models\ModelWithForeignPost' );
		Manager::register( new Authors() );
		Manager::register( new Books(), '', 'IronBound\DB\Tests\Stub\Models\Book' );
		Manager::register( new TableWithAllForeign(), '', 'IronBound\DB\Tests\Stub\Models\ModelWithAllForeign' );
		Manager::register( new BaseMetaTable( new Books() ) );

		Manager::maybe_install_table( Manager::get( 'with-foreign-post' ) );
		Manager::maybe_install_table( Manager::get( 'books' ) );
		Manager::maybe_install_table( Manager::get( 'authors' ) );
		Manager::maybe_install_table( Manager::get( 'with-all-foreign' ) );
		Manager::maybe_install_table( Manager::get( 'books-meta' ) );
	}

	public function test_create() {

		$post = self::factory()->post->create_and_get();

		$model = new ModelWithForeignPost( array(
			'post'  => $post,
			'price' => 24.75
		) );
		$model->save();

		$this->assertTrue( $model->exists() );

		$this->assertNotEmpty( $model->get_pk() );
		$this->assertEquals( time(), $model->published->getTimestamp(), '', 5 );

		$published = $model->published;

		$model = ModelWithForeignPost::get( $model->get_pk() );
		$this->assertEquals( 24.75, $model->price );
		$this->assertEquals( $post, $model->post );
		$this->assertEquals( $published, $model->published );
	}

	public function test_create_with_static_method() {

		$model = ModelWithForeignPost::create( array(
			'price' => 24.75
		) );

		$this->assertEquals( 24.75, $model->price );
	}

	public function test_update() {

		$model = new ModelWithForeignPost( array(
			'post' => self::factory()->post->create_and_get()
		) );
		$model->save();

		$post = self::factory()->post->create_and_get();

		$model->post = $post;
		$this->assertEquals( $post, $model->post );
		$model->save();

		$model = ModelWithForeignPost::get( $model->get_pk() );
		$this->assertEquals( $post, $model->post );
	}

	public function test_unset_attribute() {

		$model = new ModelWithForeignPost( array(
			'post' => self::factory()->post->create_and_get()
		) );
		$model->save();

		unset( $model->post );
		$this->assertNull( $model->post );
		$model->save();

		$model = ModelWithForeignPost::get( $model->get_pk() );
		$this->assertNull( $model->post );
	}

	public function test_delete() {

		$model = new ModelWithForeignPost();
		$model->save();

		$this->assertNotEmpty( $model->get_pk() );
		$model->delete();
		$this->assertNull( ModelWithForeignPost::get( $model->get_pk() ) );
	}

	public function test_caching() {

		$model = new ModelWithForeignPost( array(
			'price' => 29.99
		) );
		$model->save();

		$current = $GLOBALS['wpdb']->num_queries;
		ModelWithForeignPost::get( $model->get_pk() );
		$this->assertEquals( $current, $GLOBALS['wpdb']->num_queries );
	}

	public function test_serialize() {

		$model = new ModelWithForeignPost( array(
			'price' => 29.99
		) );
		$model->save();

		$this->assertEquals( $model->price, unserialize( serialize( $model ) )->price );
	}

	public function test_foreign_post_automatically_saved() {

		$model = new ModelWithAllForeign();

		$model->post = new \WP_Post( (object) array(
			'post_title'   => 'My Post',
			'post_content' => 'My Post Content'
		) );
		$model->save();

		$this->assertNotEmpty( $model->post->ID );
		$this->assertEquals( 'My Post', $model->post->post_title );
		$this->assertEquals( 'My Post Content', $model->post->post_content );
	}

	public function test_foreign_post_automatically_updated() {

		$model = ModelWithAllForeign::create( array(
			'post' => self::factory()->post->create_and_get( array(
				'post_title'   => 'My Post',
				'post_content' => 'My Post Content'
			) )
		) );

		$model->post->post_title   = 'My Updated Post';
		$model->post->post_content = 'My Updated Post Content';

		$model->save();

		$this->assertEquals( 'My Updated Post', $model->post->post_title );
		$this->assertEquals( 'My Updated Post Content', $model->post->post_content );
	}

	public function test_foreign_user_automatically_saved() {

		$model = new ModelWithAllForeign();

		$model->user = new \WP_User( (object) array(
			'user_login' => 'Test',
			'user_email' => 'example.test@example.org',
			'first_name' => 'John',
			'last_name'  => 'Doe',
			'ID'         => '',
			'user_pass'  => wp_generate_password()
		) );
		$model->save();

		$this->assertNotEmpty( $model->user->ID );
		$this->assertEquals( 'Test', $model->user->user_login );
		$this->assertEquals( 'example.test@example.org', $model->user->user_email );
		$this->assertEquals( 'John', $model->user->first_name );
		$this->assertEquals( 'Doe', $model->user->last_name );
	}

	public function test_foreign_user_automatically_updated() {

		$model = ModelWithAllForeign::create( array(
			'user' => self::factory()->user->create_and_get( array(
				'user_login' => 'Test',
				'user_email' => 'example.test@example.org',
				'first_name' => 'John',
				'last_name'  => 'Doe',
			) )
		) );

		$model->user->user_email = 'example.test-user@example.org';
		$model->user->first_name = 'James';
		$model->user->last_name  = 'Smith';

		$model->save();

		$this->assertEquals( 'example.test-user@example.org', $model->user->user_email );
		$this->assertEquals( 'James', $model->user->first_name );
		$this->assertEquals( 'Smith', $model->user->last_name );
	}

	public function test_foreign_comment_automatically_saved() {

		$model = new ModelWithAllForeign();

		/** @noinspection PhpParamsInspection */
		$model->comment = new \WP_Comment( (object) array(
			'comment_post_ID' => self::factory()->post->create(),
			'user_id'         => self::factory()->user->create(),
			'comment_content' => 'This is my comment!'
		) );
		$model->save();

		$this->assertNotEmpty( $model->comment->comment_ID );
		$this->assertEquals( 'This is my comment!', $model->comment->comment_content );
	}

	public function test_foreign_comment_automatically_updated() {

		/** @noinspection PhpParamsInspection */
		$model = ModelWithAllForeign::create( array(
			'comment' => new \WP_Comment( (object) array(
				'comment_post_ID' => self::factory()->post->create(),
				'user_id'         => self::factory()->user->create(),
				'comment_content' => 'This is my comment!'
			) )
		) );

		$model->comment->comment_content = 'My New Comment';

		$model->save();

		$this->assertEquals( 'My New Comment', $model->comment->comment_content );
	}

	public function test_foreign_term_automatically_saved() {

		$model = new ModelWithAllForeign();

		$model->term = new \WP_Term( (object) array(
			'name'     => 'My Term',
			'taxonomy' => 'category'
		) );
		$model->save();

		$this->assertNotEmpty( $model->term->term_id );
		$this->assertEquals( 'My Term', $model->term->name );
		$this->assertEquals( 'category', $model->term->taxonomy );
	}

	public function test_foreign_term_automatically_updated() {

		$model = ModelWithAllForeign::create( array(
			'term' => self::factory()->term->create_and_get( array(
				'taxonomy' => 'category',
				'name'     => 'My Term'
			) )
		) );

		$model->term->name        = 'My New Term';
		$model->term->description = 'The Description';

		$model->save();

		$this->assertEquals( 'My New Term', $model->term->name );
		$this->assertEquals( 'The Description', $model->term->description );
	}

	public function test_foreign_model_automatically_saved() {

		$model = new ModelWithAllForeign();

		$model->model = new Book( array(
			'title' => 'My Book'
		) );
		$model->save();

		$this->assertNotEmpty( $model->model->get_pk() );
		$this->assertEquals( 'My Book', $model->model->title );
	}

	public function test_foreign_model_automatically_updated() {

		$model = ModelWithAllForeign::create( array(
			'model' => Book::create( array(
				'title' => 'My Book'
			) )
		) );

		$model->model->title = 'My New Book';
		$model->save();

		$this->assertEquals( 'My New Book', $model->model->title );
	}

	public function test_foreign_model_automatically_updated_after_assignment() {

		$model = ModelWithAllForeign::create( array(
			'model' => Book::create( array(
				'title' => 'Title 1'
			) )
		) );

		$book        = $model->model;
		$book->title = 'Title 2';

		$model->model = $book;
		$model->save();

		$this->assertEquals( 'Title 2', $model->model->title );
		$this->assertEquals( 'Title 2', ModelWithAllForeign::get( $model->get_pk() )->model->title );
	}

	public function test_created_at_and_updated_at_columns_set() {

		$author = Author::create( array(
			'name' => 'John'
		) );
		$this->assertEquals( time(), $author->created_at->getTimestamp(), '', 1 );
		$this->assertEquals( time(), $author->updated_at->getTimestamp(), '', 1 );
	}

	public function test_updated_at_column_updated() {

		$author = Author::create( array(
			'name' => 'John'
		) );

		sleep( 2 );

		$author->bio = 'My Bio';
		$author->save();

		$this->assertEquals( time(), $author->updated_at->getTimestamp(), '', 1 );
	}

	public function test_create_many() {

		$authors = Author::create_many( array(
			array( 'name' => 'Joe', 'bio' => 'Hi' ),
			array( 'bio' => null, 'name' => 'John' ),
			array( 'name' => 'James' ),
		) );

		$this->assertCount( 3, $authors );
		$this->assertContainsOnlyInstancesOf( '\IronBound\DB\Tests\Stub\Models\Author', $authors );

		list( $a1, $a2, $a3 ) = $authors;

		$this->assertNotNull( $a1->id );
		$this->assertNotNull( $a2->id );
		$this->assertNotNull( $a3->id );

		$this->assertEquals( 'Joe', $a1->name );
		$this->assertEquals( 'John', $a2->name );
		$this->assertEquals( 'James', $a3->name );

		$this->assertEquals( 'Hi', $a1->bio );
		$this->assertNull( $a2->get_raw_attribute( 'bio' ) );
		$this->assertEquals( '', $a3->bio );
	}

	public function test_create_many_multiple_queries() {

		add_filter( 'ironbound_db_perform_insert_many_as_single_query', '__return_false' );

		$authors = Author::create_many( array(
			array( 'name' => 'Joe', 'bio' => 'Hi' ),
			array( 'bio' => null, 'name' => 'John' ),
			array( 'name' => 'James' ),
		) );

		$this->assertCount( 3, $authors );
		$this->assertContainsOnlyInstancesOf( '\IronBound\DB\Tests\Stub\Models\Author', $authors );

		list( $a1, $a2, $a3 ) = $authors;

		$this->assertNotNull( $a1->id );
		$this->assertNotNull( $a2->id );
		$this->assertNotNull( $a3->id );

		$this->assertEquals( 'Joe', $a1->name );
		$this->assertEquals( 'John', $a2->name );
		$this->assertEquals( 'James', $a3->name );

		$this->assertEquals( 'Hi', $a1->bio );
		$this->assertNull( $a2->get_raw_attribute( 'bio' ) );
		$this->assertEquals( '', $a3->bio );

		remove_filter( 'ironbound_db_perform_insert_many_as_single_query', '__return_false' );
	}

	public function test_refresh() {
		$model        = ModelWithForeignPost::create( array(
			'price' => 24.75
		) );
		$model->price = 50.00;

		$_model        = ModelWithForeignPost::get( $model->get_pk() );
		$_model->price = 45.00;
		$_model->save();

		$this->assertEquals( 50.00, $model->price );
		$this->assertTrue( $model->is_dirty() );

		$model->refresh();

		$this->assertEquals( 50.00, $model->price );
		$this->assertTrue( $model->is_dirty() );

		$model->price = 45;
		$this->assertEquals( 45.00, $model->price );
		$this->assertFalse( $model->is_dirty() );
	}

	public function test_refresh_and_destroy_local_changes() {
		$model        = ModelWithForeignPost::create( array(
			'price' => 24.75
		) );
		$model->price = 50.00;

		$_model        = ModelWithForeignPost::get( $model->get_pk() );
		$_model->price = 45.00;
		$_model->save();

		$this->assertEquals( 50.00, $model->price );
		$this->assertTrue( $model->is_dirty() );

		$model->refresh( true );

		$this->assertEquals( 45.00, $model->price );
		$this->assertFalse( $model->is_dirty() );
	}
}