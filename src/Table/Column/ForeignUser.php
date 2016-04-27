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

use IronBound\DB\Table\Column\Contracts\Savable;

/**
 * Class ForeignUser
 * @package IronBound\DB\Table\Column
 */
class ForeignUser extends BaseColumn implements Savable {

	/**
	 * @var string
	 */
	protected $key;

	/**
	 * ForeignUser constructor.
	 *
	 * @param string $name Column name.
	 * @param string $key  User table key. id | login | slug
	 */
	public function __construct( $name, $key = 'id' ) {
		parent::__construct( $name );

		if ( ! in_array( $key, array( 'id', 'login', 'slug' ) ) ) {
			throw new \InvalidArgumentException( "Invalid key '$key'." );
		}

		$this->key = $key;
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

		if ( ! $value instanceof \WP_User ) {
			throw new \InvalidArgumentException( 'ForeignUser can only save WP_User objects.' );
		}

		if ( ! $value->exists() ) {
			return $this->do_save( $value );
		}

		$current = get_user_by( 'id', $value->ID );

		if ( ! $current ) {
			return $this->do_save( $value );
		}

		$old = $current->to_array();
		$new = $value->to_array();

		if ( $this->has_changes( $old, $new ) ) {
			return $this->do_save( $value );
		}

		return $value;
	}

	/**
	 * Do the saving for a user.
	 *
	 * @since 2.0
	 *
	 * @param \WP_User $user
	 *
	 * @return \WP_User
	 */
	protected function do_save( \WP_User $user ) {

		if ( ! $user->exists() ) {
			$id = wp_insert_user( wp_slash( $user->to_array() ) );
		} else {
			$id = wp_update_user( wp_slash( $user->to_array() ) );
		}

		if ( is_wp_error( $id ) ) {
			throw new \InvalidArgumentException( 'Error encountered while saving WP_User: ' . $id->get_error_message() );
		}

		return get_user_by( 'id', $id );
	}
}