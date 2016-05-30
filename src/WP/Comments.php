<?php
/**
 * Comments table.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\WP;

use IronBound\DB\Table\BaseTable;
use IronBound\DB\Table\Column\DateTime;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;

/**
 * Class Comments
 * @package IronBound\DB\WP
 */
class Comments extends BaseTable {
	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return $wpdb->comments;
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'comments';
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {
		return array(
			'comment_ID'           =>
				new IntegerBased( 'BIGINT', 'ID', array( 'unsigned', 'NOT NULL', 'auto_increment' ), array( 20 ) ),
			'comment_post_ID'      =>
				new IntegerBased( 'BIGINT', 'ID', array( 'unsigned', 'NOT NULL', 'auto_increment' ), array( 20 ) ),
			'comment_author'       => new StringBased( 'TINYTEXT', 'comment_author', array( 'NOT NULL' ) ),
			'comment_author_email' => new StringBased( 'VARCHAR', 'comment_author_email', array( 'NOT NULL' ), array( 100 ) ),
			'comment_author_url'   => new StringBased( 'VARCHAR', 'comment_author_url', array( 'NOT NULL' ), array( 200 ) ),
			'comment_author_IP'    => new StringBased( 'VARCHAR', 'comment_author_IP', array( 'NOT NULL' ), array( 100 ) ),
			'comment_date'         => new DateTime( 'comment_date' ),
			'comment_date_gmt'     => new DateTime( 'comment_date_gmt' ),
			'comment_content'      => new StringBased( 'TEXT', 'comment_content', array( 'NOT NULL' ) ),
			'comment_karma'        => new IntegerBased( 'INT', 'comment_karma', array( 'NOT NULL' ), array( 11 ) ),
			'comment_approved'     => new StringBased( 'VARCHAR', 'comment_approved', array( 'NOT NULL' ), array( 20 ) ),
			'comment_agent'        => new StringBased( 'VARCHAR', 'comment_agent', array( 'NOT NULL' ), array( 255 ) ),
			'comment_type'         => new StringBased( 'VARCHAR', 'comment_type', array( 'NOT NULL' ), array( 20 ) ),
			'comment_parent'       =>
				new IntegerBased( 'BIGINT', 'comment_parent', array( 'unsigned', 'NOT NULL' ), array( 20 ) ),
			'user_id'              =>
				new IntegerBased( 'BIGINT', 'user_id', array( 'unsigned', 'NOT NULL' ), array( 20 ) ),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'comment_ID'           => 0,
			'comment_post_ID'      => 0,
			'comment_author'       => '',
			'comment_author_email' => '',
			'comment_author_url'   => '',
			'comment_author_IP'    => '',
			'comment_date'         => '',
			'comment_date_gmt'     => '',
			'comment_content'      => '',
			'comment_karma'        => 0,
			'comment_approved'     => '',
			'comment_agent'        => '',
			'comment_type'         => '',
			'comment_parent'       => 0,
			'user_id'              => 0,
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_primary_key() {
		return 'comment_ID';
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() {
		return 1;
	}
}