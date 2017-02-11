<?php
/**
 * Test the CommentSaver class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Saver;

use IronBound\DB\Saver\CommentSaver;
use IronBound\DB\Saver\Saver;

/**
 * Class Test_CommentSaver
 *
 * @package IronBound\DB\Tests\Saver
 */
class Test_CommentSaver extends \IronBound\DB\Tests\TestCase {

	/**
	 * @var Saver
	 */
	protected static $saver;

	public static function setUpBeforeClass() {

		static::$saver = new CommentSaver();

		parent::setUpBeforeClass();
	}

	public function test_get_pk() {

		$object = $this->factory()->comment->create_and_get();

		$this->assertEquals( $object->comment_ID, static::$saver->get_pk( $object ) );
	}

	public function test_get_model() {

		$object = $this->factory()->comment->create_and_get();

		$this->assertEquals( $object->comment_content, static::$saver->get_model( $object->comment_ID )->comment_content );
	}

	public function test_make_model() {

		$object = $this->factory()->comment->create_and_get();
		$model  = static::$saver->make_model( $object->to_array() );

		$this->assertInstanceOf( 'WP_Comment', $model );

		$this->assertEquals( $object->to_array(), $model->to_array() );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function test_exception_thrown_if_invalid_type() {
		static::$saver->save( new \Basic_Object() );
	}

	public function test_save_update() {

		$object   = $this->factory()->comment->create_and_get( array(
			'comment_post_ID' => $this->factory()->post->create()
		) );
		$modified = get_comment( $object->comment_ID );

		$modified->comment_content = 'New Comment Content';

		$saved = static::$saver->save( $modified );

		$this->assertEquals( $object->comment_author, $modified->comment_author );
		$this->assertEquals( 'New Comment Content', $saved->comment_content );
		$this->assertEquals( 'New Comment Content', get_comment( $saved->comment_ID )->comment_content );
	}

	public function test_save_create() {

		$saved = static::$saver->save( new \WP_Comment( (object) array(
			'comment_content' => 'A Test Comment'
		) ) );

		$this->assertNotEmpty( $saved->comment_ID );
		$this->assertEquals( 'A Test Comment', $saved->comment_content );
	}
}