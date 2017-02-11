<?php
/**
 * Test the UserSaver class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Saver;

use IronBound\DB\Saver\UserSaver;
use IronBound\DB\Saver\Saver;

/**
 * Class Test_UserSaver
 *
 * @package IronBound\DB\Tests\Saver
 */
class Test_UserSaver extends \IronBound\DB\Tests\TestCase {

	/**
	 * @var Saver
	 */
	protected static $saver;

	public static function setUpBeforeClass() {

		static::$saver = new UserSaver();

		parent::setUpBeforeClass();
	}

	public function test_get_pk() {

		$object = $this->factory()->user->create_and_get();

		$this->assertEquals( $object->ID, static::$saver->get_pk( $object ) );
	}

	public function test_get_pk_login() {

		$saver = new UserSaver( 'login' );
		$user  = $this->factory()->user->create_and_get();

		$this->assertEquals( $user->user_login, $saver->get_pk( $user ) );
	}

	public function test_get_pk_slug() {

		$saver = new UserSaver( 'slug' );
		$user  = $this->factory()->user->create_and_get();

		$this->assertEquals( $user->user_nicename, $saver->get_pk( $user ) );
	}

	public function test_get_model() {

		$object = $this->factory()->user->create_and_get();

		$this->assertEquals( $object->user_login, static::$saver->get_model( $object->ID )->user_login );
	}

	public function test_get_model_login() {

		$saver  = new UserSaver( 'login' );
		$object = $this->factory()->user->create_and_get();

		$this->assertEquals( $object->user_login, $saver->get_model( $object->user_login )->user_login );
	}

	public function test_get_model_slug() {

		$saver  = new UserSaver( 'slug' );
		$object = $this->factory()->user->create_and_get();

		$this->assertEquals( $object->user_login, $saver->get_model( $object->user_nicename )->user_login );
	}

	public function test_make_model() {

		$object = $this->factory()->user->create_and_get();
		$model  = static::$saver->make_model( $object->to_array() );

		$this->assertInstanceOf( 'WP_User', $model );

		$this->assertEquals( $object->to_array(), $model->to_array() );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function test_exception_thrown_if_invalID_type() {
		static::$saver->save( new \Basic_Object() );
	}

	public function test_save_update() {

		$object   = $this->factory()->user->create_and_get();
		$modified = get_userdata( $object->ID );

		$modified->display_name = 'New Display Name';

		$saved = static::$saver->save( $modified );

		$this->assertEquals( $object->user_login, $modified->user_login );
		$this->assertEquals( 'New Display Name', $saved->display_name );
		$this->assertEquals( 'New Display Name', get_userdata( $saved->ID )->display_name );
	}

	public function test_save_create() {

		$saved = static::$saver->save( new \WP_User( (object) array(
			'ID'           => 0,
			'display_name' => 'A Test User',
			'user_login'   => 'My New User'
		) ) );

		$this->assertNotEmpty( $saved->ID );
		$this->assertEquals( 'A Test User', $saved->display_name );
	}
}