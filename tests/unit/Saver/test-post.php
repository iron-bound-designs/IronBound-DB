<?php
/**
 * Test the PostSaver class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Saver;

use IronBound\DB\Saver\PostSaver;
use IronBound\DB\Saver\Saver;

/**
 * Class Test_PostSaver
 *
 * @package IronBound\DB\Tests\Saver
 */
class Test_PostSaver extends \IronBound\DB\Tests\TestCase {

	/**
	 * @var Saver
	 */
	protected static $saver;

	public static function setUpBeforeClass() {

		static::$saver = new PostSaver();

		parent::setUpBeforeClass();
	}

	public function test_get_pk() {

		$object = $this->factory()->post->create_and_get();

		$this->assertEquals( $object->ID, static::$saver->get_pk( $object ) );
	}

	public function test_get_model() {

		$object = $this->factory()->post->create_and_get();

		$this->assertEquals( $object->post_title, static::$saver->get_model( $object->ID )->post_title );
	}

	public function test_make_model() {

		$object = $this->factory()->post->create_and_get();
		$model  = static::$saver->make_model( $object->to_array() );

		$this->assertInstanceOf( 'WP_Post', $model );

		$this->assertEquals( $object->to_array(), $model->to_array() );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function test_exception_thrown_if_invalid_type() {
		static::$saver->save( new \Basic_Object() );
	}

	public function test_save_update() {

		$object   = $this->factory()->post->create_and_get();
		$modified = get_post( $object->ID );

		$modified->post_title = 'New Post Title';

		$saved = static::$saver->save( $modified );

		$this->assertEquals( $object->post_name, $modified->post_name );
		$this->assertEquals( 'New Post Title', $saved->post_title );
		$this->assertEquals( 'New Post Title', get_post( $saved->ID )->post_title );
	}

	public function test_save_create() {

		$saved = static::$saver->save( new \WP_Post( (object) array(
			'post_type'  => 'page',
			'post_title' => 'A Test Page'
		) ) );

		$this->assertNotEmpty( $saved->ID );
		$this->assertEquals( 'page', $saved->post_type );
		$this->assertEquals( 'A Test Page', $saved->post_title );
	}
}