<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Simple_Form
 */

namespace WPDataProjects\Simple_Form {

	use WPDataAccess\Simple_Form\WPDA_Simple_Form;
	use WPDataProjects\Parent_Child\WPDP_Child_Form;
	use WPDataProjects\Project\WPDP_Project_Design_Table_Model;

	/**
	 * Class WPDP_Simple_Form extends WPDA_Simple_Form
	 *
	 * Uses table options to hide items and add lookups to data entry form
	 *
	 * @see WPDA_Simple_Form
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Simple_Form extends WPDA_Simple_Form {

		/**
		 * Overwrites method prepare_items
		 *
		 * Uses table options to hide items and add lookups to data entry form
		 *
		 * @param bool $set_back_form_values
		 */
		protected function prepare_items( $set_back_form_values = false ) {
			parent::prepare_items( $set_back_form_values );

			foreach ( $this->wpda_list_columns->get_table_columns() as $columns ) {
				// Hide columns which have show attribute disabled.
				if ( isset ( $columns['show'] ) && ! $columns['show'] ) {
					foreach ( $this->form_items as $item ) {
						if ( $item->get_item_name() === $columns['column_name'] ) {
							$item->set_hide_item( true );
						}
					}
				}

				// Set default value if available
				if ( isset ( $columns['default'] ) && '' !== $columns['default'] ) {
					foreach ( $this->form_items as $item ) {
						if ( $item->get_item_name() === $columns['column_name'] ) {
							$item_default_value = $columns['default'];
							if ( '$$USER$$' === $item_default_value ) {
								$wp_user            = wp_get_current_user();
								$item_default_value = $wp_user->data->user_login;
							}
							$item->set_item_default_value( $item_default_value );
						}
					}
				}
			}

			// Check if there are any lookup items defined for this table.
			$lookup_column_name = [];
			$tableform          = WPDP_Project_Design_Table_Model::get_column_options( $this->table_name, 'tableform' );

			if ( null !== $tableform ) {
				foreach ( $tableform as $tableform_item ) {
					if ( isset( $tableform_item->lookup ) && false !== $tableform_item->lookup ) {
						$lookup_column_name[ $tableform_item->column_name ] = $tableform_item->lookup;
					}
				}
			}
			if ( sizeof( $lookup_column_name ) > 0 ) {
				// Process lookup items and create listboxes.
				$lookups       = [];
				$relationships = WPDP_Project_Design_Table_Model::get_column_options( $this->table_name, 'relationships' );

				if ( null !== $relationships ) {
					if ( isset( $relationships['relationships'] ) ) {
						foreach ( $relationships['relationships'] as $relationship ) {
							if ( isset( $relationship->relation_type ) && 'lookup' === $relationship->relation_type ) {
								array_push( $lookups, $relationship );
							}
						}
					}
				}

				foreach ( $this->form_items as $item ) {
					if ( isset( $lookup_column_name[ $item->get_item_name() ] ) ) {
						foreach ( $lookups as $lookup ) {
							// Lookups are always based on a single column. Use first element of array.
							$source_column_name = $lookup->source_column_name[0];
							if ( $source_column_name === $item->get_item_name() ) {
								$target_column_name = $lookup->target_column_name[0];
								$target_table_name  = $lookup->target_table_name;

								if ( isset( $lookup_column_name[ $source_column_name ] ) ) {
									$lookup_sql =
										"select `{$lookup_column_name[ $source_column_name ]}`, `$target_column_name` " .
										"from `$target_table_name` " .
										"order by `{$lookup_column_name[ $source_column_name ]}`, `$target_column_name`";

									global $wpdb;
									$rows = $wpdb->get_results( $lookup_sql, 'ARRAY_A' );

									$lov_values  = [];
									$lov_options = [];

									if ( isset( $relationships['table'] ) ) {
										foreach ( $relationships['table'] as $table_column ) {
											if ( isset( $table_column->column_name ) && isset( $table_column->mandatory ) ) {
												if ( $table_column->column_name === $source_column_name && 'No' === $table_column->mandatory ) {
													array_push( $lov_values, '' );
													array_push( $lov_options, '' );
												}
											}
										}
									}

									foreach ( $rows as $row ) {
										$lov_value = $row[ $lookup_column_name[ $source_column_name ] ] . ' (' . $row[ $target_column_name ] . ')';
										array_push( $lov_values, $lov_value );
										array_push( $lov_options, $row[ $target_column_name ] );
									}

									$item->set_data_type( 'enum' );
									$item->set_enum( $lov_values );
									$item->set_enum_options( $lov_options );
									$item->set_item_hide_icon( true );
								}
							}
						}
					}
				}
			}

		}

	}

}