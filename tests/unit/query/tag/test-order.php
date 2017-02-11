<?php
/**
 * Test the Order SQL tag.
 *
 * @author      Iron Bound Designs
 * @since       1.2
 * @copyright   2016 (c) Iron Bound Designs.
 * @license     MIT
 */

namespace IronBound\DB\Query\Tag\Tests;

use IronBound\DB\Query\Tag\Order;

/**
 * Class Test_Order
 * @package IronBound\DB\Query\Tag\Tests
 */
class Test_Order extends \IronBound\DB\Tests\TestCase {

	public function test_rand() {

		$order = new Order( Order::RAND );
		$this->assertEquals( "ORDER BY RAND()", (string) $order );
	}

	public function test_single_order_no_direction() {

		$order = new Order( 'date' );
		$this->assertEquals( 'ORDER BY date', (string) $order );
	}

	public function test_single_direction() {

		$order = new Order( 'date', Order::ASC );
		$this->assertEquals( "ORDER BY date ASC", (string) $order );
	}

	public function test_multi_orders() {

		$order = new Order( 'added', Order::ASC );
		$order->then( 'updated', Order::DESC );

		$this->assertEquals( "ORDER BY added ASC, updated DESC", (string) $order );
	}

	/**
	 * @expectedException \LogicException
	 */
	public function test_multi_order_rejected_if_rand_mode() {

		$order = new Order( Order::RAND );
		$order->then( 'column' );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function test_invalid_direction_rejected() {

		new Order( 'column', 'what' );
	}
}