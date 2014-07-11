<?php
/**
 * Plugin Name:       One-Click Child Themes for Slocum Themes
 * Plugin URI:        http://slocumstudio.com/
 * Description:       Add "One-Click" Child Theme functionality to your Slocum Themes Options Panel.
 * Version:           1.0.2
 * Author:            Slocum Studio
 * Author URI:        http://slocumstudio.com/
 * Text Domain:       sds-occt
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/sdsweb/sds-one-click-child-themes/
 *
 * @package   SDS_One_Click_Child_Themes
 * @author    Slocum Studio
 * @license   GPL-2.0+
 * @link      http://slocumstudio.com/
 * @copyright 2014 Slocum Studio
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) )
	die;

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

/**
 * SDS_One_Click_Child_Themes Class File
 */
require_once( plugin_dir_path( __FILE__ ) . 'public/class-sds-one-click-child-themes.php' );

/**
 * Register hooks that are fired when the SDS_One_Click_Child_Themes is activated or deactivated.
 */
register_activation_hook( __FILE__, array( 'SDS_One_Click_Child_Themes', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'SDS_One_Click_Child_Themes', 'deactivate' ) );

/**
 * Create an instance of SDS_One_Click_Child_Themes
 */
add_action( 'plugins_loaded', array( 'SDS_One_Click_Child_Themes', 'get_instance' ) );


/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/**
 * SDS_One_Click_Child_Themes Admin Classes
 *
 * Create an instance of SDS_One_Click_Child_Themes_Admin
 * Create an instance of SDS_Themes
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	// Admin
	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-sds-one-click-child-themes-admin.php' );
	add_action( 'plugins_loaded', array( 'SDS_One_Click_Child_Themes_Admin', 'get_instance' ) );

	// SDS Themes (making sure the theme is setup before initializing this class)
	require_once( plugin_dir_path( __FILE__ ) . 'admin/includes/class-sds-themes.php' );
	add_action( 'after_setup_theme', array( 'SDS_Themes', 'get_instance' ) );
}


/*----------------------------------------------------------------------------*
 * Plugin Updates
 *----------------------------------------------------------------------------*/

/**
 * Plugin Updates
 */
define( 'SDS_OCCT_PLUGIN_FILE', __FILE__ ); // Reference to this plugin file (used in plugin updates)

if ( ! class_exists( 'PluginUpdateChecker' ) )
	require_once( plugin_dir_path( __FILE__ ) . 'includes/plugin-update-checker.php' );

require_once( plugin_dir_path( __FILE__ ) . 'includes/class-sds-one-click-child-themes-updates.php' );
add_action( 'plugins_loaded', array( 'SDS_One_Click_Child_Themes_Updates', 'get_puc_instance' ) );
add_action( 'plugins_loaded', array( 'SDS_One_Click_Child_Themes_Updates', 'get_instance' ) );