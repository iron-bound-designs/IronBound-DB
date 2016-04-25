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
use IronBound\DB\Tests\Stub\Models\ModelWithForeignPost;
use IronBound\DB\Tests\Stub\Tables\TableWithForeignPost;

/**
 * Class Test_Crud
 * @package IronBound\DB\Tests
 */
class Test_Crud extends \WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		Manager::register( new TableWithForeignPost() );
		Manager::maybe_install_table( Manager::get( 'with-foreign-post' ) );
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
}