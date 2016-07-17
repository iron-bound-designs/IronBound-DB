<?php
/**
 * Posts table in WordPress.
 *
 * We only really care about the table columns,
 * so we can use the automatic escaping FluentQuery provides.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\WP;

use IronBound\DB\Saver\PostSaver;
use IronBound\DB\Saver\UserSaver;
use IronBound\DB\Table\BaseTable;
use IronBound\DB\Table\Column\DateTime;
use IronBound\DB\Table\Column\ForeignPost;
use IronBound\DB\Table\Column\ForeignUser;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;

/**
 * Class Posts
 * @package IronBound\DB\WP
 */
class Posts extends BaseTable {

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return $wpdb->posts;
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'posts';
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {
		return array(
			'ID'                    =>
				new IntegerBased( 'BIGINT', 'ID', array( 'unsigned', 'NOT NULL', 'auto_increment' ), array( 20 ) ),
			'post_author'           => new ForeignUser( 'post_author' ),
			'post_date'             => new DateTime( 'post_date' ),
			'post_date_gmt'         => new DateTime( 'post_date_gmt' ),
			'post_content'          => new StringBased( 'LONGTEXT', 'post_content', array( 'NOT NULL' ) ),
			'post_title'            => new StringBased( 'TEXT', 'post_title', array( 'NOT NULL' ) ),
			'post_excerpt'          => new StringBased( 'TEXT', 'post_excerpt', array( 'NOT NULL' ) ),
			'post_status'           => new StringBased( 'VARCHAR', 'post_status', array( 'NOT NULL' ), array( 20 ) ),
			'comment_status'        => new StringBased( 'VARCHAR', 'comment_status', array( 'NOT NULL' ), array( 20 ) ),
			'ping_status'           => new StringBased( 'VARCHAR', 'ping_status', array( 'NOT NULL' ), array( 20 ) ),
			'post_password'         => new StringBased( 'VARCHAR', 'post_password', array( 'NOT NULL' ), array( 20 ) ),
			'post_name'             => new StringBased( 'VARCHAR', 'post_name', array( 'NOT NULL' ), array( 200 ) ),
			'to_ping'               => new StringBased( 'TEXT', 'to_ping', array( 'NOT NULL' ) ),
			'pinged'                => new StringBased( 'TEXT', 'pinged', array( 'NOT NULL' ) ),
			'post_modified'         => new DateTime( 'post_modified' ),
			'post_modified_gmt'     => new DateTime( 'post_modified_gmt' ),
			'post_content_filtered' => new StringBased( 'LONGTEXT', 'post_content', array( 'NOT NULL' ) ),
			'post_parent'           => new ForeignPost( 'post_parent' ),
			'guid'                  => new StringBased( 'VARCHAR', 'guid', array( 'NOT NULL' ), array( 255 ) ),
			'menu_order'            => new IntegerBased( 'INT', 'menu_order', array( 'NOT NULL' ), array( 11 ) ),
			'post_type'             => new StringBased( 'VARCHAR', 'post_type', array( 'NOT NULL' ), array( 20 ) ),
			'post_mime_type'        => new StringBased( 'VARCHAR', 'post_mime_type', array( 'NOT NULL' ), array( 100 ) ),
			'comment_count'         => new IntegerBased( 'BIGINT', 'comment_count', array( 'NOT NULL' ), array( 20 ) ),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'ID'                    => 0,
			'post_author'           => 0,
			'post_date'             => '',
			'post_date_gmt'         => '',
			'post_content'          => '',
			'post_title'            => '',
			'post_status'           => 'publish',
			'comment_status'        => 'open',
			'ping_status'           => 'open',
			'post_password'         => '',
			'post_name'             => '',
			'to_ping'               => '',
			'post_modified'         => '',
			'post_modified_gmt'     => '',
			'post_content_filtered' => '',
			'post_parent'           => 0,
			'guid'                  => '',
			'menu_order'            => 0,
			'post_type'             => 'post',
			'post_mime_type'        => '',
			'comment_count'         => 0
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_primary_key() {
		return 'ID';
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() {
		return 1;
	}
}