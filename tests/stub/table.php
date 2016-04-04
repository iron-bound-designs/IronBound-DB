<?php
/**
 * Contains the stub table class.
 *
 * @author      Iron Bound Designs
 * @since       1.2
 * @copyright   2016 (c) Iron Bound Designs.
 * @license     MIT
 */

namespace IronBound\DB\Tests;

use IronBound\DB\Table\Table;

/**
 * Class Stub_Table
 * @package IronBound\DB\Tests
 */
class Stub_Table implements Table {

	public function get_table_name( \wpdb $wpdb ) {
		return $wpdb->prefix . 'test_table';
	}

	public function get_slug() {
		return 'test_table';
	}

	public function get_columns() {
		return array(
			'ID'        => '%d',
			'price'     => '%f',
			'title'     => '%s',
			'published' => '%s'
		);
	}

	public function get_column_defaults() {
		return array(
			'ID'        => '',
			'price'     => '',
			'title'     => '',
			'published' => current_time( 'mysql', true )
		);
	}

	public function get_primary_key() {
		return 'ID';
	}

	public function get_creation_sql( \wpdb $wpdb ) {
		return "CREATE TABLE {$this->get_table_name($wpdb)} (
		ID BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
		price DECIMAL(10,2) unsigned NOT NULL,
		title VARCHAR(255),
		published DATETIME NOT NULL,
		PRIMARY KEY  (ID)
		) {$wpdb->get_charset_collate()};";
	}

	public function get_version() {
		return 1;
	}
}