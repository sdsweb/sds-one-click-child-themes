<?php
/**
 * SDS_One_Click_Child_Themes
 *
 * @package   SDS_One_Click_Child_Themes
 * @author    Slocum Studio
 * @license   GPL-2.0+
 * @link      http://slocumstudio.com/
 * @copyright 2014 Slocum Studio
 */

/**
 * SDS_One_Click_Child_Themes Admin Class File
 *
 * @package SDS_One_Click_Child_Themes
 * @author Slocum Studio
 */
class SDS_One_Click_Child_Themes_Admin {
	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * This is specified in the SDS Theme Options panel class within the theme.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = 'appearance_page_sds-theme-options';

	/**
	 * Initialize the plugin by loading admin assets.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		/**
		 * Call $plugin_slug and $is_multisite from public plugin class.
		 */
		$plugin = SDS_One_Click_Child_Themes::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();
		$this->is_multisite = $plugin->is_multisite;

		// Load admin stylesheet and JavaScript
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Add navigation tabs to the Theme Options panel
		add_action( 'sds_theme_options_navigation_tabs', array( $this, 'sds_theme_options_navigation_tabs' ) );

		// Add settings to the Theme Options panel
		add_action( 'sds_theme_options_settings', array( $this, 'sds_theme_options_settings' ) );

		// Sanitize/Validate settings on the Theme Options panel
		add_filter( 'sanitize_option_sds_theme_options', array( $this, 'sanitize_option_sds_theme_options' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific stylesheets and JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function admin_enqueue_scripts() {
		if ( ! isset( $this->plugin_screen_hook_suffix ) )
			return;

		// Only enqueue on the Theme Options panel
		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-sds-theme-options', plugins_url( 'assets/css/sds-theme-options.css', __FILE__ ), array(), SDS_One_Click_Child_Themes::VERSION );
			wp_enqueue_script( $this->plugin_slug . '-sds-theme-options', plugins_url( 'assets/js/sds-theme-options.js', __FILE__ ), array( 'jquery', 'sds-theme-options' ), SDS_One_Click_Child_Themes::VERSION );
			wp_localize_script( $this->plugin_slug . '-sds-theme-options', 'sds_theme_options', array(
				'is_multisite' => $this->is_multisite
			) );
		}
	}

	/**
	 * Add navigation tabs to the Theme Options panel.
	 *
	 * @since    1.0.0
	 * @return    null
	 */
	public function sds_theme_options_navigation_tabs() {
		include_once( 'views/sds-theme-options-navigation-tabs.php' );
	}

	/**
	 * Add settings to the Theme Options panel.
	 *
	 * @since    1.0.0
	 * @return    null
	 */
	public function sds_theme_options_settings() {
		include_once( 'views/sds-theme-options-settings.php' );
	}

	/**
	 * Sanitize/Validate settings on the Theme Options panel.
	 *
	 * @since    1.0.0
	 *
	 * @return   array    Filtered/Sanitized user input
	 */
	public function sanitize_option_sds_theme_options( $input ) {
		$sds_themes = SDS_Themes::get_instance();
		/**
		 * "One-Click" Child Themes
		 */

		// Create child theme
		if ( isset( $input['create_child_theme'] ) ) {
			$theme_root = trailingslashit( get_theme_root() ); // Theme directory
			$child_theme_name = ( isset( $input['child_theme']['name'] ) ) ? sanitize_title( $input['child_theme']['name'] ) : false; // Child theme directory name
			$child_theme_directory = trailingslashit( $theme_root . $child_theme_name ); // Child theme
			$child_theme_stylesheet_header = $sds_themes->create_stylesheet_header( sanitize_text_field( $input['child_theme']['name'] ) ); // Child theme stylesheet header

			// Make sure we have a valid theme name
			if ( empty( $child_theme_name ) )
				add_settings_error( 'sds_theme_options', 'sds_theme_options-child-theme-name-invalid', __( 'Please enter a valid child theme name.', $this->plugin_slug ) );
			// Make sure the requested child theme does not already exist
			else if ( file_exists( $theme_root . $child_theme_name ) )
				add_settings_error( 'sds_theme_options', 'sds_theme_options-child-theme-exists', __( 'It appears that a child theme with that name already exists. Please try again with a different child theme name.', $this->plugin_slug ) );
			// Make sure child theme creation didn't fail
			else if ( ! $sds_themes->create_child_theme( $child_theme_directory, $child_theme_stylesheet_header, $theme_root ) )
				add_settings_error( 'sds_theme_options', 'sds_theme_options-child-theme-creation-failed', __( 'There was a problem creating the child theme. Please check your server permissions.', $this->plugin_slug ) );
			// Child theme was created successfully
			else {
				// Multisite
				if ( $this->is_multisite ) {
					// Activate the child theme
					if ( isset( $input['child_theme']['activate'] ) ) {
						// Enable it across the network first
						if( $sds_themes->network_enable_child_theme( $child_theme_name ) ) {
							// Activate child theme
							if( $sds_themes->activate_child_theme( $child_theme_name ) )
								add_settings_error( 'sds_theme_options', 'sds_theme_options-child-theme-activation-success', sprintf( __( 'New theme activated. <a href="%s">Visit site</a> or <a href="%s">edit child theme</a>.', $this->plugin_slug ), home_url( '/' ), admin_url( 'theme-editor.php?theme=' . urlencode( $child_theme_name ) ) ), 'updated' );
							// Activation failed
							else
								add_settings_error( 'sds_theme_options', 'sds_theme_options-child-theme-activation-failed', sprintf( __( 'Child theme created successfully but there was a problem activating it. Please <a href="%s">try again</a>', $this->plugin_slug ), wp_nonce_url( admin_url( 'themes.php?action=activate&amp;stylesheet=' . urlencode( $child_theme_name ) ), 'switch-theme_' . $child_theme_name ) ), 'error' );
						}
						// Network enabling failed
						else
							add_settings_error( 'sds_theme_options', 'sds_theme_options-child-theme-network-enable-failed', __( 'Child theme created successfully but there was a problem enabling it on the network. Please contact your network administrator.', $this->plugin_slug ), 'error' );
					}
					// Enable theme across network
					else if ( isset( $input['child_theme']['network_enable'] ) ) {
						// Enable it across the network
						if( $sds_themes->network_enable_child_theme( $child_theme_name ) )
							add_settings_error( 'sds_theme_options', 'sds_theme_options-child-theme-network-enable-success', sprintf( __( 'Child theme created successfully and enabled across the network. <a href="%s">Activate</a> your child theme.', $this->plugin_slug ), wp_nonce_url( admin_url( 'themes.php?action=activate&amp;stylesheet=' . urlencode( $child_theme_name ) ), 'switch-theme_' . $child_theme_name ) ), 'updated' );
						// Network enabling failed
						else
							add_settings_error( 'sds_theme_options', 'sds_theme_options-child-theme-network-enable-failed', __( 'Child theme created successfully but there was a problem enabling it on the network. Please contact your network administrator.', $this->plugin_slug ), 'error' );
					}
					else
						add_settings_error( 'sds_theme_options', 'sds_theme_options-child-theme-creation-success', __( 'Child theme created successfully. Please network enable your child theme.', $this->plugin_slug ), 'updated' );
				}
				// Non-multisite
				else {
					// Activate the child theme
					if ( isset( $input['child_theme']['activate'] ) ) {
						// Activate child theme
						if( $sds_themes->activate_child_theme( $child_theme_name ) )
							add_settings_error( 'sds_theme_options', 'sds_theme_options-child-theme-activation-success', sprintf( __( 'New theme activated. <a href="%s">Visit site</a> or <a href="%s">edit child theme</a>.', $this->plugin_slug ), home_url( '/' ), admin_url( 'theme-editor.php?theme=' . urlencode( $child_theme_name ) ) ), 'updated' );
						else
							add_settings_error( 'sds_theme_options', 'sds_theme_options-child-theme-activation-failed', sprintf( __( 'Child theme created successfully but there was a problem activating it. Please try againNew theme activated. <a href="%s">Visit site</a> or <a href="%s">edit child theme</a>.', $this->plugin_slug ), home_url( '/' ), admin_url( 'theme-editor.php?theme=' . urlencode( $child_theme_name ) ) ), 'updated' );
					}
					else
						add_settings_error( 'sds_theme_options', 'sds_theme_options-child-theme-creation-success', sprintf( __( 'Child theme created successfully. <a href="%s">Activate</a> your child theme.', $this->plugin_slug ), wp_nonce_url( admin_url( 'themes.php?action=activate&amp;stylesheet=' . urlencode( $child_theme_name ) ), 'switch-theme_' . $child_theme_name ) ), 'updated' );
				}
			}
		}

		return $input;
	}
}