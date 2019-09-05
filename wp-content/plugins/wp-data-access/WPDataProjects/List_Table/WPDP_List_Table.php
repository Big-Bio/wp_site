<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\List_Table
 */

namespace WPDataProjects\List_Table {

	/**
	 * Class WPDP_List_Table
	 *
	 * Overwrites WPDP_List_Table_Lookup. Disables insert, update, delete and import depending on given arguments.
	 *
	 * @see WPDP_List_Table_Lookup
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_List_Table extends WPDP_List_Table_Lookup {

		/**
		 * WPDP_List_Table constructor
		 *
		 * @param array $args See WPDA_List_Table for argument list
		 */
		public function __construct( array $args = [] ) {
			if ( isset( $args['mode'] ) && 'edit' !== $args['mode'] ) {
				$args['allow_insert'] = 'off';
				$args['allow_update'] = 'off';
				$args['allow_delete'] = 'off';
				$args['allow_import'] = 'off';
			}

			if ( isset( $args['where_clause'] ) && '' !== $args['where_clause'] ) {
				$this->where = $args['where_clause'];
			}

			parent::__construct( $args );
		}

	}

}