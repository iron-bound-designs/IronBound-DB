<?php
/**
 * Test the ForeignUser column.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Unit\Columns;

use IronBound\DB\Table\Column\ForeignUser;

/**
 * Class Test_ForeignUser
 *
 * @package IronBound\DB\Tests\Unit\Columns
 */
class Test_ForeignUser extends \IronBound\DB\Tests\TestCase {

	public function test_convert_raw_to_value_id() {

		$column = new ForeignUser( 'column' );

		$object_id = $this->factory()->user->create();
		$object    = $column->convert_raw_to_value( $object_id );

		$this->assertInstanceOf( 'WP_User', $object );
		$this->assertEquals( $object_id, $object->ID );
	}

	public function test_prepare_for_storage_id() {

		$column = new ForeignUser( 'column' );

		$object    = $this->factory()->user->create_and_get();
		$object_id = $column->prepare_for_storage( $object );

		$this->assertInstanceOf( 'WP_User', $object );
		$this->assertEquals( $object_id, $object->ID );
	}

	public function test_delete_callback_id() {

		$column    = new ForeignUser( 'column' );
		$object_id = $this->factory()->user->create();

		$called = false;
		$column->register_delete_callback( function () use ( &$called ) {
			$called = true;
		} );

		wp_delete_user( $object_id, true );

		$this->assertTrue( $called );
	}

	public function test_convert_raw_to_value_login() {

		$column = new ForeignUser( 'column', 'login' );

		$object_id = $this->factory()->user->create_and_get()->user_login;
		$object    = $column->convert_raw_to_value( $object_id );

		$this->assertInstanceOf( 'WP_User', $object );
		$this->assertEquals( $object_id, $object->user_login );
	}

	public function test_prepare_for_storage_login() {

		$column = new ForeignUser( 'column', 'login' );

		$object    = $this->factory()->user->create_and_get();
		$object_id = $column->prepare_for_storage( $object );

		$this->assertInstanceOf( 'WP_User', $object );
		$this->assertEquals( $object_id, $object->user_login );
	}

	public function test_delete_callback_login() {

		$column    = new ForeignUser( 'column', 'login' );
		$object_id = $this->factory()->user->create_and_get()->user_login;

		$called = false;
		$column->register_delete_callback( function ( $pk ) use ( $object_id, &$called ) {

			if ( $pk === $object_id ) {
				$called = true;
			}
		} );

		wp_delete_user( get_user_by( 'login', $object_id )->ID, true );

		$this->assertTrue( $called );
	}

	public function test_convert_raw_to_value_slug() {

		$column = new ForeignUser( 'column', 'slug' );

		$object_id = $this->factory()->user->create_and_get()->user_nicename;
		$object    = $column->convert_raw_to_value( $object_id );

		$this->assertInstanceOf( 'WP_User', $object );
		$this->assertEquals( $object_id, $object->user_nicename );
	}

	public function test_prepare_for_storage_slug() {

		$column = new ForeignUser( 'column', 'slug' );

		$object    = $this->factory()->user->create_and_get();
		$object_id = $column->prepare_for_storage( $object );

		$this->assertInstanceOf( 'WP_User', $object );
		$this->assertEquals( $object_id, $object->user_nicename );
	}

	public function test_delete_callback_slug() {

		$column    = new ForeignUser( 'column', 'slug' );
		$object_id = $this->factory()->user->create_and_get()->user_nicename;

		$called = false;
		$column->register_delete_callback( function ( $pk ) use ( $object_id, &$called ) {

			if ( $pk === $object_id ) {
				$called = true;
			}
		} );

		wp_delete_user( get_user_by( 'slug', $object_id )->ID, true );

		$this->assertTrue( $called );
	}
}