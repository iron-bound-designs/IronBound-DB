<?php
/**
 * Contains the InvalidDataForColumnException class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Exception;

use Exception;
use IronBound\DB\Table\Column\BaseColumn;

/**
 * Class InvalidDataForColumnException
 * @package IronBound\DB\Exception
 */
class InvalidDataForColumnException extends \InvalidArgumentException {

	/**
	 * @var BaseColumn
	 */
	private $column;
	
	/**
	 * @var mixed
	 */
	private $data;

	/**
	 * @inheritDoc
	 */
	public function __construct( $message, BaseColumn $column = null, $data = null, $code = 0, Exception $previous = null ) {

		if ( $column ) {
			$_data = print_r( $data, true );
			$message .= " Column: {$column->get_mysql_type()}, Data: {$_data}.";
		}

		parent::__construct( $message, $code, $previous );

		$this->column = $column;
		$this->data   = $data;
	}

	/**
	 * Get the column.
	 *
	 * @since 2.0
	 *
	 * @return BaseColumn
	 */
	public function get_column() {
		return $this->column;
	}

	/**
	 * Get the data.
	 *
	 * @since 2.0
	 *
	 * @return mixed
	 */
	public function get_data() {
		return $this->data;
	}
}