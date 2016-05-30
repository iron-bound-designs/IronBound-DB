<?php
/**
 * Users table.
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
 * Class Users
 * @package IronBound\DB\WP
 */
class Users extends BaseTable {

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return $wpdb->users;
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'users';
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {
		$columns = array(
			'ID'                  =>
				new IntegerBased( 'BIGINT', 'ID', array( 'unsigned', 'NOT NULL', 'auto_increment' ), array( 20 ) ),
			'user_login'          => new StringBased( 'VARCHAR', 'user_login', array( 'NOT NULL' ), array( 60 ) ),
			'user_pass'           => new StringBased( 'VARCHAR', 'user_pass', array( 'NOT NULL' ), array( 255 ) ),
			'user_nicename'       => new StringBased( 'VARCHAR', 'user_nicename', array( 'NOT NULL' ), array( 50 ) ),
			'user_email'          => new StringBased( 'VARCHAR', 'user_email', array( 'NOT NULL' ), array( 100 ) ),
			'user_url'            => new StringBased( 'VARCHAR', 'user_url', array( 'NOT NULL' ), array( 100 ) ),
			'user_registered'     => new DateTime( 'user_registered', array( 'NOT NULL' ) ),
			'user_activation_key' => new StringBased( 'VARCHAR', 'user_activation_key', array( 'VARCHAR' ), array( 255 ) ),
			'user_status'         => new IntegerBased( 'INT', 'user_status', array( 'NOT NULL' ), array( 11 ) ),
			'display_name'        => new StringBased( 'VARCHAR', 'display_name', array( 'NOT NULL' ), array( 250 ) )
		);

		if ( is_multisite() ) {
			$columns['spam']    = new IntegerBased( 'TINYINT', 'spam', array( 'NOT NULL' ), array( 2 ) );
			$columns['deleted'] = new IntegerBased( 'TINYINT', 'deleted', array( 'NOT NULL' ), array( 2 ) );
		}

		return $columns;
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		$columns = array(
			'ID'                  => 0,
			'user_login'          => '',
			'user_pass'           => '',
			'user_nicename'       => '',
			'user_email'          => '',
			'user_url'            => '',
			'user_registered'     => '',
			'user_activation_key' => '',
			'user_status'         => 0,
			'display_name'        => ''
		);

		if ( is_multisite() ) {
			$columns['spam']    = 0;
			$columns['deleted'] = 0;
		}

		return $columns;
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