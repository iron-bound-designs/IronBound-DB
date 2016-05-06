<?php
/**
 * Contains the class for the ForeignComment column type.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Column;

use IronBound\DB\Saver\CommentSaver;
use IronBound\DB\Table\Column\Contracts\Savable;

/**
 * Class ForeignComment
 * @package IronBound\DB\Table\Column
 */
class ForeignComment extends BaseColumn implements Savable {

	/**
	 * @var CommentSaver
	 */
	protected $saver;

	/**
	 * ForeignComment constructor.
	 *
	 * @param string       $name Column name.
	 * @param CommentSaver $saver
	 */
	public function __construct( $name, CommentSaver $saver ) {
		parent::__construct( $name );

		$this->saver = $saver;
	}

	/**
	 * @inheritDoc
	 */
	public function get_definition() {
		return "{$this->name} bigint(20) unsigned NOT NULL";
	}

	/**
	 * @inheritDoc
	 */
	public function get_mysql_type() {
		return 'BIGINT';
	}

	/**
	 * @inheritDoc
	 */
	public function convert_raw_to_value( $raw, \stdClass $row = null ) {
		return get_comment( $raw );
	}

	/**
	 * @inheritDoc
	 */
	public function prepare_for_storage( $value ) {

		if ( $value instanceof \WP_Comment || $value instanceof \stdClass ) {
			return $value->comment_ID;
		}

		return absint( $value );
	}

	/**
	 * @inheritDoc
	 */
	public function save( $value ) {
		return $this->saver->save( $value );
	}
}