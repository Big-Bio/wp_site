<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\User_Menu
 */

namespace WPDataAccess\Data_Publisher {

	use WPDataAccess\List_Table\WPDA_List_Table;

	/**
	 * Class WPDA_Publisher_List_Table extends WPDA_List_Table
	 *
	 * List table to support Data Publications.
	 *
	 * @author  Peter Schulz
	 * @since   2.0.15
	 */
	class WPDA_Publisher_List_Table extends WPDA_List_Table {

		public function __construct( $args = [] ) {
			// Add column labels.
			$args['column_headers'] = [
				'pub_id'           => __( 'Pub ID', 'wp-data-access' ),
				'pub_name'         => __( 'Publication Name', 'wp-data-access' ),
				'pub_table_name'   => __( 'Table Name', 'wp-data-access' ),
				'pub_column_names' => __( 'Column names', 'wp-data-access' ),
				'pub_responsive'   => __( 'Output', 'wp-data-access' ),

			];

			$args['title']    = __( 'Data Publisher', 'wp-data-access' );
			$args['subtitle'] = '';

			parent::__construct( $args );
		}

		/**
		 * Overwrite method column_default
		 *
		 * Column pub_responsive should return 'Flat' or 'Responsive'.
		 */
		public function column_default( $item, $column_name ) {
			if ( 'pub_responsive' === $column_name ) {
				if ( 'Yes' === $item[ $column_name ] ) {
					return 'Responsive';
				} else {
					return 'Flat';
				}
			}

			return parent::column_default( $item, $column_name );
		}

		/**
		 * Overwrites method column_default_add_action
		 *
		 * Add a link to show the shortcode of a publication.
		 *
		 * @param array  $item
		 * @param string $column_name
		 * @param array  $actions
		 */
		public function column_default_add_action( $item, $column_name, &$actions ) {
			parent::column_default_add_action( $item, $column_name, $actions );

			if ( 'pub_id' === $column_name ) {
				WPDA_Publisher_Form::show_publication( $item['pub_id'], $item['pub_table_name'] );
			}
			$actions['test'] = sprintf(
				'<a href="javascript:void(0)" 
                                    class="view"  
                                    onclick="jQuery(\'#data_publisher_test_container_%s\').toggle()">
                                    %s
                                </a>
                                ',
				$item['pub_id'],
				__( 'Test', 'wp-data-access' )
			);

			$actions['shortcode'] = sprintf(
				'<a href="javascript:void(0)" 
                                    class="view"  
                                    onclick=\'prompt("%s", "[wpdataaccess pub_id=\"%s\"]")\'>
                                    %s
                                </a>
                                ',
				__( 'Publication Shortcode', 'wp-data-access' ),
				$item['pub_id'],
				__( 'Show Shortcode', 'wp-data-access' )
			);
		}

		/**
		 * Define hidden columns
		 *
		 * @return array
		 */
		public function get_hidden_columns() {

			return [
				'pub_responsive_cols',
				'pub_responsive_type',
				'pub_responsive_icon',
				'pub_format',
			];

		}

	}

}