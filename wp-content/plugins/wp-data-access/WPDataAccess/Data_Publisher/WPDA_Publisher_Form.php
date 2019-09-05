<?php

namespace WPDataAccess\Data_Publisher {

	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Lists;
	use WPDataAccess\Data_Dictionary\WPDA_List_Columns_Cache;
	use WPDataAccess\Data_Tables\WPDA_Data_Tables;
	use WPDataAccess\Simple_Form\WPDA_Simple_Form;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Publisher_Form extends WPDA_Simple_Form
	 *
	 * Data entry form which allows users to create, update and test publications. A publication consists of a database
	 * table, a number of columns and some options. A shortcode can be generated for a publication. The shortcode can
	 * be copied to the clipboard and from there pasted in a WordPress post or page. The shortcode is used to add a
	 * dynamic HTML table to a post or page that supports searching, pagination and sorting. Tables are created with
	 * jQuery DataTables.
	 *
	 * @author  Peter Schulz
	 * @since   2.0.15
	 */
	class WPDA_Publisher_Form extends WPDA_Simple_Form {

		/**
		 * WPDA_Publisher_Form constructor.
		 *
		 * @param string $schema_name Database schema name
		 * @param string $table_name Database table name
		 * @param object $wpda_list_columns Handle to instance of WPDA_List_Columns
		 * @param array  $args
		 */
		public function __construct( $schema_name, $table_name, &$wpda_list_columns, $args = [] ) {
			$this->check_table_type = false;

			parent::__construct( $schema_name, $table_name, $wpda_list_columns, $args );

			$this->title = __( 'Data Publisher', 'wp-data-access' );
		}

		/**
		 * Overwrites method add_buttons
		 */
		public function add_buttons() {
			$index       = $this->get_item_position( 'pub_id' );
			$pub_id_item = $this->form_items[ $index ];
			$pub_id      = $pub_id_item->get_item_value();
			?>
			<a href="javascript:void(0)" onclick="jQuery('#data_publisher_test_container_<?php echo esc_html( $pub_id); ?>').toggle()"
			   class="button"><?php echo __( 'Test Publication', 'wp-data-access' ); ?></a>
			<a href="javascript:void(0)"
			   onclick='prompt("<?php echo __( 'Publication Shortcode', 'wp-data-access' ); ?>", "[wpdataaccess pub_id=\"<?php echo $pub_id; ?>\"]")'
			   class="button"><?php echo __( 'Show Shortcode', 'wp-data-access' ); ?></a>
			<a href="javascript:void(0)" id="button-copy-to-clipboard"
			   class="button"><?php echo __( 'Copy Shortcode', 'wp-data-access' ); ?></a>
			<script language="JavaScript">
				jQuery(document).ready(function () {
					var text_to_clipboard = new ClipboardJS("#button-copy-to-clipboard", {
						text: function () {
							clipboard_text = "[wpdataaccess pub_id=\"<?php echo $pub_id; ?>\"]";
							return clipboard_text;
						}
					});
					text_to_clipboard.on('success', function (e) {
						alert('<?php echo __( 'Shortcode successfully copied to clipboard!' ); ?>');
					});
					text_to_clipboard.on('error', function (e) {
						console.log('<?php echo __( 'Could not copy shortcode to clipboard!' ); ?>');
					});
				});
			</script>
			<?php
		}

		/**
		 * Overwrites method prepare_items
		 *
		 * @param bool $set_back_form_values
		 */
		public function prepare_items( $set_back_form_values = false ) {
			parent::prepare_items( $set_back_form_values );

			// Rename labels
			$labels =
				[
					'pub_id'              => __( 'Pub ID', 'wp-data-accesss' ),
					'pub_name'            => __( 'Publication Name', 'wp-data-accesss' ),
					'pub_table_name'      => __( 'Table Name', 'wp-data-accesss' ),
					'pub_column_names'    => __( 'Column Names (* = all)', 'wp-data-accesss' ),
					'pub_responsive'      => __( 'Output', 'wp-data-accesss' ),
					'pub_responsive_cols' => __( 'Number Of Columns (responsive only)', 'wp-data-accesss' ),
					'pub_responsive_type' => __( 'Type (responsive only)', 'wp-data-accesss' ),
					'pub_responsive_icon' => __( 'Show Icon (responsive only)', 'wp-data-accesss' ),
				];
			$this->set_labels( $labels );

			// Hide column pub_format
			$hide = [ 'pub_format' ];
			$this->hide_items( $hide );

			// Check table access to prepare table listbox content
			$table_access = WPDA::get_option( WPDA::OPTION_FE_TABLE_ACCESS );
			switch ( $table_access ) {
				case 'show':
					$tables = $this->get_all_db_tables();
					break;
				case 'hide':
					$tables = $this->get_all_db_tables();
					// Remove WordPress tables from listbox content
					$tables_named = [];
					foreach ( $tables as $table ) {
						$tables_named[ $table ] = true;
					}
					global $wpdb;
					foreach ( $wpdb->tables( 'all', true ) as $wp_table ) {
						unset( $tables_named[ $wp_table ] );
					}
					$tables = [];
					foreach ( $tables_named as $key => $value ) {
						array_push( $tables, $key );
					}
					break;
				default:
					// Show only selected tables and views
					$tables = WPDA::get_option( WPDA::OPTION_FE_TABLE_ACCESS_SELECTED );
			}

			foreach ( $this->form_items as $form_item ) {
				// Prepare listbox for column pub_table_name
				if ( $form_item->get_item_name() === 'pub_table_name' ) {
					$form_item->set_data_type( 'enum' );
					$form_item->set_enum( $tables );
					$form_item->set_item_hide_icon( true );
				}
				// Prepare listbox for column pub_responsive
				if ( $form_item->get_item_name() === 'pub_responsive' ) {
					$form_item->set_enum( [ 'Responsive', 'Flat' ] );
					$form_item->set_enum_options( [ 'Yes', 'No' ] );
				}
				// Prepare selection for column pub_column_names
				if ( $form_item->get_item_name() === 'pub_column_names' ) {
					$form_item->set_item_hide_icon( true );
					$form_item->set_item_js(
						'jQuery("#pub_column_names").parent().parent().find("td.icon").append("<a class=\'button\' href=\'javascript:void(0)\' onclick=\'select_columns()\'>' .
						__( 'Select', 'wp-data-access' ) .
						'</a>")'
					);
				}
			}
		}

		/**
		 * Get all db tables and views
		 *
		 * @return array
		 */
		protected function get_all_db_tables() {
			$tables    = [];
			$db_tables = WPDA_Dictionary_Lists::get_tables(); // select all db tables and views
			foreach ( $db_tables as $db_table ) {
				array_push( $tables, $db_table['table_name'] ); // add table or view to array
			}

			return $tables;
		}

		/**
		 * Overwrites method show
		 *
		 * @param bool   $allow_save
		 * @param string $add_param
		 */
		public function show( $allow_save = true, $add_param = '' ) {
			parent::show( $allow_save, $add_param );

			$index       = $this->get_item_position( 'pub_id' );
			$pub_id_item = $this->form_items[ $index ];
			$pub_id      = $pub_id_item->get_item_value();

			$index           = $this->get_item_position( 'pub_table_name' );
			$table_name_item = $this->form_items[ $index ];
			$table_name      = $table_name_item->get_item_value();

			$table_columns = WPDA_List_Columns_Cache::get_list_columns( '', $table_name );
			$columns       = [];
			foreach ( $table_columns->get_table_columns() as $table_column ) {
				array_push( $columns, $table_column['column_name'] );
			}
			?>
			<script language="JavaScript">
				const no_cols_selected = 'no column(s) selected';

				table_columns = [];
				<?php
				foreach ( $columns as $column ) {
				?>
				table_columns.push('<?php echo $column; ?>');
				<?php
				}
				?>
				function select_available(e) {
					var option = jQuery("#columns_available option:selected");
					var add_to = jQuery("#columns_selected");

					option.remove();
					new_option = add_to.append(option);

					if (jQuery("#columns_selected option[value='']").length > 0) {
						// Remove ALL from selected list.
						jQuery("#columns_selected option[value='']").remove();
					}

					jQuery('select#columns_selected option').removeAttr("selected");
				}

				function select_selected(e) {
					var option = jQuery("#columns_selected option:selected");
					if (option[0].value === '') {
						// Cannot remove ALL.
						return;
					}

					var add_to = jQuery("#columns_available");

					option.remove();
					add_to.append(option);

					if (jQuery('select#columns_selected option').length === 0) {
						jQuery("#columns_selected").append(jQuery('<option></option>').attr('value', '').text(no_cols_selected));
					}

					jQuery('select#columns_available option').removeAttr("selected");
				}

				function select_columns(e) {
					var columns_available = jQuery(
						'<select id="columns_available" name="columns_available[]" multiple size="8" style="width:200px" onclick="select_available()">' +
						'</select>'
					);
					jQuery.each(table_columns, function (i, val) {
						columns_available.append(jQuery('<option></option>').attr('value', val).text(val));
					});

					var columns_selected = jQuery(
						'<select id="columns_selected" name="columns_selected[]" multiple size="8" style="width:200px" onclick="select_selected()">' +
						'<option value="">' + no_cols_selected + '</option>' +
						'</select>'
					);

					var dialog_table = jQuery('<table style="width:410px"></table>');

					var dialog_table_row_available = dialog_table.append(jQuery('<tr></tr>').append(jQuery('<td width="50%"></td>')));
					dialog_table_row_available.append(columns_available);

					var dialog_table_row_selected = dialog_table.append(jQuery('<tr></tr>').append(jQuery('<td width="50%"></td>')));
					dialog_table_row_selected.append(columns_selected);

					var dialog_text = jQuery('<div style="width:410px"></div>');
					var dialog = jQuery('<div></div>');

					dialog.append(dialog_text);
					dialog.append(dialog_table);

					jQuery(dialog).dialog(
						{
							dialogClass: 'wp-dialog no-close',
							title: 'Add column(s) to index',
							modal: true,
							autoOpen: true,
							closeOnEscape: false,
							resizable: false,
							width: 'auto',
							buttons: {
								"OK": function () {
									var selected_columns = '';
									jQuery("#columns_selected option").each(
										function () {
											selected_columns += jQuery(this).val() + ',';
										}
									);
									if (selected_columns !== '') {
										selected_columns = selected_columns.slice(0, -1);
									}
									jQuery('#pub_column_names').val(selected_columns);

									jQuery(this).dialog('destroy').remove();
								},
								"Cancel": function () {
									jQuery(this).dialog('destroy').remove();
								}
							}
						}
					);
					jQuery(".ui-button-icon-only").hide();
				}
			</script>
			<?php
			self::show_publication( $pub_id, $table_name );
		}

		public static function show_publication( $pub_id, $table_name ) {
			$datatables_enabled            = WPDA::get_option( WPDA::OPTION_BE_LOAD_DATATABLES ) === 'on';
			$datatables_responsive_enabled = WPDA::get_option( WPDA::OPTION_BE_LOAD_DATATABLES_RESPONSE ) === 'on';

			if ( ! $datatables_enabled || ! $datatables_responsive_enabled ) {
				$publication =
					'<strong>' . __( 'ERROR: Cannot test publication', 'wp-data-access' ) . '</strong><br/><br/>' .
					__( 'SOLUTION: Load jQuery DataTables: WP Data Access > Manage Plugin > Back-End Settings', 'wp-data-access' );
			} else {
				$wpda_data_tables = new WPDA_Data_Tables();
				$publication      = $wpda_data_tables->show( $pub_id, '', '', '', '', '', '' );
			}
			?>
			<div id="data_publisher_test_container_<?php echo esc_html( $pub_id); ?>">
				<style>
					#data_publisher_test_header_<?php echo esc_html( $pub_id); ?> {
						background-color: #ccc;
						padding: 10px;
						margin-bottom: 10px;
					}

					#data_publisher_test_container_<?php echo esc_html( $pub_id); ?> {
						display: none;
						padding: 10px;
						position: relative;
						top: 50%;
						left: 50%;
						transform: translate(-50%, -50%);
						-ms-transform: translate(-50%, -50%);
						color: black;
						overflow-y: auto;
						background-color: white;
						border: 1px solid #ccc;
						width: max-content;
					}
				</style>
				<div id="data_publisher_test_header_<?php echo esc_html( $pub_id); ?>">
					<span><strong><?php echo __( 'Test Publication', 'wp-data-access' ); ?> (pub_id=<?php echo $pub_id; ?>)</strong></span>
					<span class="button" style="float:right;"
						  onclick="jQuery('#data_publisher_test_container_<?php echo esc_html( $pub_id); ?>').hide()">x</span><br/>
					<?php echo __( 'Publication might look different on your website', 'wp-data-access' ); ?>
				</div>
				<?php echo $publication; ?>
			</div>
			<script language="JavaScript">
				jQuery("#data_publisher_test_container_<?php echo esc_html( $pub_id); ?>").appendTo("#wpbody-content");
			</script>
			<?php
		}
	}

}