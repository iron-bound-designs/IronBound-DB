<?php
/**
 * Test the has foriegn relations.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests;

use IronBound\DB\Manager;
use IronBound\DB\Tests\Stub\Models\ModelWithAllForeign;
use IronBound\DB\Tests\Stub\Tables\TableWithAllForeign;

/**
 * Class Test_Relations_Has_Foreign
 *
 * @package IronBound\DB\Tests
 */
class Test_Relations_Has_Foreign extends \IronBound\DB\Tests\TestCase {

	public function setUp() {
		parent::setUp();

		Manager::register( new TableWithAllForeign(), '', get_class( new ModelWithAllForeign() ) );
		Manager::maybe_install_table( Manager::get( 'with-all-foreign' ) );
	}

	public function test_terms_eager_loading() {

		$t1 = self::factory()->term->create_and_get();
		$t2 = self::factory()->term->create_and_get();

		$m1 = ModelWithAllForeign::create( array( 'term' => $t1 ) );
		$m2 = ModelWithAllForeign::create( array( 'term' => $t2 ) );
		$m3 = ModelWithAllForeign::create( array( 'term' => $t1 ) );

		$this->flush_cache();

		$models = ModelWithAllForeign::with( 'term' )->results();

		$num_queries = $GLOBALS['wpdb']->num_queries;

		$this->assertEquals( 3, $models->count() );

		/** @var ModelWithAllForeign $m1 */
		$m1 = $models->get_model( $m1->get_pk() );
		$this->assertEquals( $t1->name, $m1->term->name ); // Name tests the Terms table
		$this->assertEquals( $t1->description, $m1->term->description ); // Description tests the TermTaxonomy table

		/** @var ModelWithAllForeign $m2 */
		$m2 = $models->get_model( $m2->get_pk() );
		$this->assertEquals( $t2->name, $m2->term->name );
		$this->assertEquals( $t2->description, $m2->term->description );

		/** @var ModelWithAllForeign $m3 */
		$m3 = $models->get_model( $m3->get_pk() );
		$this->assertEquals( $t1->name, $m3->term->name );
		$this->assertEquals( $t1->description, $m3->term->description );

		$this->assertEquals( $num_queries, $GLOBALS['wpdb']->num_queries );
	}
}