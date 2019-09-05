<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package plugin\public
 */

use WPDataAccess\Data_Tables\WPDA_Data_Tables;
use WPDataAccess\WPDA;

/**
 * Class WP_Data_Access_Public
 *
 * Defines public specific functionality for plugin WP Data Access.
 *
 * @author  Peter Schulz
 * @since   1.0.0
 */
class WP_Data_Access_Public {

	/**
	 * Add stylesheets to front-end
	 *
	 * The following stylesheets are added:
	 * + Bootstrap stylesheet (version is set in class WPDA)
	 * + jQuery DataTables stylesheet (version is set in class WPDA)
	 * + jQuery DataTables responsive stylesheet (version is set in class WPDA)
	 *
	 * Stylesheets are used to style the front-end tables. Whether stylesheets should be loaded or not can be set in
	 * the front-end settings (menu: Manage Plugin). Sites that already have some of these stylesheets loaded, can turn
	 * off loading in the front-end settings to prevent double loading.
	 *
	 * @since   1.0.0
	 *
	 * @see WPDA
	 */
	public function enqueue_styles() {
		if ( WPDA::get_option( WPDA::OPTION_FE_LOAD_DATATABLES ) === 'on' ) {
			// Load JQuery DataTables to support publication on website
			wp_register_style(
				'jquery_datatables', '//cdn.datatables.net/' .
				                     WPDA::get_option( WPDA::OPTION_WPDA_DATATABLES_VERSION ) .
				                     '/css/jquery.dataTables.min.css',
				[],
				WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
			);
			wp_enqueue_style( 'jquery_datatables' );
		}

		if ( WPDA::get_option( WPDA::OPTION_FE_LOAD_DATATABLES_RESPONSE ) === 'on' ) {
			// Load JQuery DataTables Responsive to support publication on website
			wp_register_style(
				'jquery_datatables_responsive',
				'//cdn.datatables.net/responsive/' .
				WPDA::get_option( WPDA::OPTION_WPDA_DATATABLES_RESPONSIVE_VERSION ) .
				'/css/responsive.dataTables.min.css',
				[],
				WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
			);
			wp_enqueue_style( 'jquery_datatables_responsive' );
		}

		if ( WPDA::get_option( WPDA::OPTION_FE_LOAD_BOOTSTRAP ) === 'on' ) {
			// Load Bootstrap to support publication on website
			wp_register_style(
				'prefix_bootstrap',
				'//maxcdn.bootstrapcdn.com/bootstrap/' .
				WPDA::get_option( WPDA::OPTION_WPDA_BOOTSTRAP_VERSION ) .
				'/css/bootstrap.min.css',
				[],
				WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
			);
			wp_enqueue_style( 'prefix_bootstrap' );
		}
	}

	/**
	 * Add scripts to back-end
	 *
	 * The following script files are added:
	 * + jQuery (just enqueue, registered by default)
	 * + Bootstrap (version is set in class WPDA)
	 * + jQuery DataTables (version is set in class WPDA)
	 * + jQuery DataTables responsive (version is set in class WPDA)
	 * + WP Data Access DataTables server implementation (ajax)
	 *
	 * Scripts are used to build front-end tables and support searching and pagination. Whether the scripts for
	 * Bootstrap, jQuery DataTables and/or jQuery DataTables responsice should be loaded or not can be set in the
	 * front-end settings (menu: Manage Plugin). Sites that already have some of these script files loaded, can
	 * turn off loading in the front-end settings to prevent double loading.
	 *
	 * @since   1.0.0
	 *
	 * @see WPDA
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'jquery' ); // Just enqueue: jquery is already registered.

		// Register purl external library.
		wp_register_script( 'purl', plugins_url( '../assets/js/purl.js', __FILE__ ), [ 'jquery' ] );
		wp_enqueue_script( 'purl' );

		if ( WPDA::get_option( WPDA::OPTION_FE_LOAD_DATATABLES ) === 'on' ) {
			// Load JQuery DataTables to support publication on website
			wp_register_script(
				'jquery_datatables',
				'//cdn.datatables.net/' .
				WPDA::get_option( WPDA::OPTION_WPDA_DATATABLES_VERSION ) .
				'/js/jquery.dataTables.min.js',
				[],
				WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
			);
			wp_enqueue_script( 'jquery_datatables' );
		}

		if ( WPDA::get_option( WPDA::OPTION_FE_LOAD_DATATABLES_RESPONSE ) === 'on' ) {
			// Load JQuery DataTables Responsive to support publication on website
			wp_register_script(
				'jquery_datatables_responsive',
				'//cdn.datatables.net/responsive/' .
				WPDA::get_option( WPDA::OPTION_WPDA_DATATABLES_RESPONSIVE_VERSION ) .
				'/js/dataTables.responsive.min.js',
				[],
				WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
			);
			wp_enqueue_script( 'jquery_datatables_responsive' );
		}

		if ( WPDA::get_option( WPDA::OPTION_FE_LOAD_BOOTSTRAP ) === 'on' ) {
			// Load Bootstrap to support publication on website
			wp_register_script(
				'prefix_bootstrap',
				'//maxcdn.bootstrapcdn.com/bootstrap/' .
				WPDA::get_option( WPDA::OPTION_WPDA_BOOTSTRAP_VERSION ) .
				'/js/bootstrap.min.js'
			);
			wp_enqueue_script( 'prefix_bootstrap' );
		}

		// Ajax call to WPDA datables implementation.
		$details      = __( 'Row details', 'wp-data-access' ); // Set title of modal window here to support i18n.
		$query_string = str_replace( ' ', '+', "?details=$details" );
		wp_register_script(
			'wpda_datatables',
			plugins_url( '../assets/js/wpda_datatables.js' . $query_string, __FILE__ ), [ 'jquery' ],
			[],
			WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
		);
		wp_localize_script( 'wpda_datatables', 'wpda_ajax', [ 'wpda_ajaxurl' => admin_url( 'admin-ajax.php' ) ] );
		wp_enqueue_script( 'wpda_datatables' );
	}

	/**
	 * Register shortcode 'wpdataaccess'
	 *
	 * @since   1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'wpdataaccess', [ $this, 'wpdataaccess' ] );
	}

	/**
	 * Implementation of shortcode 'wpdataaccess'
	 *
	 * Checks the values entered on validity (as far as possible) and builds the table based on the given table name,
	 * column names and other arguments. Tables is build with class {@see WPDA_Data_Tables}.
	 *
	 * @param array $atts Arguments applied with the shortcode.
	 *
	 * @return string response
	 *
	 * @see WPDA_Data_Tables
	 *
	 * @since   1.0.0
	 *
	 */
	public function wpdataaccess( $atts ) {
		$atts    = array_change_key_case( (array) $atts, CASE_LOWER );
		$wp_atts = shortcode_atts(
			[
				'pub_id'          => '',
				'table'           => '',
				'columns'         => '*',
				'responsive'      => 'no',
				'responsive_cols' => '0',           // > 1 or no effect.
				'responsive_type' => 'collapsed',   // modal,expanded,collapsed.
				'responsive_icon' => 'yes',         // yes,no.
			], $atts
		);

		$wpda_data_tables = new WPDA_Data_Tables();
		return
			$wpda_data_tables->show(
				$wp_atts['pub_id'],
				$wp_atts['table'],
				str_replace( ' ', '', $wp_atts['columns'] ),
				$wp_atts['responsive'],
				$wp_atts['responsive_cols'],
				$wp_atts['responsive_type'],
				$wp_atts['responsive_icon']
			);
	}

}
