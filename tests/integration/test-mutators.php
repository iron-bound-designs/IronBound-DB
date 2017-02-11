<?php
/**
 * Test model mutators.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests;

use IronBound\DB\Manager;
use IronBound\DB\Tests\Stub\Models\ModelWithForeignPost;
use IronBound\DB\Tests\Stub\Models\ModelWithMutators;
use IronBound\DB\Tests\Stub\Tables\TableWithForeignPost;

/**
 * Class Test_Mutators
 * @package IronBound\DB\Tests
 */
class Test_Mutators extends \IronBound\DB\Tests\TestCase {

	public function setUp() {
		parent::setUp();

		Manager::register( new TableWithForeignPost(), '', get_class( new ModelWithForeignPost() ) );
		Manager::maybe_install_table( Manager::get( 'with-foreign-post' ) );
	}

	public function test_set_mutator_called() {

		$model = new ModelWithMutators( array(
			'price' => 329.459
		) );
		$this->assertEquals( 329.46, $model->price );
	}

	public function test_get_mutator_called() {

		$model = new ModelWithMutators( array(
			'post' => self::factory()->post->create_and_get()
		) );
		$this->assertEquals( $model, $model->post->model );
	}
}