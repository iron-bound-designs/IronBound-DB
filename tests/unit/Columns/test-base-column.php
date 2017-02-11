<?php
/**
 * Test the Base Column class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Unit\Columns;

use IronBound\DB\Table\Column\BaseColumn;

/**
 * Class Test_BaseColumn
 *
 * @package IronBound\DB\Tests\Unit\Columns
 */
class Test_BaseColumn extends \IronBound\DB\Tests\TestCase {

	public function test_get_definition_without_column_name() {

		/** @var BaseColumn $column */
		$column = $this->getMockBuilder( '\IronBound\DB\Table\Column\BaseColumn' )
		               ->setConstructorArgs( array( 'column', array( 'NOT NULL', 'auto_increment' ), array( 10, 2 ) ) )
		               ->setMethods( array( 'get_mysql_type' ) )->getMockForAbstractClass();
		$column->method( 'get_mysql_type' )->willReturn( 'BIGINT' );

		$reflected = new \ReflectionMethod( $column, 'get_definition_without_column_name' );
		$reflected->setAccessible( true );

		$result = $reflected->invoke( $column, array( 'auto_increment' ) );

		$this->assertEquals( 'BIGINT(10,2) NOT NULL', $result );
	}
}