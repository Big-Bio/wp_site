<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Settings
 */

namespace WPDataAccess\Settings {

	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Lists;
	use WPDataAccess\Data_Publisher\WPDA_Publisher_Model;
	use WPDataAccess\Design_Table\WPDA_Design_Table_Model;
	use WPDataAccess\User_Menu\WPDA_User_Menu_Model;
	use WPDataAccess\Utilities\WPDA_Import;
	use WPDataAccess\Utilities\WPDA_Message_Box;
	use WPDataAccess\Utilities\WPDA_Repository;
	use WPDataAccess\WPDA;
	use WPDataProjects\Project\WPDP_Project_Design_Table_Model;

	/**
	 * Class WPDA_Settings
	 *
	 * The following plugin settings are supported through this class, each having its own tab:
	 * + Back-end Settings
	 * + Front-end Settings
	 * + Data Publisher Settings
	 * + Data Backup Settings
	 * + Uninstall Settings
	 * + Manage Repository
	 *
	 * All tabs have the following similar structure:
	 * + If form was posted save options (show success or error message)
	 * + Read options
	 * + Show form with options for selected tab
	 *
	 * Tabs Back-end Settings, Front-end Settings, Data Backup Settings and Uninstall Settings have reset buttons. When
	 * the reset button on a specific tab is clicked, the default values for the settings on that tab are taken from
	 * WPDA and stored in $pwdb->options.
	 *
	 * Tab Manage Repository import and export functionality for menu items that are saved in plugin table
	 * wp_wpda_menu_items. This is especially helpful if users like to transfer menu item settings from one WordPress
	 * installation to another. When the users clicks on tab Manage Repository, the repository is validated and the
	 * status of the repository is shown. If the repository has errors, a button is offered to recreate the
	 * repository.
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_Settings {

		const DROPBOX_CLIENT_ID     = 'rv5japeynhpzmyy';
		const DROPBOX_CLIENT_SECRET = 'v45glikrzr6h62z';

		/**
		 * Menu slug of the current page
		 *
		 * @var string
		 */
		protected $page;

		/**
		 * Available tabs on the page
		 *
		 * @var array
		 */
		protected $tabs;

		/**
		 * Current tab name
		 *
		 * @var string
		 */
		protected $current_tab;

		/**
		 * Reference to wpda import object
		 *
		 * @var WPDA_Import
		 */
		protected $wpda_import;

		/**
		 * WPDA_Settings constructor
		 *
		 * Member $this->tabs is filled in the constructor to support i18n.
		 *
		 * If a request was send for recreation of the repository, this is done in the constructor. This action must
		 * be performed before checking the user menu model, which is part of the constructor as well, necessary to
		 * inform the user if any errors were reported.
		 *
		 * @since   1.0.0
		 */
		public function __construct() {

			// Get menu slag of current page.
			if ( isset( $_REQUEST['page'] ) ) {
				$this->page = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ); // input var okay.
			} else {
				// In order to show a list table we need a page.
				wp_die( __( 'ERROR: Wrong arguments [missing page argument]', 'wp-data-access' ) );
			}

			// Tabs array is filled in constructor to add i18n.
			$this->tabs = [
				'backend'       => __( 'Back-end Settings', 'wp-data-access' ),
				'frontend'      => __( 'Front-end Settings', 'wp-data-access' ),
				'datapublisher' => __( 'Data Publisher Settings', 'wp-data-access' ),
				'databackup'    => __( 'Data Backup Settings', 'wp-data-access' ),
				'uninstall'     => __( 'Uninstall Settings', 'wp-data-access' ),
				'repository'    => __( 'Manage Repository', 'wp-data-access' ),
				'system'        => __( 'System Info', 'wp-data-access' ),
			];

			// Set default tab.
			$this->current_tab = 'backend';
			if ( isset( $_REQUEST['tab'] ) ) {

				if ( isset( $this->tabs[ $_REQUEST['tab'] ] ) ) {

					// Set requested tab (if value doesn't exist, default tab will be shown).
					$this->current_tab = sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ); // input var okay.

				}
			}

			// Recreation of repository must be performed before checking the availability of menu items (done next).
			if ( 'repository' === $this->current_tab && isset( $_REQUEST['repos'] ) && // input var okay.
			     'true' === sanitize_text_field( wp_unslash( $_REQUEST['repos'] ) ) ) { // input var okay.

				// Security check.
				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'wpda-settings-recreate-repository' ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				// Recreate repository.
				$wpda_repository = new WPDA_Repository();
				$wpda_repository->recreate();
				WPDA::set_option( WPDA::OPTION_WPDA_SETUP_ERROR ); // Set to default.
				WPDA::set_option( WPDA::OPTION_WPDA_SHOW_WHATS_NEW ); // Set to default.

				$msg = new WPDA_Message_Box(
					[
						'message_text' => __( 'Repository recreation completed', 'wp-data-access' ),
					]
				);
				$msg->box();
			}

			// Inform the user if status repository invalid.
			$wpda_repository = new WPDA_Repository();
			$wpda_repository->inform_user();

		}

		/**
		 * Show setting page
		 *
		 * Consists of tabs {@see WPDA_Settings::add_tabs()} and the content of the selected tab
		 * {@see WPDA_Settings::add_content()}.
		 *
		 * @since   1.0.0
		 *
		 * @see WPDA_Settings::add_tabs()
		 * @see WPDA_Settings::add_content()
		 */
		public function show() {

			?>

			<div class="wrap">
				<h1><?php echo __( 'Manage Plugin', 'wp-data-access' ); ?></h1>

				<?php

				$this->add_tabs();
				$this->add_content();

				?>

			</div>

			<?php

		}

		/**
		 * Add tabs to page
		 *
		 * @since   1.0.0
		 */
		protected function add_tabs() {

			?>

			<h2 class="nav-tab-wrapper">

				<?php

				foreach ( $this->tabs as $tab => $name ) {

					$class = ( $tab === $this->current_tab ) ? ' nav-tab-active' : '';
					echo '<a class="nav-tab' . esc_attr( $class ) . '" href="?page=' . esc_attr( $this->page ) .
					     '&tab=' . esc_attr( $tab ) . '">' . esc_attr( $name ) . '</a>';

				}

				?>

			</h2>

			<?php

		}

		/**
		 * Add content to page
		 *
		 * @since   1.0.0
		 */
		protected function add_content() {

			switch ( $this->current_tab ) {

				case 'frontend':
					$this->add_content_frontend();
					break;

				case 'datapublisher':
					$this->add_content_datapublisher();
					break;

				case 'databackup':
					$this->add_content_databackup();
					break;

				case 'uninstall':
					$this->add_content_uninstall();
					break;

				case 'repository':
					$this->add_content_repository();
					break;

				case 'system':
					$this->add_content_system();
					break;

				default:
					// Back-end settings is shown by default.
					$this->add_content_backend();

			}

		}

		/**
		 * Add system info tab content
		 *
		 * See class documentation for flow explanation.
		 *
		 * @since   2.0.13
		 */
		protected function add_content_system() {
			global $wpdb;
			global $wp_version;

			$uploads = wp_get_upload_dir();

			// Check table wp_wpda_menu_items.
			$table_name                               = WPDA_User_Menu_Model::get_menu_table_name();
			$table_menu_items_exists                  = WPDA_User_Menu_Model::table_exists();
			$triggers_menu_items_before_insert_exists = WPDA_User_Menu_Model::triggers_menu_items_before_insert_exists();
			$triggers_menu_items_before_update_exists = WPDA_User_Menu_Model::triggers_menu_items_before_update_exists();

			// Check table wp_wpda_table_design.
			$design_table_name        = WPDA_Design_Table_Model::get_design_table_name();
			$design_table_name_exists = WPDA_Design_Table_Model::table_exists();

			// Check table wp_wpda_logging
			$logging_table_name    = $wpdb->prefix . WPDA::get_option( WPDA::OPTION_WPDA_PREFIX ) . 'logging';
			$wpda_dictionary_exist = new WPDA_Dictionary_Exist( '', $logging_table_name );
			$logging_table_exists  = $wpda_dictionary_exist->table_exists( false );

			// Check data projects tables.
			$data_projects_project_name        = $wpdb->prefix . 'wpdp_project';
			$wpda_dictionary_exist             = new WPDA_Dictionary_Exist( '', $data_projects_project_name );
			$data_projects_project_name_exists = $wpda_dictionary_exist->table_exists( false );

			$data_projects_page_name        = $wpdb->prefix . 'wpdp_page';
			$wpda_dictionary_exist          = new WPDA_Dictionary_Exist( '', $data_projects_page_name );
			$data_projects_page_name_exists = $wpda_dictionary_exist->table_exists( false );

			$data_projects_table_name        = WPDP_Project_Design_Table_Model::get_design_table_name();
			$data_projects_table_name_exists = WPDP_Project_Design_Table_Model::table_exists();

			// Check table characteristics.
			$query                           =
				"select table_name, engine, table_collation " .
				"from information_schema.tables " .
				"where table_schema = '{$wpdb->dbname}' " .
				"and table_name in " .
				"('$table_name', '$design_table_name', '$logging_table_name', " .
				"'$data_projects_project_name', '$data_projects_page_name', '$data_projects_table_name')";
			$table_chararteristics_results   = $wpdb->get_results( $query, 'ARRAY_A' );
			$table_chararteristics_engine    = [];
			$table_chararteristics_collation = [];
			if ( false !== $table_chararteristics_results ) {
				foreach ( $table_chararteristics_results as $table_chararteristics_result ) {
					$table_chararteristics_engine[ $table_chararteristics_result['table_name'] ]    = $table_chararteristics_result['engine'];
					$table_chararteristics_collation[ $table_chararteristics_result['table_name'] ] = $table_chararteristics_result['table_collation'];
				}
			}
			?>
			<style>
				.wpda-table-system-info th {
					font-style: italic;
					font-weight: normal;
					padding: 0;
				}

				.wpda-table-system-info td {
					padding: 0;
				}

				.wpda-table-settings tr:nth-child(even) {
					background: unset;
				}
			</style>
			<script language="JavaScript">
				jQuery(document).ready(function () {
					var text_to_clipboard = new ClipboardJS("#button-copy-to-clipboard", {
						text: function () {
							clipboard_text = "";
							jQuery("#wpda_table_info tr .wpda_system_info_title").each(function () {
								clipboard_text += jQuery(this).text().trim() + "\n";
								jQuery(this).parent().find("th.wpda_system_info_subtitle").each(function () {
									clipboard_text += jQuery(this).text().trim();
									clipboard_text += "=";
									clipboard_text += jQuery(this).parent().find("td.wpda_system_info_value").text().trim() + "\n";
								});
							});
							return clipboard_text;
						}
					});
					text_to_clipboard.on('success', function (e) {
						alert('<?php echo __( 'System info successfully copied to clipboard!' ); ?>');
					});
					text_to_clipboard.on('error', function (e) {
						console.log('<?php echo __( 'Could not copy system info to clipboard!' ); ?>');
					});
				});
			</script>
			<table class="wpda-table-settings" id="wpda_table_info">
				<tr>
					<th class="wpda_system_info_title"><?php echo __( 'Operating System' ); ?></th>
					<td>
						<table class="wpda-table-system-info" style="width:100%">
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Type' ); ?></th>
								<td class="wpda_system_info_value">
									<?php echo php_uname( 's' ); ?>
								</td>
								<td style="float:right">
									<a id="button-copy-to-clipboard" href="javascript:void(0)"
									   class="button button-primary">
										<?php echo __( 'Copy to clipboard' ); ?>
									</a>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Release' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo php_uname( 'r' ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Version' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo php_uname( 'v' ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Machine Type' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo php_uname( 'm' ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Host Name' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo php_uname( 'n' ); ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<th class="wpda_system_info_title"><?php echo __( 'Database Management System' ); ?></th>
					<td>
						<table class="wpda-table-system-info" style="width:100%">
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Version' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php
									$db_version = $wpdb->get_results( "SHOW VARIABLES LIKE 'version'", 'ARRAY_N' );
									if ( is_array( $db_version ) && isset( $db_version[0][1] ) ) {
										echo $db_version[0][1];
									} else {
										$wpdb->db_version;
									}
									?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Pivileges' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php
									$db_privileges = $wpdb->get_results( 'SHOW PRIVILEGES', 'ARRAY_N' );
									if ( is_array( $db_privileges ) ) {
										$db_privileges_output = '';
										foreach ( $db_privileges as $db_privilege ) {
											$db_privileges_output .= "$db_privilege[0], ";
										}
										echo substr( $db_privileges_output, 0, strlen( $db_privileges_output ) - 2 );
									}
									?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Grants' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php
									$db_grants = $wpdb->get_results( 'SHOW GRANTS', 'ARRAY_N' );
									if ( is_array( $db_grants ) ) {
										$db_grants_output = '';
										foreach ( $db_grants as $db_grant ) {
											$strpos = strpos( $db_grant[0], 'IDENTIFIED BY PASSWORD ' );
											if ( false !== $strpos ) {
												$db_grants_output .= substr( $db_grant[0], 0, $strpos ) . 'IDENTIFIED BY PASSWORD \'*****\'<br/>';
											} else {
												$db_grants_output .= "$db_grant[0]<br/>";
											}
										}
										echo $db_grants_output;
									}
									?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'SQL Mode' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php
									$db_sql_mode = $wpdb->get_results( 'SHOW VARIABLES LIKE \'sql_mode\'', 'ARRAY_N' );
									if ( isset( $db_sql_mode[0][1] ) ) {
										echo $db_sql_mode[0][1];
									}
									?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<th class="wpda_system_info_title"><?php echo __( 'Web Server' ); ?></th>
					<td>
						<table class="wpda-table-system-info" style="width:100%">
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Software' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo $_SERVER['SERVER_SOFTWARE']; ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'PHP Version' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo phpversion(); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Protocol' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo $_SERVER['SERVER_PROTOCOL']; ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Name' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo $_SERVER['SERVER_NAME']; ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Address' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo $_SERVER['SERVER_ADDR']; ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Root DIR' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo $_SERVER['DOCUMENT_ROOT']; ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Temp DIR' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo sys_get_temp_dir(); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'HTTP Upload' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo @ini_get( 'file_uploads' ) ? 'Enabled' : 'Disabled'; ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Max Upload File Size' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo @ini_get( 'upload_max_filesize' ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Post Max Size' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo @ini_get( 'post_max_size' ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Max Execution Time' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo @ini_get( 'max_execution_time' ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Max Input Time' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo @ini_get( 'max_input_time' ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Memory Limit' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo @ini_get( 'memory_limit' ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Output Buffering' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo @ini_get( 'output_buffering' ); ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<th class="wpda_system_info_title"><?php echo __( 'WordPress' ); ?></th>
					<td>
						<table class="wpda-table-system-info" style="width:100%">
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Version' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo $wp_version; ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Home DIR' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php
									echo get_home_path();

									$error_level = error_reporting();
									error_reporting( E_ALL ^ E_WARNING );
									$file_permission = fileperms( get_home_path() );
									error_reporting( $error_level );
									echo '&nbsp;&nbsp;&nbsp;>&nbsp;&nbsp;&nbsp;' . decoct( $file_permission & 0777 );
									?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Uploads DIR' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo $uploads['basedir'];

									$error_level = error_reporting();
									error_reporting( E_ALL ^ E_WARNING );
									$file_permission = fileperms( $uploads['basedir'] );
									error_reporting( $error_level );
									echo '&nbsp;&nbsp;&nbsp;>&nbsp;&nbsp;&nbsp;' . decoct( $file_permission & 0777 );
									?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Home URL' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo get_home_url(); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Site URL' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo get_site_url(); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Upload URL' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo $uploads['baseurl']; ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Use MySQLi' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php
									// Taken from wp-db class
									if ( function_exists( 'mysqli_connect' ) ) {
										$use_mysqli = true;
										if ( defined( 'WP_USE_EXT_MYSQL' ) ) {
											$use_mysqli = ! WP_USE_EXT_MYSQL;
										}
										echo $use_mysqli ? 'true' : 'false';
									}
									?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Database Host' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo DB_HOST; ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Database Name' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo DB_NAME; ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Database User' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo DB_USER; ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Database Character Set' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo DB_CHARSET; ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Database Collate' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo DB_COLLATE; ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'WP Debugging Mode' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo ! defined( 'WP_DEBUG' ) ? 'undefined' : ( true === WP_DEBUG ? 'true' : 'false' ); ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<th class="wpda_system_info_title"><?php echo __( 'WP Data Access' ); ?></th>
					<td>
						<table class="wpda-table-system-info" style="width:100%">
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Version' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo WPDA::get_option( WPDA::OPTION_WPDA_VERSION ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Repository' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php
									echo $table_menu_items_exists ? '+' : '-';
									echo esc_attr( $table_name );
									echo ' (';
									echo isset( $table_chararteristics_engine[ $table_name ] ) ? $table_chararteristics_engine[ $table_name ] : '';
									echo ' | ';
									echo isset( $table_chararteristics_collation[ $table_name ] ) ? $table_chararteristics_collation[ $table_name ] : '';
									echo ') (triggers ';
									echo $triggers_menu_items_before_insert_exists ? '+' : '-';
									echo 'insert | ';
									echo $triggers_menu_items_before_update_exists ? '+' : '-';
									echo 'update)';
									echo ' <br/>';
									echo $design_table_name_exists ? '+' : '-';
									echo esc_attr( $design_table_name );
									echo ' (';
									echo isset( $table_chararteristics_engine[ $design_table_name ] ) ? $table_chararteristics_engine[ $design_table_name ] : '';
									echo ' | ';
									echo isset( $table_chararteristics_collation[ $design_table_name ] ) ? $table_chararteristics_collation[ $design_table_name ] : '';
									echo ')';
									echo ' <br/>';
									echo $logging_table_exists ? '+' : '-';
									echo esc_attr( $logging_table_name );
									echo ' (';
									echo isset( $table_chararteristics_engine[ $logging_table_name ] ) ? $table_chararteristics_engine[ $logging_table_name ] : '';
									echo ' | ';
									echo isset( $table_chararteristics_collation[ $logging_table_name ] ) ? $table_chararteristics_collation[ $logging_table_name ] : '';
									echo ')';
									echo ' <br/>';
									echo $data_projects_project_name_exists ? '+' : '-';
									echo esc_attr( $data_projects_project_name );
									echo ' (';
									echo isset( $table_chararteristics_engine[ $data_projects_project_name ] ) ? $table_chararteristics_engine[ $data_projects_project_name ] : '';
									echo ' | ';
									echo isset( $table_chararteristics_collation[ $data_projects_project_name ] ) ? $table_chararteristics_collation[ $data_projects_project_name ] : '';
									echo ')';
									echo ' <br/>';
									echo $data_projects_page_name_exists ? '+' : '-';
									echo esc_attr( $data_projects_page_name );
									echo ' (';
									echo isset( $table_chararteristics_engine[ $data_projects_page_name ] ) ? $table_chararteristics_engine[ $data_projects_page_name ] : '';
									echo ' | ';
									echo isset( $table_chararteristics_collation[ $data_projects_page_name ] ) ? $table_chararteristics_collation[ $data_projects_page_name ] : '';
									echo ')';
									echo ' <br/>';
									echo $data_projects_table_name_exists ? '+' : '-';
									echo esc_attr( $data_projects_table_name );
									echo ' (';
									echo isset( $table_chararteristics_engine[ $data_projects_table_name ] ) ? $table_chararteristics_engine[ $data_projects_table_name ] : '';
									echo ' | ';
									echo isset( $table_chararteristics_collation[ $data_projects_table_name ] ) ? $table_chararteristics_collation[ $data_projects_table_name ] : '';
									echo ')';
									?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<th class="wpda_system_info_title"><?php echo __( 'Browser' ); ?></th>
					<td>
						<table id="wpda_system_info_browser" class="wpda-table-system-info" style="width:100%">
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Agent' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo $_SERVER['HTTP_USER_AGENT']; ?>
								</td>
							</tr>
							<script language="JavaScript">
								jQuery.each(jQuery.browser, function (i, val) {
									jQuery("#wpda_system_info_browser").append("<tr><th class=\"wpda_system_info_subtitle\">" + i[0].toUpperCase() + i.substring(1).toLowerCase() + "</th><td class=\"wpda_system_info_value\" colspan=\"2\">" + val + "</td></tr>");
								});
							</script>
						</table>
					</td>
				</tr>
			</table>
			<?php
		}

		/**
		 * Add data publisher tab content
		 *
		 * See class documentation for flow explanation.
		 *
		 * @since   2.0.15
		 */
		protected function add_content_datapublisher() {
			if ( isset( $_REQUEST['action'] ) ) {
				$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // input var okay.

				// Security check.
				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'wpda-publication-settings' ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				if ( 'save' === $action ) {
					// Save options.
					if ( isset( $_REQUEST['load_datatables'] ) ) {
						$load_datatables_request = sanitize_text_field( wp_unslash( $_REQUEST['load_datatables'] ) ); // input var okay.

						if ( 'both' === $load_datatables_request || 'be' === $load_datatables_request ) {
							WPDA::set_option( WPDA::OPTION_BE_LOAD_DATATABLES, 'on' );
						} else {
							WPDA::set_option( WPDA::OPTION_BE_LOAD_DATATABLES, 'off' );
						}

						if ( 'both' === $load_datatables_request || 'fe' === $load_datatables_request ) {
							WPDA::set_option( WPDA::OPTION_FE_LOAD_DATATABLES, 'on' );
						} else {
							WPDA::set_option( WPDA::OPTION_FE_LOAD_DATATABLES, 'off' );
						}
					}

					if ( isset( $_REQUEST['load_datatables_responsive'] ) ) {
						$load_datatables_responsive_request = sanitize_text_field( wp_unslash( $_REQUEST['load_datatables_responsive'] ) ); // input var okay.

						if ( 'both' === $load_datatables_responsive_request || 'be' === $load_datatables_responsive_request ) {
							WPDA::set_option( WPDA::OPTION_BE_LOAD_DATATABLES_RESPONSE, 'on' );
						} else {
							WPDA::set_option( WPDA::OPTION_BE_LOAD_DATATABLES_RESPONSE, 'off' );
						}

						if ( 'both' === $load_datatables_responsive_request || 'fe' === $load_datatables_responsive_request ) {
							WPDA::set_option( WPDA::OPTION_FE_LOAD_DATATABLES_RESPONSE, 'on' );
						} else {
							WPDA::set_option( WPDA::OPTION_FE_LOAD_DATATABLES_RESPONSE, 'off' );
						}
					}

					if ( isset( $_REQUEST['publication_roles'] ) ) {
						$publication_roles_request = isset( $_REQUEST['publication_roles'] ) ? $_REQUEST['publication_roles'] : null;
						if ( is_array( $publication_roles_request ) ) {
							$publication_roles = implode( ',', $publication_roles_request );
						} else {
							$publication_roles = '';
						}
					} else {
						$publication_roles = '';
					}
					WPDA::set_option( WPDA::OPTION_DP_PUBLICATION_ROLES, $publication_roles );

					if ( isset( $_REQUEST['load_bootstrap'] ) ) {
						$load_bootstrap_request = sanitize_text_field( wp_unslash( $_REQUEST['load_bootstrap'] ) ); // input var okay.

						if ( 'both' === $load_bootstrap_request || 'be' === $load_bootstrap_request ) {
							WPDA::set_option( WPDA::OPTION_BE_LOAD_BOOTSTRAP, 'on' );
						} else {
							WPDA::set_option( WPDA::OPTION_BE_LOAD_BOOTSTRAP, 'off' );
						}

						if ( 'both' === $load_bootstrap_request || 'fe' === $load_bootstrap_request ) {
							WPDA::set_option( WPDA::OPTION_FE_LOAD_BOOTSTRAP, 'on' );
						} else {
							WPDA::set_option( WPDA::OPTION_FE_LOAD_BOOTSTRAP, 'off' );
						}
					}
				} elseif ( 'setdefaults' === $action ) {
					// Set all publication settings back to default.
					WPDA::set_option( WPDA::OPTION_BE_LOAD_DATATABLES );
					WPDA::set_option( WPDA::OPTION_FE_LOAD_DATATABLES );

					WPDA::set_option( WPDA::OPTION_BE_LOAD_DATATABLES_RESPONSE );
					WPDA::set_option( WPDA::OPTION_FE_LOAD_DATATABLES_RESPONSE );

					WPDA::set_option( WPDA::OPTION_DP_PUBLICATION_ROLES );

					WPDA::set_option( WPDA::OPTION_BE_LOAD_BOOTSTRAP );
					WPDA::set_option( WPDA::OPTION_FE_LOAD_BOOTSTRAP );
				}

				$msg = new WPDA_Message_Box(
					[
						'message_text' => __( 'Settings saved', 'wp-data-access' ),
					]
				);
				$msg->box();

			}

			$datatables_version = WPDA::get_option( WPDA::OPTION_WPDA_DATATABLES_VERSION );
			$be_load_datatables = WPDA::get_option( WPDA::OPTION_BE_LOAD_DATATABLES );
			$fe_load_datatables = WPDA::get_option( WPDA::OPTION_FE_LOAD_DATATABLES );
			if ( 'on' === $be_load_datatables && 'on' === $fe_load_datatables ) {
				$load_datatables = 'both';
			} elseif ( 'on' === $be_load_datatables ) {
				$load_datatables = 'be';
			} elseif ( 'on' === $fe_load_datatables ) {
				$load_datatables = 'fe';
			} else {
				$load_datatables = '';
			}

			$datatables_responsive_version = WPDA::get_option( WPDA::OPTION_WPDA_DATATABLES_RESPONSIVE_VERSION );
			$be_load_datatables_responsive = WPDA::get_option( WPDA::OPTION_BE_LOAD_DATATABLES_RESPONSE );
			$fe_load_datatables_responsive = WPDA::get_option( WPDA::OPTION_FE_LOAD_DATATABLES_RESPONSE );
			if ( 'on' === $be_load_datatables_responsive && 'on' === $fe_load_datatables_responsive ) {
				$load_datatables_responsive = 'both';
			} elseif ( 'on' === $be_load_datatables_responsive ) {
				$load_datatables_responsive = 'be';
			} elseif ( 'on' === $fe_load_datatables_responsive ) {
				$load_datatables_responsive = 'fe';
			} else {
				$load_datatables_responsive = '';
			}

			global $wp_roles;
			$lov_roles = [];
			foreach ( $wp_roles->roles as $role => $val ) {
				array_push( $lov_roles, $role );
			}
			$publication_roles = WPDA::get_option( WPDA::OPTION_DP_PUBLICATION_ROLES );

			$bootstrap_version = WPDA::get_option( WPDA::OPTION_WPDA_BOOTSTRAP_VERSION );
			$be_load_bootstrap = WPDA::get_option( WPDA::OPTION_BE_LOAD_BOOTSTRAP );
			$fe_load_bootstrap = WPDA::get_option( WPDA::OPTION_FE_LOAD_BOOTSTRAP );
			if ( 'on' === $be_load_bootstrap && 'on' === $fe_load_bootstrap ) {
				$load_bootstrap = 'both';
			} elseif ( 'on' === $be_load_bootstrap ) {
				$load_bootstrap = 'be';
			} elseif ( 'on' === $fe_load_bootstrap ) {
				$load_bootstrap = 'fe';
			} else {
				$load_bootstrap = '';
			}
			?>
			<form id="wpda_settings_publication" method="post"
				  action="?page=<?php echo esc_attr( $this->page ); ?>&tab=datapublisher">
				<table class="wpda-table-settings">
					<tr>
					<tr>
						<th>jQuery DataTables</th>
						<td>
							<label>
								<?php echo sprintf( __( 'Load jQuery DataTables (version %s) scripts and styles', 'wp-data-access' ), esc_attr( $datatables_version ) ); ?>
							</label>
							<div style="height:10px"></div>
							<labeL>
								<input type="radio" name="load_datatables" value="both"
									<?php echo 'both' === $load_datatables ? 'checked' : ''; ?>
								><?php echo __( 'In WordPress Back-end and Frond-end', 'wp-data-access' ); ?>
							</labeL>
							<br/>
							<labeL>
								<input type="radio" name="load_datatables" value="be"
									<?php echo 'be' === $load_datatables ? 'checked' : ''; ?>
								><?php echo __( 'In WordPress Back-end only ', 'wp-data-access' ); ?>
							</labeL>
							<br/>
							<labeL>
								<input type="radio" name="load_datatables" value="fe"
									<?php echo 'fe' === $load_datatables ? 'checked' : ''; ?>
								><?php echo __( 'In WordPress Frond-end only', 'wp-data-access' ); ?>
							</labeL>
							<br/>
							<labeL>
								<input type="radio" name="load_datatables" value=""
									<?php echo '' === $load_datatables ? 'checked' : ''; ?>
								><?php echo __( 'Do not load jQuery DataTables', 'wp-data-access' ); ?>
							</labeL>
						</td>
					</tr>
					<tr>
						<th>jQuery DataTables Responsive</th>
						<td>
							<label>
								<?php echo sprintf( __( 'Load jQuery DataTables Responsive (version %s) scripts and styles', 'wp-data-access' ), esc_attr( $datatables_responsive_version ) ); ?>
							</label>
							<div style="height:10px"></div>
							<labeL>
								<input type="radio" name="load_datatables_responsive" value="both"
									<?php echo 'both' === $load_datatables_responsive ? 'checked' : ''; ?>
								><?php echo __( 'In WordPress Back-end and Frond-end', 'wp-data-access' ); ?>
							</labeL>
							<br/>
							<labeL>
								<input type="radio" name="load_datatables_responsive" value="be"
									<?php echo 'be' === $load_datatables_responsive ? 'checked' : ''; ?>
								><?php echo __( 'In WordPress Back-end only ', 'wp-data-access' ); ?>
							</labeL>
							<br/>
							<labeL>
								<input type="radio" name="load_datatables_responsive" value="fe"
									<?php echo 'fe' === $load_datatables_responsive ? 'checked' : ''; ?>
								><?php echo __( 'In WordPress Frond-end only', 'wp-data-access' ); ?>
							</labeL>
							<br/>
							<labeL>
								<input type="radio" name="load_datatables_responsive" value=""
									<?php echo '' === $load_datatables_responsive ? 'checked' : ''; ?>
								><?php echo __( 'Do not load jQuery DataTables Responsive', 'wp-data-access' ); ?>
							</labeL>
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Data Publisher Tool Access', 'wp-data-access' ); ?></th>
						<td><?php echo __( 'Select the WordPress roles which should be allowed to have access to the Data Publisher tool', 'wp-data-access' ); ?>
							<div style="height:10px"></div>
							<select name="publication_roles[]" multiple size="6">
								<?php
								foreach ( $lov_roles as $lov_role ) {
									if ( false !== strpos( $publication_roles, $lov_role ) ) {
										$granted = 'selected';
									} else {
										$granted = '';
									}
									?>
									<option value="<?php echo $lov_role; ?>" <?php echo $granted; ?>><?php echo $lov_role; ?></option>
									<?php
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th>Bootstrap</th>
						<td>
							<label>
								<?php echo sprintf( __( 'Load Bootstrap (version %s) scripts and styles', 'wp-data-access' ), esc_attr( $bootstrap_version ) ); ?>
							</label>
							<div style="height:10px"></div>
							<labeL>
								<input type="radio" name="load_bootstrap" value="both"
									<?php echo 'both' === $load_bootstrap ? 'checked' : ''; ?>
								><?php echo __( 'In WordPress Back-end and Frond-end', 'wp-data-access' ); ?>
							</labeL>
							<br/>
							<labeL>
								<input type="radio" name="load_bootstrap" value="be"
									<?php echo 'be' === $load_bootstrap ? 'checked' : ''; ?>
								><?php echo __( 'In WordPress Back-end only ', 'wp-data-access' ); ?>
							</labeL>
							<br/>
							<labeL>
								<input type="radio" name="load_bootstrap" value="fe"
									<?php echo 'fe' === $load_bootstrap ? 'checked' : ''; ?>
								><?php echo __( 'In WordPress Frond-end only', 'wp-data-access' ); ?>
							</labeL>
							<br/>
							<labeL>
								<input type="radio" name="load_bootstrap" value=""
									<?php echo '' === $load_bootstrap ? 'checked' : ''; ?>
								><?php echo __( 'Do not load Bootstrap', 'wp-data-access' ); ?>
							</labeL>
						</td>
					</tr>
					<tr>
						<th><span class="dashicons dashicons-info" style="float:right;font-size:300%;"></span></th>
						<td>
							<span class="dashicons dashicons-yes"></span>
							<?php echo __( 'jQuery DataTables (+Responsive) is needed in the Front-end to support publications on your website', 'wp-data-access' ); ?>
							<br/>
							<span class="dashicons dashicons-yes"></span>
							<?php echo __( 'jQuery DataTables (+Responsive) is needed in the Back-end to test publications in the WordPress dashboard', 'wp-data-access' ); ?>
							<br/>
							<span class="dashicons dashicons-yes"></span>
							<?php echo __( 'If you have already loaded jQuery DataTables for other purposes disable loading them to prevent duplication errors', 'wp-data-access' ); ?>
							<br/>
							<span class="dashicons dashicons-yes"></span>
							<?php echo __( 'Users have readonly access to tables to which you have granted access in Front-end Settings only', 'wp-data-access' ); ?>
							<br/>
							<span class="dashicons dashicons-yes"></span>
							<?php echo __( 'Bootstrap is not needed for jQuery DataTables (it is optional)', 'wp-data-access' ); ?>
						</td>
					</tr>
				</table>
				<div class="wpda-table-settings-button">
					<input type="hidden" name="action" value="save"/>
					<input type="submit"
						   value="<?php echo __( 'Save Publication Settings', 'wp-data-access' ); ?>"
						   class="button button-primary"/>
					<a href="javascript:void(0)"
					   onclick="if (confirm('<?php echo __( 'Reset to defaults?', 'wp-data-access' ); ?>')) {
							   jQuery('input[name=&quot;action&quot;]').val('setdefaults');
							   jQuery('#wpda_settings_publication').trigger('submit')
							   }"
					   class="button">
						<?php echo __( 'Reset Publication Settings To Defaults', 'wp-data-access' ); ?>
					</a>
				</div>
				<?php wp_nonce_field( 'wpda-publication-settings', '_wpnonce', false ); ?>
			</form>
			<?php
		}

		/**
		 * Add data backup tab content
		 *
		 * See class documentation for flow explanation.
		 *
		 * @since   2.0.7
		 */
		protected function add_content_databackup() {

			if ( isset( $_REQUEST['action'] ) ) {
				$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // input var okay.

				// Security check.
				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'wpda-databackup-settings' ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				if ( 'save' === $action ) {
					// Save options.
					$save_local_path = isset( $_REQUEST['local_path'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['local_path'] ) ) : ''; // input var okay.
					if ( 'WIN' === strtoupper( substr( PHP_OS, 0, 3 ) ) ) {
						if ( '\\' !== substr( $save_local_path, - 1 ) ) {
							$save_local_path .= '\\';
						}
					} else {
						if ( '/' !== substr( $save_local_path, - 1 ) ) {
							$save_local_path .= '/';
						}
					}
					WPDA::set_option( WPDA::OPTION_DB_LOCAL_PATH, $save_local_path );

					$options_activated = [];
					if ( isset( $_REQUEST['local_path_activated'] ) ) {
						$error_level = error_reporting();
						error_reporting( E_ALL ^ E_WARNING );
						$local_path      = WPDA::get_option( WPDA::OPTION_DB_LOCAL_PATH );
						$file_permission = fileperms( $local_path );
						error_reporting( $error_level );
						if ( $file_permission && '4' === substr( decoct( $file_permission ), 0, 1 ) ) {
							$options_activated['local_path'] = true;
						}
					}

					if ( isset( $_REQUEST['dropbox_auth'] ) ) {
						$dropbox_auth = sanitize_text_field( wp_unslash( $_REQUEST['dropbox_auth'] ) );
					} else {
						$dropbox_auth = '';
					}
					$dropbox_auth_saved = get_option( 'wpda_db_dropbox_auth' );
					if ( '' !== $dropbox_auth && $dropbox_auth_saved !== $dropbox_auth ) {
						$client   = new \GuzzleHttp\Client( [
							'base_uri' => 'https://api.dropboxapi.com/oauth2/token',
						] );
						$response = $client->request(
							'POST',
							'',
							[
								'form_params' => [
									'code'          => $dropbox_auth,
									'grant_type'    => 'authorization_code',
									'client_id'     => SELF::DROPBOX_CLIENT_ID,
									'client_secret' => SELF::DROPBOX_CLIENT_SECRET,
								]
							]
						);
						if ( ! ( 200 === $response->getStatusCode() && 'OK' === $response->getReasonPhrase() ) ) {
							$msg = new WPDA_Message_Box(
								[
									'message_text'           => __( 'Dropbox authorization failed ', 'wp-data-access' ) .
									                            $response->getStatusCode() . ' ' .
									                            $response->getReasonPhrase(),
									'message_type'           => 'error',
									'message_is_dismissible' => false,
								]
							);
							$msg->box();
						} else {
							$body_content = json_decode( $response->getBody()->getContents() );
							$access_token = $body_content->access_token;

							update_option( 'wpda_db_dropbox_access_token', $access_token );
						}
					}
					update_option( 'wpda_db_dropbox_auth', $dropbox_auth );

					if ( isset( $_REQUEST['dropbox_activated'] ) ) {
						$options_activated['dropbox'] = true;
					}

					if ( isset( $_REQUEST['dropbox_folder'] ) ) {
						$dropbox_folder = sanitize_text_field( wp_unslash( $_REQUEST['dropbox_folder'] ) );
						if ( '/' !== substr( $dropbox_folder, - 1 ) ) {
							$dropbox_folder .= '/';
						}
					}
					WPDA::set_option( WPDA::OPTION_DB_DROPBOX_PATH, $dropbox_folder );

					update_option( 'wpda_db_options_activated', $options_activated );
				} elseif ( 'setdefaults' === $action ) {
					// Set all data backup settings back to default.
					WPDA::set_option( WPDA::OPTION_DB_LOCAL_PATH );
					WPDA::set_option( WPDA::OPTION_DB_DROPBOX_PATH );
				}

				$msg = new WPDA_Message_Box(
					[
						'message_text' => __( 'Settings saved', 'wp-data-access' ),
					]
				);
				$msg->box();
			}

			$error_level = error_reporting();
			error_reporting( E_ALL ^ E_WARNING );
			$local_path      = WPDA::get_option( WPDA::OPTION_DB_LOCAL_PATH );
			$file_permission = fileperms( $local_path );
			error_reporting( $error_level );

			$owner_info = ( ( $file_permission & 0x0100 ) ? 'r' : '-' );
			$owner_info .= ( ( $file_permission & 0x0080 ) ? 'w' : '-' );
			$owner_info .= ( ( $file_permission & 0x0040 ) ?
				( ( $file_permission & 0x0800 ) ? 's' : 'x' ) :
				( ( $file_permission & 0x0800 ) ? 'S' : '-' ) );
			$group_info = ( ( $file_permission & 0x0020 ) ? 'r' : '-' );
			$group_info .= ( ( $file_permission & 0x0010 ) ? 'w' : '-' );
			$group_info .= ( ( $file_permission & 0x0008 ) ?
				( ( $file_permission & 0x0400 ) ? 's' : 'x' ) :
				( ( $file_permission & 0x0400 ) ? 'S' : '-' ) );
			$world_info = ( ( $file_permission & 0x0004 ) ? 'r' : '-' );
			$world_info .= ( ( $file_permission & 0x0002 ) ? 'w' : '-' );
			$world_info .= ( ( $file_permission & 0x0001 ) ?
				( ( $file_permission & 0x0200 ) ? 't' : 'x' ) :
				( ( $file_permission & 0x0200 ) ? 'T' : '-' ) );

			$dropbox_auth   = get_option( 'wpda_db_dropbox_auth' );
			$dropbox_folder = WPDA::get_option( WPDA::OPTION_DB_DROPBOX_PATH );

			$options_activated = get_option( 'wpda_db_options_activated' );
			?>

			<form id="wpda_settings_databackup" method="post"
				  action="?page=<?php echo esc_attr( $this->page ); ?>&tab=databackup">
				<table class="wpda-table-settings">
					<tr>
						<th><?php echo __( 'Local file system' ); ?></th>
						<td>
							<label>
								<input type="checkbox"
									   name="local_path_activated" <?php if ( isset( $options_activated['local_path'] ) ) {
									echo 'checked';
								} ?> />
								<?php echo __( 'Activated', 'wp-data-access' ) ?>
							</label>
							<br/><br/>
							<?php echo __( 'Enter the name of the folder where data backup files should be stored.' ); ?>
							<br/>
							<input type="text" name="local_path" value="<?php echo $local_path; ?>"/>
							<span><?php echo __( 'Make sure the folder exists with permission to write files.' ); ?></span>
							<?php
							if ( 'WIN' !== strtoupper( substr( PHP_OS, 0, 3 ) ) ) {
								if ( ! $file_permission ) {
									echo '<br/><br/>';
									echo __( 'ERROR: Invalid folder', 'wp-data-access' );
								} else {
									if ( '4' !== substr( decoct( $file_permission ), 0, 1 ) ) {
										echo '<br/><br/>';
										echo __( 'ERROR: Not a folder', 'wp-data-access' );
									} else {
										$fileowner  = fileowner( $local_path );
										$groupowner = filegroup( $local_path );
										?>
										<br/><br/>
										{
										<?php echo __( '"Permission"' ); ?>:
										{
										<?php echo __( '"owner"' ); ?>:
										{
										<?php echo __( '"name"' ); ?>: "<?php echo posix_getpwuid( $fileowner )['name'] ?>",
										<?php echo __( '"access"' ); ?>: "<?php echo $owner_info; ?>"
										},
										<?php echo __( '"group"' ); ?>:
										{
										<?php echo __( '"name"' ); ?>: "<?php echo posix_getpwuid( $groupowner )['name'] ?>",
										<?php echo __( '"access"' ); ?>: "<?php echo $group_info; ?>"
										},
										<?php echo __( '"world"' ); ?>:
										{
										<?php echo __( '"access"' ); ?>: "<?php echo $world_info; ?>"
										}
										}
										}
										<?php
									}
								}
							}
							?>
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Dropbox' ); ?></th>
						<td>
							<label>
								<input type="checkbox"
									   name="dropbox_activated" <?php if ( isset( $options_activated['dropbox'] ) ) {
									echo 'checked';
								} ?> />
								<?php echo __( 'Activated', 'wp-data-access' ) ?>
							</label>
							<br/><br/>
							<a href="https://www.dropbox.com/" class="button button-secondary" target="_blank">
								<?php echo __( 'Create a Dropbox account' ); ?>
							</a>
							<span style="vertical-align:-webkit-baseline-middle;">
								<?php echo __( 'You can skip this step if you already have an account.' ); ?>
							</span>
							<br/><br/>
							<?php echo __( 'Authorize the WP Data Access Dropbox app and enter the authorization code in the text box below.' ); ?>
							<br/>
							<input type="text" name="dropbox_auth" value="<?php echo $dropbox_auth; ?>"/>
							<a href="https://www.dropbox.com/oauth2/authorize?response_type=code&client_id=rv5japeynhpzmyy"
							   class="button button-secondary"
							   target="_blank"
							   style="vertical-align:bottom;">
								<?php echo __( 'Get Dropbox authorization code' ); ?>
							</a>
							<br/><br/>
							<?php echo __( 'Enter the name of the folder where data backup files should be stored. If the folder doesn\'t exists, it\'ll be created for you.' ); ?>
							<br/>
							<input type="text" name="dropbox_folder" value="<?php echo $dropbox_folder; ?>"/>
						</td>
					</tr>
				</table>
				<div class="wpda-table-settings-button">
					<input type="hidden" name="action" value="save"/>
					<input type="submit"
						   value="<?php echo __( 'Save Data Backup Settings', 'wp-data-access' ); ?>"
						   class="button button-primary"/>
					<a href="javascript:void(0)"
					   onclick="if (confirm('<?php echo __( 'Reset to defaults?', 'wp-data-access' ); ?>')) {
							   jQuery('input[name=&quot;action&quot;]').val('setdefaults');
							   jQuery('#wpda_settings_frontend').trigger('submit')
							   }"
					   class="button">
						<?php echo __( 'Reset Data Backup To Defaults', 'wp-data-access' ); ?>
					</a>
				</div>
				<?php wp_nonce_field( 'wpda-databackup-settings', '_wpnonce', false ); ?>
			</form>

			<?php

		}

		/**
		 * Add front-end tab content
		 *
		 * See class documentation for flow explanation.
		 *
		 * @since   1.0.0
		 */
		protected function add_content_frontend() {

			if ( isset( $_REQUEST['action'] ) ) {
				$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // input var okay.

				// Security check.
				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'wpda-front-end-settings' ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				if ( 'save' === $action ) {
					WPDA::set_option(
						WPDA::OPTION_FE_TABLE_ACCESS,
						isset( $_REQUEST['table_access'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['table_access'] ) ) : null // input var okay.
					);

					$table_access_selected_new_value = isset( $_REQUEST['table_access_selected'] ) ? $_REQUEST['table_access_selected'] : null;
					if ( is_array( $table_access_selected_new_value ) ) {
						// Check the requested table names for sql injection. This is simply done by checking if the table
						// name exists in our WordPress database.
						$table_access_selected_new_value_checked = [];
						foreach ( $table_access_selected_new_value as $key => $value ) {
							$wpda_dictionary_checks = new WPDA_Dictionary_Exist( '', $value );
							if ( $wpda_dictionary_checks->table_exists( false, false ) ) {
								// Add existing table to list.
								$table_access_selected_new_value_checked[ $key ] = $value;
							} else {
								// An invalid table name was provided. Might be an sql injection attack or an invalid state.
								wp_die( __( 'ERROR: Table not found', 'wp-data-access' ) );
							}
						}
					} else {
						$table_access_selected_new_value_checked = '';
					}

					WPDA::set_option(
						WPDA::OPTION_FE_TABLE_ACCESS_SELECTED,
						$table_access_selected_new_value_checked
					);
				} elseif ( 'setdefaults' === $action ) {
					// Set all frond-end settings back to default.
					WPDA::set_option( WPDA::OPTION_FE_TABLE_ACCESS );
					WPDA::set_option( WPDA::OPTION_FE_TABLE_ACCESS_SELECTED );
				}

				$msg = new WPDA_Message_Box(
					[
						'message_text' => __( 'Settings saved', 'wp-data-access' ),
					]
				);
				$msg->box();

			}

			// Get options.
			$table_access          = WPDA::get_option( WPDA::OPTION_FE_TABLE_ACCESS );
			$table_access_selected = WPDA::get_option( WPDA::OPTION_FE_TABLE_ACCESS_SELECTED );

			if ( is_array( $table_access_selected ) ) {
				// Convert table for simple access.
				$table_access_selected_by_name = [];
				foreach ( $table_access_selected as $key => $value ) {
					$table_access_selected_by_name[ $value ] = true;
				}
			}

			?>

			<form id="wpda_settings_frontend" method="post"
				  action="?page=<?php echo esc_attr( $this->page ); ?>&tab=frontend">
				<table class="wpda-table-settings">
					<tr>
						<th><?php echo __( 'Table access', 'wp-data-access' ); ?></th>
						<td>
							<label>
								<input
										type="radio"
										name="table_access"
										value="show"
									<?php echo 'show' === $table_access ? 'checked' : ''; ?>
								><?php echo __( 'Show WordPress tables', 'wp-data-access' ); ?>
							</label>
							<br/>
							<label>
								<input
										type="radio"
										name="table_access"
										value="hide"
									<?php echo 'hide' === $table_access ? 'checked' : ''; ?>
								><?php echo __( 'Hide WordPress tables', 'wp-data-access' ); ?>
							</label>
							<br/>
							<label>
								<input
										type="radio"
										name="table_access"
										value="select"
									<?php echo 'select' === $table_access ? 'checked' : ''; ?>
								><?php echo __( 'Show only selected tables', 'wp-data-access' ); ?>
							</label>
							<div id="tables_selected" <?php echo 'select' === $table_access ? '' : 'style="display:none"'; ?>>
								<br/>
								<select name="table_access_selected[]" multiple size="10">
									<?php
									$tables = WPDA_Dictionary_Lists::get_tables();
									foreach ( $tables as $table ) {
										$table_name = $table['table_name'];
										?>
										<option value="<?php echo esc_attr( $table_name ); ?>" <?php echo isset( $table_access_selected_by_name[ $table_name ] ) ? 'selected' : ''; ?>><?php echo esc_attr( $table_name ); ?></option>
										<?php
									}
									?>
								</select>
							</div>
							<script language="JavaScript">
								jQuery(document).ready(function () {
									jQuery("input[name='table_access']").on("click", function () {
										if (this.value == 'select') {
											jQuery("#tables_selected").show();
										} else {
											jQuery("#tables_selected").hide();
										}
									});
								});
							</script>
						</td>
					</tr>
				</table>
				<div class="wpda-table-settings-button">
					<input type="hidden" name="action" value="save"/>
					<input type="submit"
						   value="<?php echo __( 'Save Front-end Settings', 'wp-data-access' ); ?>"
						   class="button button-primary"/>
					<a href="javascript:void(0)"
					   onclick="if (confirm('<?php echo __( 'Reset to defaults?', 'wp-data-access' ); ?>')) {
							   jQuery('input[name=&quot;action&quot;]').val('setdefaults');
							   jQuery('#wpda_settings_frontend').trigger('submit')
							   }"
					   class="button">
						<?php echo __( 'Reset Front-end Settings To Defaults', 'wp-data-access' ); ?>
					</a>
				</div>
				<?php wp_nonce_field( 'wpda-front-end-settings', '_wpnonce', false ); ?>
			</form>

			<?php

		}

		/**
		 * Add uninstall tab content
		 *
		 * See class documentation for flow explanation.
		 *
		 * @since   1.0.0
		 */
		protected function add_content_uninstall() {

			if ( isset( $_REQUEST['action'] ) ) {
				$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // input var okay.

				// Security check.
				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'wpda-uninstall-settings' ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				if ( 'save' === $action ) {

					// Save changes.
					WPDA::set_option(
						WPDA::OPTION_WPDA_UNINSTALL_TABLES,
						isset( $_REQUEST['delete_tables'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['delete_tables'] ) ) : 'off' // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_WPDA_UNINSTALL_OPTIONS,
						isset( $_REQUEST['delete_options'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['delete_options'] ) ) : 'off' // input var okay.
					);

				} elseif ( 'setdefaults' === $action ) {

					// Set back to default values.
					WPDA::set_option( WPDA::OPTION_WPDA_UNINSTALL_TABLES );
					WPDA::set_option( WPDA::OPTION_WPDA_UNINSTALL_OPTIONS );

				}

				$msg = new WPDA_Message_Box(
					[
						'message_text' => __( 'Settings saved', 'wp-data-access' ),
					]
				);
				$msg->box();

			}

			$delete_tables  = WPDA::get_option( WPDA::OPTION_WPDA_UNINSTALL_TABLES );
			$delete_options = WPDA::get_option( WPDA::OPTION_WPDA_UNINSTALL_OPTIONS );

			?>

			<iframe id="stealth_mode" style="display:none"></iframe>
			<form id="wpda_settings_uninstall" method="post"
				  action="?page=<?php echo esc_attr( $this->page ); ?>&tab=uninstall">
				<table class="wpda-table-settings">
					<tr>
						<th>
							<?php echo __( 'On plugin uninstall', 'wp-data-access' ); ?>
						</th>
						<td>
							<label>
								<input type="checkbox" name="delete_plugin" style="margin-right: 0" checked
									   disabled="disabled">
								<?php echo __( 'Delete plugin', 'wp-data-access' ); ?>
							</label>
							<br/>
							<label>
								<input type="checkbox" name="delete_tables"
									   style="margin-right: 0" <?php echo 'on' === $delete_tables ? 'checked' : ''; ?>>
								<?php echo __( 'Delete plugin tables (all data will be lost)', 'wp-data-access' ); ?>
							</label>
							<br/>
							<label>
								<input type="checkbox" name="delete_options"
									   style="margin-right: 0" <?php echo 'on' === $delete_options ? 'checked' : ''; ?>>
								<?php echo __( 'Delete plugin settings (all settings will be lost)', 'wp-data-access' ); ?>
							</label>
						</td>
					</tr>
				</table>
				<div class="wpda-table-settings-button">
					<input type="hidden" name="action" value="save"/>
					<input type="submit"
						   value="<?php echo __( 'Save Uninstall Settings', 'wp-data-access' ); ?>"
						   class="button button-primary"/>
					<a href="javascript:void(0)"
					   onclick="if (confirm('<?php echo __( 'Reset to defaults?', 'wp-data-access' ); ?>')) {
							   jQuery('input[name=\'action\']').val('setdefaults');
							   jQuery('#wpda_settings_uninstall').trigger('submit');
							   }"
					   class="button button-secondary">
						<?php echo __( 'Reset Uninstall Settings To Defaults', 'wp-data-access' ); ?>
					</a>
				</div>
				<?php wp_nonce_field( 'wpda-uninstall-settings', '_wpnonce', false ); ?>
			</form>

			<?php

		}

		/**
		 * Add repository tab content
		 *
		 * See class documentation for flow explanation.
		 *
		 * @since   1.0.0
		 */
		protected function add_content_repository() {

			global $wpdb;

			if ( isset( $_REQUEST['action'] ) ) {
				$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // input var okay.

				// Security check.
				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'wpda-repository-settings' ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				if ( 'save' === $action ) {
					// Save changes.
					WPDA::set_option(
						WPDA::OPTION_MR_KEEP_BACKUP_TABLES,
						isset( $_REQUEST['keep_backup_tables'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['keep_backup_tables'] ) ) : 'off' // input var okay.
					);
				} elseif ( 'setdefaults' === $action ) {
					// Set back to default values.
					WPDA::set_option( WPDA::OPTION_MR_KEEP_BACKUP_TABLES );

				}

				$msg = new WPDA_Message_Box(
					[
						'message_text' => __( 'Settings saved', 'wp-data-access' ),
					]
				);
				$msg->box();

			}

			$keep_backup_tables = WPDA::get_option( WPDA::OPTION_MR_KEEP_BACKUP_TABLES );

			// Check table wp_wpda_menu_items.
			$table_name            = WPDA_User_Menu_Model::get_menu_table_name();
			$trigger_before_insert = $wpdb->prefix . WPDA::get_option( WPDA::OPTION_WPDA_PREFIX ) . 'menu_items_before_insert';
			$trigger_before_update = $wpdb->prefix . WPDA::get_option( WPDA::OPTION_WPDA_PREFIX ) . 'menu_items_before_update';

			$table_menu_items_exists                  = WPDA_User_Menu_Model::table_exists();
			$triggers_menu_items_before_insert_exists = WPDA_User_Menu_Model::triggers_menu_items_before_insert_exists();
			$triggers_menu_items_before_update_exists = WPDA_User_Menu_Model::triggers_menu_items_before_update_exists();

			$no_menu_items = 0;
			if ( $table_menu_items_exists ) {
				$no_menu_items = WPDA_User_Menu_Model::count_menu_items_stored();
			}

			// Check table wp_wpda_table_design.
			$design_table_name        = WPDA_Design_Table_Model::get_design_table_name();
			$design_table_name_exists = WPDA_Design_Table_Model::table_exists();
			$no_table_designs         = 0;
			if ( $design_table_name_exists ) {
				$no_table_designs = WPDA_Design_Table_Model::count_table_designs_stored();
			}

			// Check table wp_wpda_logging
			$logging_table_name    = $wpdb->prefix . WPDA::get_option( WPDA::OPTION_WPDA_PREFIX ) . 'logging';
			$wpda_dictionary_exist = new WPDA_Dictionary_Exist( '', $logging_table_name );
			$logging_table_exists  = $wpda_dictionary_exist->table_exists( false );
			$no_logs               = 0;
			if ( $logging_table_exists ) {
				$query = "select count(*) from $logging_table_name";
				$rows  = $wpdb->get_results( $query, 'ARRAY_N' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
				if ( 1 === $wpdb->num_rows && isset( $rows[0][0] ) ) {
					$no_logs = $rows[0][0];
				} else {
					$no_logs = '?';
				}
			}

			// Check data projects tables.
			$data_projects_project_name        = $wpdb->prefix . 'wpdp_project';
			$wpda_dictionary_exist             = new WPDA_Dictionary_Exist( '', $data_projects_project_name );
			$data_projects_project_name_exists = $wpda_dictionary_exist->table_exists( false );
			$no_projects                       = 0;
			if ( $data_projects_project_name_exists ) {
				$query = "select count(*) from $data_projects_project_name";
				$rows  = $wpdb->get_results( $query, 'ARRAY_N' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
				if ( 1 === $wpdb->num_rows && isset( $rows[0][0] ) ) {
					$no_projects = $rows[0][0];
				} else {
					$no_projects = '?';
				}
			}

			$data_projects_page_name        = $wpdb->prefix . 'wpdp_page';
			$wpda_dictionary_exist          = new WPDA_Dictionary_Exist( '', $data_projects_page_name );
			$data_projects_page_name_exists = $wpda_dictionary_exist->table_exists( false );
			$no_pages                       = 0;
			if ( $data_projects_page_name_exists ) {
				$query = "select count(*) from $data_projects_page_name";
				$rows  = $wpdb->get_results( $query, 'ARRAY_N' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
				if ( 1 === $wpdb->num_rows && isset( $rows[0][0] ) ) {
					$no_pages = $rows[0][0];
				} else {
					$no_pages = '?';
				}
			}

			$data_projects_table_name        = WPDP_Project_Design_Table_Model::get_design_table_name();
			$data_projects_table_name_exists = WPDP_Project_Design_Table_Model::table_exists();
			if ( $data_projects_table_name_exists ) {
				$no_project_table_designs = WPDP_Project_Design_Table_Model::count_table_designs_stored();
			} else {
				$no_project_table_designs = 0;
			}

			$data_publication_table_name = WPDA_Publisher_Model::get_table_name();
			$data_publication_table_name_exists = WPDA_Publisher_Model::table_exists();
			if ( $data_publication_table_name_exists ) {
				$no_data_publication = WPDA_Publisher_Model::count();
			} else {
				$no_data_publication = 0;
			}

			$table     = __( 'Table', 'wp-data-access' );
			$trigger   = __( 'Trigger', 'wp-data-access' );
			$found     = __( 'found', 'wp-data-access' );
			$not_found = __( 'not found', 'wp-data-access' );

			// Count old backup tables
			$bck_postfix      = '_BACKUP_';
			$query            = "select table_name from information_schema.tables " .
			                    "where table_schema = '{$wpdb->dbname}' " .
			                    " and ( table_name like '$table_name{$bck_postfix}%' " .
			                    " or table_name like '$design_table_name{$bck_postfix}%' " .
			                    " or table_name like '$logging_table_name{$bck_postfix}%' " .
			                    " or table_name like '$data_publication_table_name{$bck_postfix}%' " .
			                    " or table_name like '$data_projects_project_name{$bck_postfix}%' " .
			                    " or table_name like '$data_projects_page_name{$bck_postfix}%' " .
			                    " or table_name like '$data_projects_table_name{$bck_postfix}%' )";
			$backup_tables    = $wpdb->get_results( $query, 'ARRAY_N' );
			$no_backup_tables = $wpdb->num_rows;

			if ( isset( $_REQUEST['remove_backup'] ) && 'true' === $_REQUEST['remove_backup'] ) {
				// Security check
				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'wpda-settings-remove_backup-repository' ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				// Remove old backup tables
				foreach ( $backup_tables as $backup_table ) {
					$wpdb->query( "drop table {$backup_table[0]}" );
				}

				// Count backup tables again...
				$query = "select table_name from information_schema.tables " .
				         "where table_schema = '{$wpdb->dbname}' " .
				         " and ( table_name like '$table_name{$bck_postfix}%' " .
				         " or table_name like '$design_table_name{$bck_postfix}%' " .
				         " or table_name like '$logging_table_name{$bck_postfix}%' " .
				         " or table_name like '$data_publication_table_name{$bck_postfix}%' " .
				         " or table_name like '$data_projects_project_name{$bck_postfix}%' " .
				         " or table_name like '$data_projects_page_name{$bck_postfix}%' " .
				         " or table_name like '$data_projects_table_name{$bck_postfix}%' )";
				$wpdb->get_results( $query, 'ARRAY_N' );
				$no_backup_tables = $wpdb->num_rows;

				$msg = new WPDA_Message_Box(
					[
						'message_text' => __( 'Old backup tables dropped', 'wp-data-access' ),
					]
				);
				$msg->box();
			}

			?>

			<iframe id="stealth_mode" style="display:none"></iframe>
			<form id="wpda_settings_repository" method="post"
				  action="?page=<?php echo esc_attr( $this->page ); ?>&tab=repository">
				<table class="wpda-table-settings">
					<tr>
						<th>
							<?php echo __( 'On Plugin Update', 'wp-data-access' ); ?>
						</th>
						<td>
							<label>
								<input type="checkbox" name="keep_backup_tables"
									   style="margin-right: 0" <?php echo 'on' === $keep_backup_tables ? 'checked' : ''; ?>>
								<?php echo __( 'Keep backup of repository tables', 'wp-data-access' ); ?>
							</label>
							<br/><br/>
							<?php
							$wpnonce_remove_backup = wp_create_nonce( 'wpda-settings-remove_backup-repository' );
							?>
							<a href="?page=<?php echo esc_attr( $this->page ); ?>&tab=repository&remove_backup=true&_wpnonce=<?php echo esc_attr( $wpnonce_remove_backup ); ?>"
							   class="button <?php echo 0 === $no_backup_tables ? 'disabled' : ''; ?>">
								<?php echo __( 'Remove' ) . ' ' . esc_html( $no_backup_tables ) . ' ' . __( 'old backup tables' ); ?>
							</a>
						</td>
					</tr>
					<tr>
						<th>
							<?php echo __( 'Data Menus', 'wp-data-access' ); ?>
						</th>
						<td>
							<span class="dashicons <?php echo $table_menu_items_exists ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
							<?php echo esc_attr( $table ); ?> <strong><?php echo esc_attr( $table_name ); ?></strong>
							<?php echo $table_menu_items_exists ? esc_attr( $found ) : esc_attr( $not_found ); ?>
							<br/>
							<span class="dashicons <?php echo $triggers_menu_items_before_insert_exists ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
							<?php echo esc_attr( $trigger ); ?>
							<strong><?php echo esc_attr( $trigger_before_insert ); ?></strong>
							<?php echo $triggers_menu_items_before_insert_exists ? esc_attr( $found ) : esc_attr( $not_found ); ?>
							<br/>
							<span class="dashicons <?php echo $triggers_menu_items_before_update_exists ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
							<?php echo esc_attr( $trigger ); ?>
							<strong><?php echo esc_attr( $trigger_before_update ); ?></strong>
							<?php echo $triggers_menu_items_before_update_exists ? esc_attr( $found ) : esc_attr( $not_found ); ?>
							<?php
							if ( $table_menu_items_exists ) {
								?>
								<br/><br/>
								<span class="dashicons dashicons-yes"></span>
								<strong>
									<?php echo esc_attr( $no_menu_items ); ?>
									<?php echo __( 'menu items in repository', 'wp-data-access' ); ?>
								</strong>
								<?php
							}
							?>
						</td>
					</tr>
					<tr>
						<th>
							<?php echo __( 'Data Designer', 'wp-data-access' ); ?>
						</th>
						<td>
							<span class="dashicons <?php echo $design_table_name_exists ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
							<?php echo esc_attr( $table ); ?>
							<strong><?php echo esc_attr( $design_table_name ); ?></strong>
							<?php echo $design_table_name_exists ? esc_attr( $found ) : esc_attr( $not_found ); ?>
							<?php
							if ( $design_table_name_exists ) {
								?>
								<br/><br/>
								<span class="dashicons dashicons-yes"></span>
								<strong>
									<?php echo esc_attr( $no_table_designs ); ?>
									<?php echo __( 'table designs in repository', 'wp-data-access' ); ?>
								</strong>
								<?php
							}
							?>
						</td>
					</tr>
					<tr>
						<th>
							<?php echo __( 'Data Logging', 'wp-data-access' ); ?>
						</th>
						<td>
							<span class="dashicons <?php echo $logging_table_exists ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
							<?php echo esc_attr( $table ); ?>
							<strong><?php echo esc_attr( $logging_table_name ); ?></strong>
							<?php echo $logging_table_exists ? esc_attr( $found ) : esc_attr( $not_found ); ?>
							<?php
							if ( $logging_table_exists ) {
								?>
								<br/><br/>
								<span class="dashicons dashicons-yes"></span>
								<strong>
									<?php echo esc_attr( $no_logs ); ?>
									<?php echo __( 'logging rows in repository', 'wp-data-access' ); ?>
								</strong>
								<?php
							}
							?>
						</td>
					</tr>
					<tr>
						<th>
							<?php echo __( 'Data Projects', 'wp-data-access' ); ?>
						</th>
						<td>
							<span class="dashicons <?php echo $design_table_name_exists ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
							<?php echo esc_attr( $table ); ?>
							<strong><?php echo esc_attr( $data_projects_project_name ); ?></strong>
							<?php echo $design_table_name_exists ? esc_attr( $found ) : esc_attr( $not_found ); ?>
							<br/>
							<span class="dashicons <?php echo $design_table_name_exists ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
							<?php echo esc_attr( $table ); ?>
							<strong><?php echo esc_attr( $data_projects_page_name ); ?></strong>
							<?php echo $design_table_name_exists ? esc_attr( $found ) : esc_attr( $not_found ); ?>
							<br/>
							<span class="dashicons <?php echo $design_table_name_exists ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
							<?php echo esc_attr( $table ); ?>
							<strong><?php echo esc_attr( $data_projects_table_name ); ?></strong>
							<?php echo $design_table_name_exists ? esc_attr( $found ) : esc_attr( $not_found ); ?>
							<br/>
							<?php
							if ( $data_projects_project_name_exists ) {
								?>
								<br/>
								<span class="dashicons dashicons-yes"></span>
								<strong>
									<?php echo esc_attr( $no_projects ); ?>
									<?php echo __( 'data projects in repository', 'wp-data-access' ); ?>
								</strong>
								<?php
							}
							if ( $data_projects_page_name_exists ) {
								?>
								<br/>
								<span class="dashicons dashicons-yes"></span>
								<strong>
									<?php echo esc_attr( $no_pages ); ?>
									<?php echo __( 'project pages in repository', 'wp-data-access' ); ?>
								</strong>
								<?php
							}
							if ( $data_projects_table_name_exists ) {
								?>
								<br/>
								<span class="dashicons dashicons-yes"></span>
								<strong>
									<?php echo esc_attr( $no_project_table_designs ); ?>
									<?php echo __( 'project tables in repository', 'wp-data-access' ); ?>
								</strong>
								<?php
							}
							?>
						</td>
					</tr>

					<tr>
						<th>
							<?php echo __( 'Data Publisher', 'wp-data-access' ); ?>
						</th>
						<td>
							<span class="dashicons <?php echo $data_publication_table_name_exists ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
							<?php echo esc_attr( $table ); ?>
							<strong><?php echo esc_attr( $data_publication_table_name ); ?></strong>
							<?php echo $data_publication_table_name_exists ? esc_attr( $found ) : esc_attr( $not_found ); ?>
							<?php
							if ( $data_publication_table_name_exists ) {
								?>
								<br/><br/>
								<span class="dashicons dashicons-yes"></span>
								<strong>
									<?php echo esc_attr( $no_data_publication ); ?>
									<?php echo __( 'publication in repository', 'wp-data-access' ); ?>
								</strong>
								<?php
							}
							?>
						</td>
					</tr>

				</table>
				<div class="wpda-table-settings-button">
					<input type="hidden" name="action" value="save"/>
					<input type="submit"
						   value="<?php echo __( 'Save Manage Respository Settings', 'wp-data-access' ); ?>"
						   class="button button-primary"/>
					<a href="javascript:void(0)"
					   onclick="if (confirm('<?php echo __( 'Reset to defaults?', 'wp-data-access' ); ?>')) {
							   jQuery('input[name=\'action\']').val('setdefaults');
							   jQuery('#wpda_settings_repository').trigger('submit');
							   }"
					   class="button button-secondary">
						<?php echo __( 'Reset Manage Repository Settings To Defaults', 'wp-data-access' ); ?>
					</a>
					<?php
					$wpnonce_recreate = wp_create_nonce( 'wpda-settings-recreate-repository' );
					?>
					<a href="?page=<?php echo esc_attr( $this->page ); ?>&tab=repository&repos=true&_wpnonce=<?php echo esc_attr( $wpnonce_recreate ); ?>"
					   class="button button-secondary">
						<?php echo __( 'Recreate', 'wp-data-access' ); ?> WP Data Access
						<?php echo __( 'Repository', 'wp-data-access' ); ?>
					</a>
				</div>
				<?php wp_nonce_field( 'wpda-repository-settings', '_wpnonce', false ); ?>
			</form>

			<div class="wpda-table-settings-button">

				<?php

				$repository_valid = true;

				// Check if repository should be recreated.
				if (
					! $table_menu_items_exists ||
					! $design_table_name_exists ||
					! $data_projects_project_name_exists ||
					! $data_projects_page_name_exists ||
					! $data_projects_table_name_exists ||
					! $data_publication_table_name_exists
				) {
					?>
					<p><strong><?php echo __( 'Your repository has errors!', 'wp-data-access' ); ?></strong></p>
					<p>
						<?php echo __( 'Recreate the WP Data Access repository to solve this problem.', 'wp-data-access' ); ?>
						<?php echo __( 'Please leave your comments on the support forum if the problem remains.', 'wp-data-access' ); ?>
						(<a href="https://wordpress.org/support/plugin/wp-data-access/" target="_blank">go to forum</a>)
					</p>
					<?php

					$repository_valid = false;
				} elseif ( ! $triggers_menu_items_before_insert_exists || ! $triggers_menu_items_before_update_exists ) {
					?>
					<p>
						<strong><?php echo __( 'Your repository is available with limitations!', 'wp-data-access' ); ?></strong>
					</p>
					<p>
						<?php echo __( 'Data menu management is available, but without consistency checks.', 'wp-data-access' ); ?>
					</p>
					<?php
				}

				?>

				<?php

				?>

			</div>

			<?php

		}

		/**
		 * Add back-end tab content
		 *
		 * See class documentation for flow explanation.
		 *
		 * @since   1.0.0
		 */
		protected function add_content_backend() {

			if ( isset( $_REQUEST['action'] ) ) {
				$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // input var okay.

				// Security check.
				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'wpda-back-end-settings' ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				if ( 'save' === $action ) {
					// Save options.
					WPDA::set_option(
						WPDA::OPTION_BE_TABLE_ACCESS,
						isset( $_REQUEST['table_access'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['table_access'] ) ) : null // input var okay.
					);

					$table_access_selected_new_value = isset( $_REQUEST['table_access_selected'] ) ? $_REQUEST['table_access_selected'] : null;
					if ( is_array( $table_access_selected_new_value ) ) {
						// Check the requested table names for sql injection. This is simply done by checking if the table
						// name exists in our WordPress database.
						$table_access_selected_new_value_checked = [];
						foreach ( $table_access_selected_new_value as $key => $value ) {
							$wpda_dictionary_checks = new WPDA_Dictionary_Exist( '', $value );
							if ( $wpda_dictionary_checks->table_exists( false ) ) {
								// Add existing table to list.
								$table_access_selected_new_value_checked[ $key ] = $value;
							} else {
								// An invalid table name was provided. Might be an sql injection attack or an invalid state.
								wp_die( __( 'ERROR: Invalid table name', 'wpda_main' ) );
							}
						}
					} else {
						$table_access_selected_new_value_checked = '';
					}
					WPDA::set_option(
						WPDA::OPTION_BE_TABLE_ACCESS_SELECTED,
						$table_access_selected_new_value_checked
					);

					WPDA::set_option(
						WPDA::OPTION_BE_VIEW_LINK,
						isset( $_REQUEST['view_link'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['view_link'] ) ) : 'off' // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_ALLOW_INSERT,
						isset( $_REQUEST['allow_insert'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['allow_insert'] ) ) : 'off' // input var okay.
					);
					WPDA::set_option(
						WPDA::OPTION_BE_ALLOW_UPDATE,
						isset( $_REQUEST['allow_update'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['allow_update'] ) ) : 'off' // input var okay.
					);
					WPDA::set_option(
						WPDA::OPTION_BE_ALLOW_DELETE,
						isset( $_REQUEST['allow_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['allow_delete'] ) ) : 'off' // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_EXPORT_ROWS,
						isset( $_REQUEST['export_rows'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['export_rows'] ) ) : 'off' // input var okay.
					);
					WPDA::set_option(
						WPDA::OPTION_BE_EXPORT_VARIABLE_PREFIX,
						isset( $_REQUEST['export_variable_rows'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['export_variable_rows'] ) ) : 'off' // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_ALLOW_IMPORTS,
						isset( $_REQUEST['allow_imports'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['allow_imports'] ) ) : 'off' // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_CONFIRM_EXPORT,
						isset( $_REQUEST['confirm_export'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['confirm_export'] ) ) : 'off' // input var okay.
					);
					WPDA::set_option(
						WPDA::OPTION_BE_CONFIRM_VIEW,
						isset( $_REQUEST['confirm_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['confirm_view'] ) ) : 'off' // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_PAGINATION,
						isset( $_REQUEST['pagination'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['pagination'] ) ) : null // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_REMEMBER_SEARCH,
						isset( $_REQUEST['remember_search'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['remember_search'] ) ) : 'off' // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_INNODB_COUNT,
						isset( $_REQUEST['innodb_count'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['innodb_count'] ) ) : 100000 // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_DESIGN_MODE,
						isset( $_REQUEST['design_mode'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['design_mode'] ) ) : 'basic' // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_TEXT_WRAP_SWITCH,
						isset( $_REQUEST['text_wrap_switch'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['text_wrap_switch'] ) ) : 'off' // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_TEXT_WRAP,
						isset( $_REQUEST['text_wrap'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['text_wrap'] ) ) : 400 // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_DEBUG,
						isset( $_REQUEST['debug'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['debug'] ) ) : 'off' // input var okay.
					);
				} elseif ( 'setdefaults' === $action ) {
					// Set all back-end settings back to default.
					WPDA::set_option( WPDA::OPTION_BE_TABLE_ACCESS );
					WPDA::set_option( WPDA::OPTION_BE_TABLE_ACCESS_SELECTED );
					WPDA::set_option( WPDA::OPTION_BE_VIEW_LINK );
					WPDA::set_option( WPDA::OPTION_BE_ALLOW_INSERT );
					WPDA::set_option( WPDA::OPTION_BE_ALLOW_UPDATE );
					WPDA::set_option( WPDA::OPTION_BE_ALLOW_DELETE );
					WPDA::set_option( WPDA::OPTION_BE_EXPORT_ROWS );
					WPDA::set_option( WPDA::OPTION_BE_EXPORT_VARIABLE_PREFIX );
					WPDA::set_option( WPDA::OPTION_BE_ALLOW_IMPORTS );
					WPDA::set_option( WPDA::OPTION_BE_CONFIRM_EXPORT );
					WPDA::set_option( WPDA::OPTION_BE_CONFIRM_VIEW );
					WPDA::set_option( WPDA::OPTION_BE_PAGINATION );
					WPDA::set_option( WPDA::OPTION_BE_REMEMBER_SEARCH );
					WPDA::set_option( WPDA::OPTION_BE_INNODB_COUNT );
					WPDA::set_option( WPDA::OPTION_BE_DESIGN_MODE );
					WPDA::set_option( WPDA::OPTION_BE_TEXT_WRAP_SWITCH );
					WPDA::set_option( WPDA::OPTION_BE_TEXT_WRAP );
					WPDA::set_option( WPDA::OPTION_BE_DEBUG );
				}

				$msg = new WPDA_Message_Box(
					[
						'message_text' => __( 'Settings saved', 'wp-data-access' ),
					]
				);
				$msg->box();
			}

			// Get options.
			$table_access          = WPDA::get_option( WPDA::OPTION_BE_TABLE_ACCESS );
			$table_access_selected = WPDA::get_option( WPDA::OPTION_BE_TABLE_ACCESS_SELECTED );

			if ( is_array( $table_access_selected ) ) {
				// Convert table for simple access.
				$table_access_selected_by_name = [];
				foreach ( $table_access_selected as $key => $value ) {
					$table_access_selected_by_name[ $value ] = true;
				}
			}

			$view_link = WPDA::get_option( WPDA::OPTION_BE_VIEW_LINK );

			$allow_insert = WPDA::get_option( WPDA::OPTION_BE_ALLOW_INSERT );
			$allow_update = WPDA::get_option( WPDA::OPTION_BE_ALLOW_UPDATE );
			$allow_delete = WPDA::get_option( WPDA::OPTION_BE_ALLOW_DELETE );

			$export_rows          = WPDA::get_option( WPDA::OPTION_BE_EXPORT_ROWS );
			$export_variable_rows = WPDA::get_option( WPDA::OPTION_BE_EXPORT_VARIABLE_PREFIX );

			$allow_imports = WPDA::get_option( WPDA::OPTION_BE_ALLOW_IMPORTS );

			$confirm_export = WPDA::get_option( WPDA::OPTION_BE_CONFIRM_EXPORT );
			$confirm_view   = WPDA::get_option( WPDA::OPTION_BE_CONFIRM_VIEW );

			$pagination = WPDA::get_option( WPDA::OPTION_BE_PAGINATION );

			$remember_search = WPDA::get_option( WPDA::OPTION_BE_REMEMBER_SEARCH );

			$innodb_count = WPDA::get_option( WPDA::OPTION_BE_INNODB_COUNT );

			$design_mode = WPDA::get_option( WPDA::OPTION_BE_DESIGN_MODE );

			$text_wrap_switch = WPDA::get_option( WPDA::OPTION_BE_TEXT_WRAP_SWITCH );
			$text_wrap        = WPDA::get_option( WPDA::OPTION_BE_TEXT_WRAP );

			$debug = WPDA::get_option( WPDA::OPTION_BE_DEBUG );

			?>

			<form id="wpda_settings_backend" method="post"
				  action="?page=<?php echo esc_attr( $this->page ); ?>&tab=backend">
				<table class="wpda-table-settings">
					<tr>
						<th><?php echo __( 'Table access', 'wp-data-access' ); ?></th>
						<td>
							<label>
								<input
										type="radio"
										name="table_access"
										value="show"
									<?php echo 'show' === $table_access ? 'checked' : ''; ?>
								><?php echo __( 'Show WordPress tables', 'wp-data-access' ); ?>
							</label>
							<br/>
							<label>
								<input
										type="radio"
										name="table_access"
										value="hide"
									<?php echo 'hide' === $table_access ? 'checked' : ''; ?>
								><?php echo __( 'Hide WordPress tables', 'wp-data-access' ); ?>
							</label>
							<br/>
							<label>
								<input
										type="radio"
										name="table_access"
										value="select"
									<?php echo 'select' === $table_access ? 'checked' : ''; ?>
								><?php echo __( 'Show only selected tables', 'wp-data-access' ); ?>
							</label>
							<div id="tables_selected" <?php echo 'select' === $table_access ? '' : 'style="display:none"'; ?>>
								<br/>
								<select name="table_access_selected[]" multiple size="10">
									<?php
									$tables = WPDA_Dictionary_Lists::get_tables();
									foreach ( $tables as $table ) {
										$table_name = $table['table_name'];
										?>
										<option value="<?php echo esc_attr( $table_name ); ?>" <?php echo isset( $table_access_selected_by_name[ $table_name ] ) ? 'selected' : ''; ?>><?php echo esc_attr( $table_name ); ?></option>
										<?php
									}
									?>
								</select>
							</div>
							<script language="JavaScript">
								jQuery(document).ready(function () {
									jQuery("input[name='table_access']").on("click", function () {
										if (this.value == 'select') {
											jQuery("#tables_selected").show();
										} else {
											jQuery("#tables_selected").hide();
										}
									});
								});
							</script>
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Row access', 'wp-data-access' ); ?></th>
						<td>
							<label>
								<input
										type="checkbox"
										name="view_link"
									<?php echo 'on' === $view_link ? 'checked' : ''; ?>
								><?php echo __( 'Add view link to list table', 'wp-data-access' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo __( 'Allow transactions?', 'wp-data-access' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="allow_insert"
									<?php echo 'on' === $allow_insert ? 'checked' : ''; ?> /><?php echo __( 'Allow insert', 'wp-data-access' ); ?>
							</label>
							<br/>
							<label>
								<input type="checkbox" name="allow_update"
									<?php echo 'on' === $allow_update ? 'checked' : ''; ?> /><?php echo __( 'Allow update', 'wp-data-access' ); ?>
							</label>
							<br/>
							<label>
								<input type="checkbox" name="allow_delete"
									<?php echo 'on' === $allow_delete ? 'checked' : ''; ?> /><?php echo __( 'Allow delete', 'wp-data-access' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo __( 'Allow exports?', 'wp-data-access' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="export_rows"
									<?php echo 'on' === $export_rows ? 'checked' : ''; ?> /><?php echo __( 'Allow row export', 'wp-data-access' ); ?>
							</label>
							<br/>
							<label>
								<input type="checkbox" name="export_variable_rows"
									<?php echo 'on' === $export_variable_rows ? 'checked' : ''; ?> /><?php echo __( 'Export with variable WP prefix', 'wp-data-access' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo __( 'Allow imports?', 'wp-data-access' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="allow_imports"
									<?php echo 'on' === $allow_imports ? 'checked' : ''; ?> /><?php echo __( 'Allow to import scripts from Data Explorer table pages', 'wp-data-access' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Ask for confirmation?', 'wp-data-access' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="confirm_export"
									<?php echo 'on' === $confirm_export ? 'checked' : ''; ?> /><?php echo __( 'When starting export', 'wp-data-access' ); ?>
							</label>
							<br/>
							<label>
								<input type="checkbox" name="confirm_view"
									<?php echo 'on' === $confirm_view ? 'checked' : ''; ?> /><?php echo __( 'When viewing non WPDA table', 'wp-data-access' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Default pagination value', 'wp-data-access' ); ?></th>
						<td>
							<input
									type="number" step="1" min="1" max="999" name="pagination" maxlength="3"
									value="<?php echo esc_attr( $pagination ); ?>">
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Search box', 'wp-data-access' ); ?></th>
						<td>
							<label>
								<input
										type="checkbox"
										name="remember_search" <?php echo 'on' === $remember_search ? 'checked' : ''; ?>
								><?php echo __( 'Remember last search', 'wp-data-access' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Max InnoDB row count', 'wp-data-access' ); ?></th>
						<td>
							<input
									type="number" step="1" min="1" max="999999" name="innodb_count" maxlength="3"
									value="<?php echo esc_attr( $innodb_count ); ?>">
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Default designer mode', 'wp-data-access' ); ?></th>
						<td>
							<select name="design_mode">
								<option value="basic" <?php echo 'basic' === $design_mode ? 'selected' : ''; ?>>Basic
								</option>
								<option value="advanced" <?php echo 'advanced' === $design_mode ? 'selected' : ''; ?>>
									Advanced
								</option>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Content wrap', 'wp-data-access' ); ?></th>
						<td>
							<label>
								<input
										type="checkbox"
										name="text_wrap_switch" <?php echo 'on' === $text_wrap_switch ? 'checked' : ''; ?>
								><?php echo __( 'No content wrap', 'wp-data-access' ); ?>
							</label>
							<br/>
							<input
									type="number" step="1" min="1" max="999999" name="text_wrap" maxlength="3"
									value="<?php echo esc_attr( $text_wrap ); ?>">
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Debug mode', 'wp-data-access' ); ?></th>
						<td>
							<label>
								<input
										type="checkbox"
										name="debug" <?php echo 'on' === $debug ? 'checked' : ''; ?>
								><?php echo __( 'Plugin dashboard debug mode', 'wp-data-access' ); ?>
							</label>
						</td>
					</tr>
				</table>
				<div class="wpda-table-settings-button">
					<input type="hidden" name="action" value="save"/>
					<input type="submit" value="<?php echo __( 'Save Back-end Settings', 'wp-data-access' ); ?>"
						   class="button button-primary"/>
					<a href="javascript:void(0)"
					   onclick="if (confirm('<?php echo __( 'Reset to defaults?', 'wp-data-access' ); ?>')) {
							   jQuery('input[name=&quot;action&quot;]').val('setdefaults');
							   jQuery('#wpda_settings_backend').trigger('submit')
							   }"
					   class="button">
						<?php echo __( 'Reset Back-end Settings To Defaults', 'wp-data-access' ); ?>
					</a>
				</div>
				<?php wp_nonce_field( 'wpda-back-end-settings', '_wpnonce', false ); ?>
			</form>

			<?php

		}

	}

}
