<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Utilities
 */

namespace WPDataAccess\Utilities {

	use WPDataAccess\WPDA;
	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;

	/**
	 * Class WPDA_Table_Actions
	 *
	 * @author  Peter Schulz
	 * @since   2.0.13
	 */
	class WPDA_Table_Actions {

		/**
		 * Database schema name
		 *
		 * @var string
		 */
		protected $schema_name;

		/**
		 * Database table name
		 *
		 * @var string
		 */
		protected $table_name;

		/**
		 * Database table structure
		 *
		 * @var array
		 */
		protected $table_structure;

		/**
		 * Original create table statement
		 *
		 * @var string
		 */
		protected $create_table_stmt_orig;

		/**
		 * Reformatted create table statement
		 *
		 * @var string
		 */
		protected $create_table_stmt;

		/**
		 * Database indexes
		 *
		 * @var array
		 */
		protected $indexes;

		/**
		 * Indicates if table is a WordPress table
		 *
		 * @var boolean
		 */
		protected $is_wp_table;

		/**
		 * Possible values: Table and View
		 *
		 * @var string
		 */
		protected $dbo_type;

		/**
		 * Shows the specifications for the specified table or view
		 *
		 * There are four tabs provided:
		 *
		 * TAB Actions
		 * Provides actions for the given table or view, like export, rename, copy, drop, alter, and so on. A button
		 * is provided for every possible action. For some actions additional info can be provided through input fields
		 * like the type of download for an export. Not all buttons are available for all tables and views. WordPress
		 * tables for example cannot be dropped. Views for example can not be truncated. Which buttons are provided
		 * depends on the table or view.
		 *
		 * TAB Structure
		 * Shows the columns and their attributes.
		 *
		 * TAB Indexes
		 * Shows the indexes for the specified table. Not available for views.
		 *
		 * TAB SQL
		 * Shows the create table or views statement for the given table of view. A button is provided to copy
		 * this statement to the clipboard.
		 *
		 * @since   2.0.13
		 */
		public function show() {
			if ( ! isset( $_REQUEST['table_name'] ) || ! isset( $_REQUEST['schema_name'] ) ) {
				wp_die( __( 'ERROR: Wrong arguments', 'wp-data-access' ) );
			} else {
				$this->schema_name = sanitize_text_field( wp_unslash( $_REQUEST['schema_name'] ) ); // input var okay.
				$this->table_name  = sanitize_text_field( wp_unslash( $_REQUEST['table_name'] ) ); // input var okay.

				$wpda_data_dictionary = new WPDA_Dictionary_Exist( $this->schema_name, $this->table_name );
				if ( ! $wpda_data_dictionary->table_exists() ) {
					echo '<div>' . __( 'ERROR: Invalid table name or not authorized', 'wp-data-access' ) . '</div>';

					return;
				}

				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '?'; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, "wpda-actions-{$this->table_name}" ) ) {
					echo '<div>' . __( 'ERROR: Not authorized', 'wp-data-access' ) . '</div>';

					return;
				}

				$this->dbo_type = isset( $_REQUEST['dbo_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['dbo_type'] ) ) : null; // input var okay.

				$this->is_wp_table = WPDA::is_wp_table( $this->table_name );

				global $wpdb;
				$query                 = "show full columns from `{$this->schema_name}`.`{$this->table_name}`";
				$this->table_structure = $wpdb->get_results( $query, 'ARRAY_A' );

				if ( strpos( strtoupper( $this->dbo_type ), 'TABLE' ) !== false ) {
					$this->dbo_type = 'Table';
					$query          = "show create table `{$this->schema_name}`.`{$this->table_name}`";
					$create_table   = $wpdb->get_results( $query, 'ARRAY_A' );
					if ( isset( $create_table[0]['Create Table'] ) ) {
						$this->create_table_stmt_orig = $create_table[0]['Create Table'];
						$this->create_table_stmt      = preg_replace( "/\(/", "<br/>(", $this->create_table_stmt_orig, 1 );
						$this->create_table_stmt      = preg_replace( '/\,\s\s\s/', '<br/>,   ', $this->create_table_stmt );
						$pos                          = strrpos( $this->create_table_stmt, ')' );
						if ( false !== $pos ) {
							$this->create_table_stmt =
								substr( $this->create_table_stmt, 0, $pos - 1 ) .
								"<br/>)" .
								substr( $this->create_table_stmt, $pos + 1 );
						}

						$query         = "show indexes from `{$this->schema_name}`.`{$this->table_name}`";
						$this->indexes = $wpdb->get_results( $query, 'ARRAY_A' );
					} else {
						$this->create_table_stmt = __( 'Error reading create table statement', 'wp-data-access' );
					}
				} elseif ( strtoupper( $this->dbo_type ) === 'VIEW' ) {
					$this->dbo_type = 'View';
					$query          = "show create view `{$this->schema_name}`.`{$this->table_name}`";
					$create_table   = $wpdb->get_results( $query, 'ARRAY_A' );
					if ( isset( $create_table[0]['Create View'] ) ) {
						$this->create_table_stmt_orig = $create_table[0]['Create View'];
						$this->create_table_stmt      = str_replace( "AS select", "AS<br/>select", $this->create_table_stmt_orig );
						$this->create_table_stmt      = str_replace( "from", "<br/>from", $this->create_table_stmt );
					}
				} else {
					$this->dbo_type = '';
				}
				?>
				<div id="<?php echo esc_attr( $this->table_name ); ?>-tabs" style="border:1px solid #e5e5e5;">
					<div class="nav-tab-wrapper">
						<?php
						if ( '' !== $this->dbo_type ) {
							echo '<a id="' . esc_attr( $this->table_name ) . '-sel-1" class="nav-tab nav-tab-active' .
							     '" href="javascript:void(0)" onclick="settab(\'' . esc_attr( $this->table_name ) . '\', \'1\');" 
								style="font-size:inherit;">' .
							     __( 'Actions', 'wp-data-access' ) .
							     '</a>';
						}
						echo '<a id="' . esc_attr( $this->table_name ) . '-sel-2" class="nav-tab' .
						     '" href="javascript:void(0)" onclick="settab(\'' . esc_attr( $this->table_name ) . '\', \'2\');" 
							style="font-size:inherit;">' .
						     __( 'Structure', 'wp-data-access' ) .
						     '</a>';
						if ( 'Table' === $this->dbo_type ) {
							echo '<a id="' . esc_attr( $this->table_name ) . '-sel-3" class="nav-tab' .
							     '" href="javascript:void(0)" onclick="settab(\'' . esc_attr( $this->table_name ) . '\', \'3\');" 
								style="font-size:inherit;">' .
							     __( 'Indexes', 'wp-data-access' ) .
							     '</a>';
						}
						if ( '' !== $this->dbo_type ) {
							echo '<a id="' . esc_attr( $this->table_name ) . '-sel-4" class="nav-tab' .
							     '" href="javascript:void(0)" onclick="settab(\'' . esc_attr( $this->table_name ) . '\', \'4\');" 
								style="font-size:inherit;">' .
							     __( 'SQL', 'wp-data-access' ) .
							     '</a>';
						}
						?>
					</div>
					<?php
					if ( '' !== $this->dbo_type ) {
						?>
						<div id="<?php echo esc_attr( $this->table_name ); ?>-tab-1" style="padding:3px;">
							<?php $this->tab_actions(); ?>
						</div>
						<?php
					}
					?>
					<div id="<?php echo esc_attr( $this->table_name ); ?>-tab-2" style="padding:3px;display:none;">
						<?php $this->tab_structure(); ?>
					</div>
					<?php
					if ( 'Table' === $this->dbo_type ) {
						?>
						<div id="<?php echo esc_attr( $this->table_name ); ?>-tab-3" style="padding:3px;display:none;">
							<?php $this->tab_index(); ?>
						</div>
						<?php
					}
					if ( '' !== $this->dbo_type ) {
						?>
						<div id="<?php echo esc_attr( $this->table_name ); ?>-tab-4" style="padding:3px;display:none;">
							<?php $this->tab_sql(); ?>
						</div>
						<?php
					}
					?>
				</div>
				<script language="JavaScript">
					function settab(table_name, tab) {
						for (i = 1; i <= 4; i++) {
							jQuery("#" + table_name + "-sel-" + i.toString()).removeClass('nav-tab-active');
							jQuery("#" + table_name + "-tab-" + i.toString()).hide();
						}
						jQuery("#" + table_name + "-sel-" + tab).addClass('nav-tab-active');
						jQuery("#" + table_name + "-tab-" + tab).show();
					}

					jQuery(document).ready(function () {
						var sql_to_clipboard = new ClipboardJS("#button-copy-clipboard-<?php echo esc_attr( $this->table_name ); ?>");
						sql_to_clipboard.on('success', function (e) {
							alert('<?php echo __( 'SQL successfully copied to clipboard!', 'wp-data-access' ); ?>');
						});
						sql_to_clipboard.on('error', function (e) {
							alert('<?php echo __( 'Could not copy SQL to clipboard!', 'wp-data-access' ); ?>');
						});
						jQuery("#rename-table-from-<?php echo esc_attr( $this->table_name ); ?>").on('keyup paste', function () {
							this.value = this.value.replace(/[^\w\$\_]/g, '');
						});
						jQuery("#copy-table-from-<?php echo esc_attr( $this->table_name ); ?>").on('keyup paste', function () {
							this.value = this.value.replace(/[^\w\$\_]/g, '');
						});
					});
				</script>
				<?php
			}
		}

		/**
		 * Provides content for tab Structure
		 */
		protected function tab_structure() {
			?>
			<table class="widefat striped rows wpda-structure-table">
				<tr>
					<th class="nobr"><strong><?php echo __( 'Column Name', 'wp-data-access' ); ?></strong></th>
					<th class="nobr"><strong><?php echo __( 'Data Type', 'wp-data-access' ); ?></strong></th>
					<th><strong><?php echo __( 'Collation', 'wp-data-access' ); ?></strong></th>
					<th><strong><?php echo __( 'Null?', 'wp-data-access' ); ?></strong></th>
					<th><strong><?php echo __( 'Key?', 'wp-data-access' ); ?></strong></th>
					<th class="nobr"><strong><?php echo __( 'Default Value', 'wp-data-access' ); ?></strong></th>
					<th style="width:80%;"><strong><?php echo __( 'Extra', 'wp-data-access' ); ?></strong></th>
				</tr>
				<?php
				foreach ( $this->table_structure as $column ) {
					?>
					<tr>
						<td class="nobr"><?php echo esc_attr( $column['Field'] ); ?></td>
						<td class="nobr"><?php echo esc_attr( $column['Type'] ); ?></td>
						<td class="nobr"><?php echo esc_attr( $column['Collation'] ); ?></td>
						<td class="nobr"><?php echo esc_attr( $column['Null'] ); ?></td>
						<td class="nobr"><?php echo esc_attr( $column['Key'] ); ?></td>
						<td class="nobr"><?php echo esc_attr( $column['Default'] ); ?></td>
						<td><?php echo esc_attr( $column['Extra'] ); ?></td>
					</tr>
					<?php
				}
				?>
			</table>
			<?php
		}

		/**
		 * Provides content for tab Indexes
		 */
		protected function tab_index() {
			?>
			<table class="widefat striped rows wpda-structure-table">
				<tr>
					<th class="nobr"><strong><?php echo __( 'Index Name', 'wp-data-access' ); ?></strong></th>
					<th><strong><?php echo __( 'Unique?', 'wp-data-access' ); ?></strong></th>
					<th><strong>#</strong></th>
					<th class="nobr"><strong><?php echo __( 'Column Name', 'wp-data-access' ); ?></strong></th>
					<th><strong><?php echo __( 'Collation', 'wp-data-access' ); ?></strong></th>
					<th class="nobr"><strong><?php echo __( 'Index Prefix?', 'wp-data-access' ); ?></strong></th>
					<th><strong><?php echo __( 'Null?', 'wp-data-access' ); ?></strong></th>
					<th class="nobr" style="width:80%;">
						<strong><?php echo __( 'Index Type', 'wp-data-access' ); ?></strong></th>
				</tr>
				<?php
				if ( 0 === count( $this->indexes ) ) {
					echo '<tr><td colspan="8">' . __( 'No indexes defined for this table', 'wp-data-access' ) . '</td></tr>';
				}
				$current_index_name = '';
				foreach ( $this->indexes as $index ) {
					if ( $current_index_name !== $index['Key_name'] ) {
						$current_index_name = esc_attr( $index['Key_name'] );
						$new_index          = true;
					} else {
						$new_index = false;
					}
					?>
					<tr>
						<td class="nobr">
							<?php if ( $new_index ) {
								echo esc_attr( $index['Key_name'] );
							} ?>
						</td>
						<td class="nobr">
							<?php if ( $new_index ) {
								echo '0' === $index['Non_unique'] ? 'Yes' : 'No';
							} ?>
						</td>
						<td class="nobr">
							<?php echo esc_attr( $index['Seq_in_index'] ); ?>
						</td>
						<td class="nobr">
							<?php echo esc_attr( $index['Column_name'] ); ?>
						</td>
						<td class="nobr">
							<?php echo 'A' === $index['Collation'] ? 'Ascending' : 'Not sorted'; ?>
						</td>
						<td class="nobr">
							<?php echo esc_attr( $index['Sub_part'] ); ?>
						</td>
						<td class="nobr">
							<?php echo '' === $index['Null'] ? 'NO' : esc_attr( $index['Null'] ); ?>
						</td>
						<td><?php echo esc_attr( $index['Index_type'] ); ?></td>
					</tr>
					<?php
				}
				?>
			</table>
			<?php
		}

		/**
		 * Provides content for tab SQL
		 */
		protected function tab_sql() {
			?>
			<table class="widefat striped rows wpda-structure-table">
				<tr>
					<td>
						<?php echo wp_kses( $this->create_table_stmt, [ 'br' => [] ] ); ?>
					</td>
					<td style="text-align: right;">
						<a id="button-copy-clipboard-<?php echo esc_attr( $this->table_name ); ?>"
						   href="javascript:void(0)"
						   class="button button-primary"
						   data-clipboard-text="<?php echo $this->create_table_stmt_orig; ?>"
						>
							<?php echo __( 'Copy to clipboard', 'wp-data-access' ); ?>
						</a>
					</td>
				</tr>
			</table>
			<?php
		}

		/**
		 * Provides content for tab Actions
		 */
		protected function tab_actions() {
			?>
			<table class="widefat striped rows wpda-structure-table">
				<?php
				if ( 'Table' === $this->dbo_type ) {
					$this->tab_export();
				}
				if ( $this->is_wp_table === false ) {
					$this->tab_rename();
				}
				if ( 'Table' === $this->dbo_type ) {
					$this->tab_copy();
				}
				if ( 'Table' === $this->dbo_type && $this->is_wp_table === false ) {
					$this->tab_truncate();
				}
				if ( $this->is_wp_table === false ) {
					$this->tab_drop();
				}
				if ( 'Table' === $this->dbo_type ) {
					$this->tab_optimize();
				}
				if ( 'Table' === $this->dbo_type ) {
					$this->tab_alter();
				}
				?>
			</table>
			<?php
		}

		/**
		 * Provides content for Export action
		 */
		protected function tab_export() {
			$check_export_access = 'true';
			if ( 'on' === WPDA::get_option( WPDA::OPTION_BE_CONFIRM_EXPORT ) ) {
				$check_export_access = "confirm('Export table $this->table_name?')";
			}
			$wp_nonce_action = 'wpda-export-*';
			$wp_nonce        = wp_create_nonce( $wp_nonce_action );
			$src             = "?action=wpda_export&type=table&schema_name={$this->schema_name}&table_names={$this->table_name}&_wpnonce=$wp_nonce&format_type=";

			global $wpdb;
			$export_variable_prefix = false;
			if ( strpos( $this->table_name, $wpdb->prefix ) === 0 ) {
				// Offer an extra SQL option: SQL (add variable WP prefix)
				$export_variable_prefix = true;
			}
			$export_variable_prefix_option = ( 'on' === WPDA::get_option( WPDA::OPTION_BE_EXPORT_VARIABLE_PREFIX ) );
			?>
			<tr>
				<td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
					<a href="javascript:void(0)"
					   class="button button-primary"
					   onclick="if (<?php echo esc_attr( $check_export_access ); ?>) jQuery('#stealth_mode').attr('src','<?php echo esc_attr( $src ); ?>' + jQuery('#format_type_<?php echo esc_attr( $this->table_name ); ?>').val())"
					   style="display:block;"
					>
						<?php echo __( 'EXPORT', 'wp-data-access' ); ?>
					</a>
				</td>
				<td style="vertical-align:middle;">
					<span><?php echo __( 'Export', 'wp-data-access' ); ?> <strong><?php echo __( 'table', 'wp-data-access' ); ?> `<?php echo esc_attr( $this->table_name ); ?>`</strong> <?php echo __( 'to', 'wp-data-access' ); ?>: </span>
					<select id="format_type_<?php echo esc_attr( $this->table_name ); ?>" name="format_type">
						<option value="sql" <?php echo $export_variable_prefix_option ? '' : 'selected'; ?>>SQL</option>
						<?php if ( $export_variable_prefix ) { ?>
							<option value="sqlpre" <?php echo $export_variable_prefix_option ? 'selected' : ''; ?>>
								<?php echo __( 'SQL (add variable WP prefix)', 'wp-data-access' ); ?>
							</option>
						<?php } ?>
						<option value="xml">XML</option>
						<option value="json">JSON</option>
						<option value="excel">Excel</option>
						<option value="csv">CSV</option>
					</select>
					<span> <?php echo __( '(file download)', 'wp-data-access' ); ?></span>
				</td>
			</tr>
			<?php
		}

		/**
		 * Provides content for Rename action
		 */
		protected function tab_rename() {
			$wp_nonce_action_rename = "wpda-rename-{$this->table_name}";
			$wp_nonce_rename        = wp_create_nonce( $wp_nonce_action_rename );
			$rename_table_form_id   = 'rename_table_form_' . esc_attr( $this->table_name );
			$rename_table_form      =
				"<form" .
				" id='" . $rename_table_form_id . "'" .
				" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_MAIN ) . "'" .
				" method='post'>" .
				"<input type='hidden' name='action' value='rename-table' />" .
				"<input type='hidden' name='rename_table_name_old' value='" . esc_attr( $this->table_name ) . "' />" .
				"<input type='hidden' name='rename_table_name_new' id='rename_table_name_" . esc_attr( $this->table_name ) . "' value='' />" .
				"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_rename ) . "' />" .
				"</form>";
			?>
			<tr>
				<td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
					<script language="JavaScript">
						jQuery("#wpda_invisible_container").append("<?php echo $rename_table_form; ?>");
					</script>
					<a href="javascript:void(0)"
					   class="button button-primary"
					   onclick="if (jQuery('#rename-table-from-<?php echo esc_attr( $this->table_name ); ?>').val()==='') { alert('<?php echo __( 'Please enter a valid table name', 'wp-data-access' ); ?>'); return false; } if (confirm('<?php echo __( 'Rename', 'wp-data-access' ) . ' ' . esc_attr( strtolower( $this->dbo_type ) ) . '?'; ?>')) { jQuery('#rename_table_name_<?php echo esc_attr( $this->table_name ); ?>').val(jQuery('#rename-table-from-<?php echo esc_attr( $this->table_name ); ?>').val()); jQuery('#<?php echo $rename_table_form_id; ?>').submit(); }"
					   style="display:block;"
					>
						<?php echo __( 'RENAME', 'wp-data-access' ); ?>
					</a>
				</td>
				<td style="vertical-align:middle;">
					<?php echo __( 'Rename', 'wp-data-access' ); ?>
					<strong><?php echo esc_attr( strtolower( $this->dbo_type ) ); ?>
						`<?php echo esc_attr( $this->table_name ); ?>`</strong> to:
					<input type="text" id="rename-table-from-<?php echo esc_attr( $this->table_name ); ?>" value="">
				</td>
			</tr>
			<?php
		}

		/**
		 * Provides content for Copy action
		 */
		protected function tab_copy() {
			$wp_nonce_action_copy = "wpda-copy-{$this->table_name}";
			$wp_nonce_copy        = wp_create_nonce( $wp_nonce_action_copy );
			$copy_table_form_id   = 'copy_table_form_' . esc_attr( $this->table_name );
			$copy_table_form      =
				"<form" .
				" id='$copy_table_form_id'" .
				" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_MAIN ) . "'" .
				" method='post'>" .
				"<input type='hidden' name='action' value='copy-table' />" .
				"<input type='hidden' name='copy_table_name_src' value='" . esc_attr( $this->table_name ) . "' />" .
				"<input type='hidden' name='copy_table_name_dst' id='copy_table_name_" . esc_attr( $this->table_name ) . "' value='' />" .
				"<input type='checkbox' name='copy-table-data' id='copy_table_data_" . esc_attr( $this->table_name ) . "' checked />" .
				"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_copy ) . "' />" .
				"</form>";
			?>
			<tr>
				<td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
					<script language="JavaScript">
						jQuery("#wpda_invisible_container").append("<?php echo $copy_table_form; ?>");
					</script>
					<a href="javascript:void(0)"
					   class="button button-primary"
					   onclick="if (jQuery('#copy-table-from-<?php echo esc_attr( $this->table_name ); ?>').val()==='') { alert('<?php echo __( 'Please enter a valid table name', 'wp-data-access' ); ?>'); return false; } if (confirm('<?php echo __( 'Copy', 'wp-data-access' ) . ' ' . esc_attr( strtolower( $this->dbo_type ) ) . '?'; ?>')) { jQuery('#copy_table_name_<?php echo esc_attr( $this->table_name ); ?>').val(jQuery('#copy-table-from-<?php echo esc_attr( $this->table_name ); ?>').val()); jQuery('#<?php echo $copy_table_form_id; ?>').submit(); }"
					   style="display:block;"
					>
						<?php echo __( 'COPY', 'wp-data-access' ); ?>
					</a>
				</td>
				<td style="vertical-align:middle;">
					<?php echo __( 'Copy', 'wp-data-access' ); ?>
					<strong><?php echo esc_attr( strtolower( $this->dbo_type ) ); ?>
						`<?php echo esc_attr( $this->table_name ); ?>
						`</strong> <?php echo __( 'to', 'wp-data-access' ); ?>:
					<input type="text" id="copy-table-from-<?php echo esc_attr( $this->table_name ); ?>" value="">
					<label style="vertical-align:baseline"><input type="checkbox" checked
																  onclick="jQuery('#copy_table_data_<?php echo esc_attr( $this->table_name ); ?>').prop('checked', jQuery(this).is(':checked'));"><?php echo __( 'Copy data', 'wp-data-access' ); ?>
					</label>
				</td>
			</tr>
			<?php
		}

		/**
		 * Provides content for Truncate action
		 */
		protected function tab_truncate() {
			$wp_nonce_action_truncate = "wpda-truncate-{$this->table_name}";
			$wp_nonce_truncate        = wp_create_nonce( $wp_nonce_action_truncate );
			$truncate_table_form_id   = 'truncate_table_form_' . esc_attr( $this->table_name );
			$truncate_table_form      =
				"<form" .
				" id='$truncate_table_form_id'" .
				" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_MAIN ) . "'" .
				" method='post'>" .
				"<input type='hidden' name='action' value='truncate' />" .
				"<input type='hidden' name='truncate_table_name' value='" . esc_attr( $this->table_name ) . "' />" .
				"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_truncate ) . "' />" .
				"</form>";
			?>
			<tr>
				<td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
					<script language="JavaScript">
						jQuery("#wpda_invisible_container").append("<?php echo $truncate_table_form; ?>");
					</script>
					<a href="javascript:void(0)"
					   class="button button-primary"
					   onclick="if (confirm('<?php echo __( 'Truncate table?', 'wp-data-access' ); ?>')) { jQuery('#<?php echo $truncate_table_form_id; ?>').submit(); }"
					   style="display:block;"
					>
						<?php echo __( 'TRUNCATE', 'wp-data-access' ); ?>
					</a>
				</td>
				<td style="vertical-align:middle;">
					<?php echo __( 'Permanently delete all data from', 'wp-data-access' ); ?>
					<strong><?php echo esc_attr( strtolower( $this->dbo_type ) ); ?>
						`<?php echo esc_attr( $this->table_name ); ?>`</strong>
					.<br/>
					<strong><?php echo __( 'This action cannot be undone!', 'wp-data-access' ); ?></strong>
				</td>
			</tr>
			<?php
		}

		/**
		 * Provides content for Drop action
		 */
		protected function tab_drop() {
			$wp_nonce_action_drop = "wpda-drop-{$this->table_name}";
			$wp_nonce_drop        = wp_create_nonce( $wp_nonce_action_drop );
			if ( 'View' === $this->dbo_type ) {
				$msg_drop = __( 'Drop view?', 'wp-data-access' );
			} else {
				$msg_drop = __( 'Drop table?', 'wp-data-access' );
			}
			$drop_table_form_id = 'drop_table_form_' . esc_attr( $this->table_name );
			$drop_table_form    =
				"<form" .
				" id='$drop_table_form_id'" .
				" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_MAIN ) . "'" .
				" method='post'>" .
				"<input type='hidden' name='action' value='drop' />" .
				"<input type='hidden' name='drop_table_name' value='" . esc_attr( $this->table_name ) . "' />" .
				"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_drop ) . "' />" .
				"</form>";
			?>
			<tr>
				<td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
					<script language="JavaScript">
						jQuery("#wpda_invisible_container").append("<?php echo $drop_table_form; ?>");
					</script>
					<a href="javascript:void(0)"
					   class="button button-primary"
					   onclick="if (confirm('<?php echo $msg_drop; ?>')) { jQuery('#<?php echo $drop_table_form_id; ?>').submit(); }"
					   style="display:block;"
					>
						<?php echo __( '', 'wp-data-access' ); ?>DROP
					</a>
				</td>
				<td style="vertical-align:middle;">
					<?php echo __( 'Permanently delete', 'wp-data-access' ); ?>
					<strong><?php echo esc_attr( strtolower( $this->dbo_type ) ); ?>
						`<?php echo esc_attr( $this->table_name ); ?>`</strong>
					<?php echo __( 'and all table data from the database.', 'wp-data-access' ); ?><br/>
					<strong><?php echo __( 'This action cannot be undone!', 'wp-data-access' ); ?></strong>
				</td>
			</tr>
			<?php
		}

		/**
		 * Provides content for Optimize action
		 *
		 * Data_length
		 * Index_length
		 * Data_free
		 */
		protected function tab_optimize() {
			global $wpdb;

			$table_structure             = $wpdb->get_row( $wpdb->prepare( 'show table status like %s', $this->table_name ) );
			$query_innodb_file_per_table = $wpdb->get_row( "show session variables like 'innodb_file_per_table'" );

			if ( ! empty( $query_innodb_file_per_table ) ) {
				$innodb_file_per_table = ( 'ON' === $query_innodb_file_per_table->Value );
			} else {
				$innodb_file_per_table = true;
			}

			if ( 'InnoDB' === $table_structure->Engine && ! $innodb_file_per_table ) {
				return;
			}

			$consider_optimize =
				$table_structure->Data_free > 0 && $table_structure->Data_length > 0 &&
				( $table_structure->Data_free / $table_structure->Data_length > 0.2 );

			$wp_nonce_action_optimize = "wpda-optimize-{$this->table_name}";
			$wp_nonce_optimize        = wp_create_nonce( $wp_nonce_action_optimize );
			$optimize_table_form_id   = 'optimize_table_form_' . esc_attr( $this->table_name );
			$optimize_table_form      =
				"<form" .
				" id='$optimize_table_form_id'" .
				" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_MAIN ) . "'" .
				" method='post'>" .
				"<input type='hidden' name='action' value='optimize-table' />" .
				"<input type='hidden' name='optimize_table_name' value='" . esc_attr( $this->table_name ) . "' />" .
				"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_optimize ) . "' />" .
				"</form>";
			$msg_optimize             = __( 'Optimize table?', 'wp-data-access' );
			?>
			<tr>
				<td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
					<script language="JavaScript">
						jQuery("#wpda_invisible_container").append("<?php echo $optimize_table_form; ?>");
					</script>
					<a href="javascript:void(0)"
					   class="button button-primary"
					   onclick="if (confirm('<?php echo $msg_optimize; ?>')) { jQuery('#<?php echo $optimize_table_form_id; ?>').submit(); }"
					   style="display:block;<?php if ( ! $consider_optimize ) {
						   echo 'opacity:0.5;';
					   } ?>"
					>
						<?php echo __( 'OPTIMIZE', 'wp-data-access' ); ?>
					</a>
				</td>
				<td style="vertical-align:middle;<?php if ( ! $consider_optimize ) {
					echo 'opacity:0.5;';
				} ?>">
					<?php echo __( 'Optimize', 'wp-data-access' ); ?>
					<strong><?php echo esc_attr( strtolower( $this->dbo_type ) ); ?>
						`<?php echo esc_attr( $this->table_name ); ?>`</strong>.<br/>
					<?php
					if ( $consider_optimize ) {
						?>
						<strong><?php echo __( 'MySQL locks the table during the time OPTIMIZE TABLE is running!', 'wp-data-access' ); ?></strong>
						<?php
					} else {
						?>
						<strong><?php echo __( 'Table optimization not considered useful! But you can...', 'wp-data-access' ); ?></strong>
						<?php
					}
					?>
				</td>
			</tr>
			<?php
		}

		/**
		 * Provides content for Alter action
		 */
		protected function tab_alter() {
			$wp_nonce_action_alter = "wpda-alter-{$this->table_name}";
			$wp_nonce_alter        = wp_create_nonce( $wp_nonce_action_alter );
			$alter_table_form_id   = 'alter_table_form_' . esc_attr( $this->table_name );
			$alter_table_form      =
				"<form" .
				" id='$alter_table_form_id'" .
				" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_DESIGNER ) . "'" .
				" method='post'>" .
				"<input type='hidden' name='action' value='edit' />" .
				"<input type='hidden' name='action2' value='init' />" .
				"<input type='hidden' name='wpda_table_name' value='" . esc_attr( $this->table_name ) . "' />" .
				"<input type='hidden' name='wpda_table_name_re' value='" . esc_attr( $this->table_name ) . "' />" .
				"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_alter ) . "' />" .
				"<input type='hidden' name='page_number' value='1' />" .
				"<input type='hidden' name='caller' value='dataexplorer' />" .
				"</form>";
			?>
			<tr>
				<td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
					<script language="JavaScript">
						jQuery("#wpda_invisible_container").append("<?php echo $alter_table_form; ?>");
					</script>
					<a href="javascript:void(0)"
					   class="button button-primary"
					   onclick="if (confirm('<?php echo __( 'Alter table?', 'wp-data-access' ); ?>')) { jQuery('#<?php echo $alter_table_form_id; ?>').submit(); }"
					   style="display:block;"
					>
						<?php echo __( 'ALTER', 'wp-data-access' ); ?>
					</a>
				</td>
				<td style="vertical-align:middle;">
					<?php echo __( 'Loads', 'wp-data-access' ); ?>
					<strong><?php echo esc_attr( strtolower( $this->dbo_type ) ); ?>
						`<?php echo esc_attr( $this->table_name ); ?>`</strong>
					<?php echo __( 'into the Data Designer.', 'wp-data-access' ); ?>
				</td>
			</tr>
			<?php
		}

	}

}
