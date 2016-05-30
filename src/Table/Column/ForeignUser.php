<?php
/**
 * Contains the class for the ForeignUser column type.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Column;

use IronBound\DB\Saver\UserSaver;
use IronBound\DB\Table\Column\Contracts\Savable;
use IronBound\DB\Table\ForeignKey\DeleteConstrainable;

/**
 * Class ForeignUser
 * @package IronBound\DB\Table\Column
 */
class ForeignUser extends BaseColumn implements Savable, Foreign, DeleteConstrainable {

	/**
	 * @var UserSaver
	 */
	protected $saver;

	/**
	 * @var string
	 */
	protected $key;

	/**
	 * ForeignUser constructor.
	 *
	 * @param string    $name Column name.
	 * @param UserSaver $saver
	 * @param string    $key  User table key. id | login | slug
	 */
	public function __construct( $name, UserSaver $saver, $key = 'id' ) {
		parent::__construct( $name );

		if ( ! in_array( $key, array( 'id', 'login', 'slug' ) ) ) {
			throw new \InvalidArgumentException( "Invalid key '$key'." );
		}

		$this->saver = $saver;
		$this->key   = $key;
	}

	/**
	 * @inheritDoc
	 */
	public function get_foreign_table_name( \wpdb $wpdb ) {
		return $wpdb->users;
	}

	/**
	 * @inheritDoc
	 */
	public function get_foreign_table_column_name() {
		switch ( $this->key ) {
			case 'id':
				return 'ID';
			case 'login':
				return 'user_login';
			case 'slug':
				return 'user_nicename';
			default:
				throw new \UnexpectedValueException( 'Unexpected key type.' );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_definition() {
		switch ( $this->key ) {
			case 'id':
				return "{$this->name} bigint(20) unsigned NOT NULL";
			case 'login':
				return "{$this->name} varchar(60) NOT NULL";
			case 'slug':
				return "{$this->name} varchar(50) NOT NULL";
			default:
				throw new \UnexpectedValueException( 'Unexpected key type.' );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_mysql_type() {
		switch ( $this->key ) {
			case 'id':
				return 'BIGINT';
			case 'login':
			case 'slug':
				return 'VARCHAR';
			default:
				throw new \UnexpectedValueException( 'Unexpected key type.' );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function convert_raw_to_value( $raw, \stdClass $row = null ) {
		return get_user_by( $this->key, $raw );
	}

	/**
	 * @inheritDoc
	 */
	public function prepare_for_storage( $value ) {

		if ( $value instanceof \WP_User ) {

			switch ( $this->key ) {
				case 'id':
					return $value->ID;
				case 'login':
					return $value->user_login;
				case 'slug':
					return $value->user_nicename;
				default:
					throw new \UnexpectedValueException( 'Unexpected key type.' );
			}
		}

		switch ( $this->key ) {
			case 'id':
				return absint( $value );
			case 'login':
			case 'slug':
				return trim( $value );
			default:
				throw new \UnexpectedValueException( 'Unexpected key type.' );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function save( $value ) {
		return $this->saver->save( $value );
	}

	/**
	 * @inheritDoc
	 */
	public function get_pk( $value ) {
		return $this->saver->get_pk( $value );
	}

	/**
	 * @inheritDoc
	 */
	public function register_delete_callback( $callback ) {

		$saver = $this->saver;

		add_action( 'delete_user', function ( $id, $reassign ) use ( $callback, $saver ) {

			$user = get_user_by( 'id', $id );

			$callback( $saver->get_pk( $user ), $user, $reassign );

		}, 10, 2 );
	}
}