<?php
/**
 * Test the TermSaver class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Saver;

use IronBound\DB\Saver\TermSaver;
use IronBound\DB\Saver\Saver;

/**
 * Class Test_TermSaver
 *
 * @package IronBound\DB\Tests\Saver
 */
class Test_TermSaver extends \IronBound\DB\Tests\TestCase {

	/**
	 * @var Saver
	 */
	protected static $saver;

	public static function setUpBeforeClass() {

		static::$saver = new TermSaver();

		parent::setUpBeforeClass();
	}

	public function test_get_pk() {

		$object = $this->factory()->term->create_and_get();

		$this->assertEquals( $object->term_id, static::$saver->get_pk( $object ) );
	}

	public function test_get_model() {

		$object = $this->factory()->term->create_and_get();

		$this->assertEquals( $object->name, static::$saver->get_model( $object->term_id )->name );
	}

	public function test_make_model() {

		$object = $this->factory()->term->create_and_get();
		$model  = static::$saver->make_model( $object->to_array() );

		$this->assertInstanceOf( 'WP_Term', $model );

		$this->assertEquals( $object->to_array(), $model->to_array() );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function test_exception_thrown_if_invalid_type() {
		static::$saver->save( new \Basic_Object() );
	}

	public function test_save_update() {

		$object   = $this->factory()->term->create_and_get();
		$modified = get_term( $object->term_id );

		$modified->name = 'New Term Name';

		$saved = static::$saver->save( $modified );

		$this->assertEquals( $object->slug, $modified->slug );
		$this->assertEquals( 'New Term Name', $saved->name );
		$this->assertEquals( 'New Term Name', get_term( $saved->term_id )->name );
	}

	public function test_save_create() {

		$saved = static::$saver->save( new \WP_Term( (object) array(
			'name'     => 'A Test Term',
			'taxonomy' => \WP_UnitTest_Factory_For_Term::DEFAULT_TAXONOMY
		) ) );

		$this->assertNotEmpty( $saved->term_id );
		$this->assertEquals( 'A Test Term', $saved->name );
	}
}