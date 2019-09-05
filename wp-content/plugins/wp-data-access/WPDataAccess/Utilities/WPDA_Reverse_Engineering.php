<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Utilities
 */

namespace WPDataAccess\Utilities {

	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Reverse_Engineering
	 *
	 * @author  Peter Schulz
	 * @since   2.0.13
	 */
	class WPDA_Reverse_Engineering {

		/**
		 * Data base table name
		 *
		 * @var string
		 */
		protected $table_name;

		/**
		 * WPDA_Reverse_Engineering constructor
		 *
		 * @param string $table_name Database table name
		 *
		 * @since   2.0.13
		 */
		public function __construct( $table_name ) {
			$this->table_name = $table_name;
		}

		/**
		 * Get table info in Data Designer format
		 *
		 * @param string $design_mode Possible values are: Basic or Advanced
		 *
		 * @return array Database table in Data Designer format
		 *
		 * @since   2.0.13
		 */
		public function get_designer_format( $design_mode ) {
			$table_structure = [];

			$tab  = $this->get_table_info();
			$rows = $this->get_table_columns();
			$idxs = $this->get_table_indexes();

			$idx_current         = '';
			$idx_current_unique  = '';
			$idx_current_columns = '';
			$idx_array           = [];
			foreach ( $idxs as $idx ) {
				if ( $idx['index_name'] !== $idx_current ) {
					if ( $idx_current_columns !== '' ) {
						$idx_array[] = [
							'index_name'   => $idx_current,
							'unique'       => $idx_current_unique,
							'column_names' => rtrim( $idx_current_columns, "," ),
						];
					}
					$idx_current         = $idx['index_name'];
					$idx_current_unique  = $idx['non_unique'] === '1' ? 'No' : 'Yes';
					$idx_current_columns = $idx['column_name'] . ',';
				} else {
					$idx_current_unique  = $idx['non_unique'] === '1' ? 'No' : 'Yes';
					$idx_current_columns .= $idx['column_name'] . ',';
				}
			}
			if ( $idx_current !== '' ) {
				$idx_array[] = [
					'index_name'   => $idx_current,
					'unique'       => $idx_current_unique,
					'column_names' => rtrim( $idx_current_columns, "," ),
				];
			}

			foreach ( $rows as $row ) {
				$obj              = (object) null;
				$obj->column_name = $row['column_name'];
				$obj->data_type   = $row['data_type'];
				if ( stripos( $row['column_type'], 'unsigned' ) !== false ) {
					$obj->type_attribute = 'unsigned';
				} elseif ( stripos( $row['column_type'], 'unsigned zerofill' ) !== false ) {
					$obj->type_attribute = 'unsigned zerofill';
				} else {
					$obj->type_attribute = '';
				}
				$obj->key        = 'PRI' === $row['column_key'] ? 'Yes' : 'No';
				$obj->mandatory  = 'YES' === $row['is_nullable'] ? 'No' : 'Yes';
				$obj->max_length = '';
				if ( 'varchar' === $row['data_type'] || 'char' === $row['data_type'] ||
				     'varbinary' === $row['data_type'] || 'binary' === $row['data_type'] ||
				     'tinytext' === $row['data_type'] || 'tinyblob' === $row['data_type'] ) {
					$obj->max_length = $row['character_maximum_length'];
				}
				$obj->extra = $row['extra'];
				if ( '' !== $row['column_default'] && null !== $row['column_default'] ) {
					if ( 'number' === WPDA::get_type( $obj->data_type ) ) {
						$obj->default = $row['column_default'];
					} else {
						$obj->default = "'{$row['column_default']}'";
					}
				} else {
					$obj->default = '';
				}
				$obj->list = '';
				if ( 'enum' === $row['data_type'] ) {
					$obj->list = substr( substr( $row['column_type'], 5 ), 0, - 1 );
				} elseif ( 'set' === $row['data_type'] ) {
					$obj->list = substr( substr( $row['column_type'], 4 ), 0, - 1 );
				} elseif ( null !== $row['numeric_precision'] ) {
					$obj->max_length = $row['numeric_precision'];
				}

				$table_structure[] = $obj;
			}

			return [
				'design_mode' => $design_mode,
				'engine'      => $tab[0]['engine'],
				'collation'   => $tab[0]['table_collation'],
				'table'       => $table_structure,
				'indexes'     => $idx_array,
			];
		}

		/**
		 * Get database table info
		 *
		 * @return array Table info
		 *
		 * @since   2.0.13
		 */
		protected function get_table_info() {
			global $wpdb;

			$query = $wpdb->prepare(
				'
					SELECT engine,
				           table_collation
					FROM   information_schema.tables
					WHERE  table_schema = %s
					  AND  table_name   = %s
				',
				[
					$wpdb->dbname,
					$this->table_name,
				]
			);

			return $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
		}

		/**
		 * Get database column info
		 *
		 * @return array Table column info
		 *
		 * @since   2.0.13
		 */
		protected function get_table_columns() {
			global $wpdb;

			$query = $wpdb->prepare(
				'
					SELECT column_name,
						   data_type,
						   column_type,
						   is_nullable,
						   column_default,
						   column_key,
						   extra,
						   character_maximum_length,
						   numeric_precision
					FROM   information_schema.columns
					WHERE  table_schema = %s
					  AND  table_name   = %s
					ORDER BY ordinal_position
				',
				[
					$wpdb->dbname,
					$this->table_name,
				]
			);

			return $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
		}

		/**
		 * Get database index info
		 *
		 * @return array Index info
		 *
		 * @since   2.0.13
		 */
		protected function get_table_indexes() {
			global $wpdb;

			$query = $wpdb->prepare(
				"
					SELECT index_name,
						   column_name,
						   non_unique
					FROM   information_schema.statistics
					WHERE  table_schema = %s
					  AND  table_name   = %s
					  AND  index_name   != 'PRIMARY'
					ORDER BY index_name, column_name, seq_in_index
				",
				[
					$wpdb->dbname,
					$this->table_name,
				]
			);

			return $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
		}

	}

}
