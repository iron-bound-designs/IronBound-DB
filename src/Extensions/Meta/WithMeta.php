<?php
/**
 * Contains the definition for the WithMeta interface.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Extensions\Meta;
use IronBound\DB\Extensions\Meta\MetaTable;

/**
 * Interface WithMeta
 * @package IronBound\DB\Model
 */
interface WithMeta {

	/**
	 * Add metadata to the model.
	 *
	 * @see   add_metadata()
	 *
	 * @since 2.0
	 *
	 * @param string $key    Metadata key.
	 * @param mixed  $value  Metadata value. Must be serializable if non-scalar. Must be slashed.
	 * @param bool   $unique Whether the specified metadata key should be unique for the object.
	 *
	 * @return int|false
	 */
	public function add_meta( $key, $value, $unique = false );

	/**
	 * Update metadata on the model. If no value already exists for the given
	 * metadata key, the metadata will be added.
	 *
	 * @see   update_metadata()
	 *
	 * @since 2.0
	 *
	 * @param string $key        Metadata key.
	 * @param mixed  $value      Metadata value. Must be serializable if non-scalar. Must be slashed.
	 * @param string $prev_value Optional, if specified, only update existing metadata with the specified value.
	 *
	 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public function update_meta( $key, $value, $prev_value = '' );

	/**
	 * Retrieve metadata from the model.
	 *
	 * @see   get_metadata()
	 *
	 * @since 2.0
	 *
	 * @param string $key    Metadata key. If not specified, retrieve all metadata from the model.
	 * @param bool   $single Optional. If given, return only the first value of the specified meta key.
	 *
	 * @return mixed Single metadata value, or array of values.
	 */
	public function get_meta( $key = '', $single = false );

	/**
	 * Delete metadata on the model.
	 *
	 * @see   delete_metadata()
	 *
	 * @since 2.0
	 *
	 * @param string $key        Metadata key.
	 * @param mixed  $value      Optional. Metadata value. Must be serializable if non-scalar. Must be slashed.
	 *                           If specified, only metadata with the given value will be deleted.
	 * @param bool   $delete_all Optional. If specified, metadata entries for all models,
	 *                           not just this one, will be deleted.
	 *
	 * @return bool True on successful delete, false on failure.
	 */
	public function delete_meta( $key, $value = '', $delete_all = false );

	/**
	 * Get the table the metadata is stored in.
	 *
	 * @since 2.0
	 *
	 * @return MetaTable
	 */
	public static function get_meta_table();

	/**
	 * Get the meta type.
	 *
	 * This is based on the table name, such that appending 'meta' will give the un-prefixed table name.
	 *
	 * For example, wp_plugin_booksmeta => plugin_books.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public static function get_meta_type();
}