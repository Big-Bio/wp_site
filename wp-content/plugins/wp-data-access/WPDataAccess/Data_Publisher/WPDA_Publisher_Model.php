<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\User_Menu
 */

namespace WPDataAccess\Data_Publisher {

	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Publisher_Model
	 *
	 * Data Publisher model which contains some basic functionality for publications.
	 *
	 * @author  Peter Schulz
	 * @since   2.0.15
	 */
	class WPDA_Publisher_Model {

		/**
		 * Return the number of publication
		 *
		 * @return int
		 */
		public static function count() {
			global $wpdb;
			$query = 'SELECT count(*) noitems FROM ' . self::get_table_name();

			$result = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
			return 1 === $wpdb->num_rows ? $result[0]['noitems'] : 0;
		}

		/**
		 * Return the publication for a specific publication id
		 *
		 * @param int $pub_id Publication id
		 *
		 * @return bool|array
		 */
		public static function get_publication( $pub_id ) {
			global $wpdb;
			$query =
				$wpdb->prepare( '
							SELECT *
							  FROM ' . self::get_table_name() . '
							 WHERE pub_id = %d
						',
					[
						$pub_id,
					]
				); // db call ok; no-cache ok.

			$dataset = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
			return 1 === $wpdb->num_rows ? $dataset : false;
		}

		/**
		 * Base table name
		 *
		 * @return string Database table name
		 */
		public static function get_table_name() {
			global $wpdb;

			return $wpdb->prefix . WPDA::get_option( WPDA::OPTION_WPDA_PREFIX ) . 'publisher';
		}

		/**
		 * Checks if the database exists
		 *
		 * @return bool
		 */
		public static function table_exists() {
			$wpda_dictionary_exist = new WPDA_Dictionary_Exist( '', static::get_table_name() );

			return $wpda_dictionary_exist->table_exists( false );
		}

	}

}