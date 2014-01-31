<?php
/**
 * SDS_Themes
 *
 * @package   SDS_Themes
 * @author    Slocum Studio
 * @license   GPL-2.0+
 * @link      http://slocumstudio.com/
 * @copyright 2014 Slocum Studio
 */

/**
 * SDS_Themes Class File
 *
 * Functionality for interacting with, fetching data from, and creating themes & child themes.
 *
 * @package SDS_One_Click_Child_Themes
 * @author Slocum Studio
 */
class SDS_Themes {
	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Current parent theme details.
	 *
	 * @since    1.0.0
	 *
	 * @var      object    WP_Theme
	 */
	public $current_parent_theme = null;

	/**
	 * Current child theme details.
	 *
	 * @since    1.0.0
	 *
	 * @var      object    WP_Theme
	 */
	public $current_child_theme = null;

	/**
	 * Installed child theme details.
	 *
	 * @since    1.0.0
	 *
	 * @var      array    An array of WP_Theme objects
	 */
	public $installed_child_themes = null;

	/**
	 * Initialize the plugin.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		/**
		 * Call $plugin_slug from public plugin class.
		 */
		$plugin = SDS_One_Click_Child_Themes::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Initialize Theme Data
		$this->current_parent_theme = $this->get_current_parent_theme();
		$this->current_child_theme = $this->get_current_child_theme();
		$this->installed_child_themes = $this->get_installed_child_themes( $this->current_parent_theme );
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
	 * Return the details of the current parent theme.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A WP_Theme object.
	 */
	public function get_current_parent_theme() {
		if ( is_a( $this->current_parent_theme, 'WP_Theme' ) )
			return $this->current_parent_theme;

		return ( is_child_theme() ) ? wp_get_theme()->parent() : wp_get_theme();
	}

	/**
	 * Return the details of the current child theme (if activated).
	 *
	 * @since     1.0.0
	 *
	 * @return    object/false    A WP_Theme object or false if child theme is not active.
	 */
	public function get_current_child_theme() {
		// Bail if child theme is not active
		if ( ! is_child_theme() )
			return false;

		if ( is_a( $this->current_child_theme, 'WP_Theme' ) )
			return $this->current_child_theme;

		return wp_get_theme();
	}

	/**
	 * Return the details of installed child theme (if activated).
	 *
	 * @since     1.0.0
	 *
	 * @return    array/false    An array of WP_Theme objects or false if child theme is not active.
	 */
	public function get_installed_child_themes( $parent_theme ) {
		// Bail if parent theme is not a WP_Theme object
		if( empty( $parent_theme ) || ! is_a( $parent_theme, 'WP_Theme' ) )
			return false;

		if ( is_array( $this->installed_child_themes ) )
			return $this->installed_child_themes;

		$theme_template = $this->current_parent_theme->get_stylesheet(); // Get current parent theme directory name
		$wp_themes = wp_get_themes(); // Get all installed themes
		$child_themes = array();

		// Check for child themes
		foreach ( $wp_themes as $theme ) {
			// Child theme of the current active theme and not the current theme
			if ( $theme->get_template() === $theme_template && $theme->get_template() !== $theme->get_stylesheet() )
				$child_themes[] = $theme;
		}

		return ( ! empty( $child_themes ) ) ? $child_themes : false;
	}

	/**
	 * Creates a stylesheet header template.
	 *
	 * @uses get_file_data()
	 *
	 * @since     1.0.0
	 *
	 * @return    string    A stylesheet header template string for use in style.css
	 */
	function create_stylesheet_header( $theme_name ) {
		if ( empty( $theme_name ) )
			return false;

		$stylesheet_header = false;

		// Stylesheet headers to fetch from current theme stylesheet
		$stylesheet_header_data = array(
			'name'        => 'Theme Name',
			'theme_uri'   => 'Theme URI',
			'description' => 'Description',
			'author'      => 'Author',
			'author_uri'  => 'Author URI',
			'version'     => 'Version',
			'license'     => 'License',
			'license_uri' => 'License URI',
			'template'    => 'Template',
		);

		// Fetch stylesheet header
		$parent_stylesheet_header = get_file_data( trailingslashit( $this->current_parent_theme->get_stylesheet_directory() ) . 'style.css', $stylesheet_header_data );

		// Adjust for child theme data
		$parent_stylesheet_header['name'] = $theme_name;
		$parent_stylesheet_header['description'] = sprintf( __( 'A "one-click" child theme created for %1$s - %2$s', $this->plugin_slug ), $this->current_parent_theme->get( 'Name' ), $parent_stylesheet_header['description'] );
		$parent_stylesheet_header['version'] = '1.0';
		$parent_stylesheet_header['template'] = $this->current_parent_theme->get_stylesheet();

		// Create child theme stylesheet header
		foreach ( $stylesheet_header_data as $key => $header )
			$stylesheet_header .= " * $header: {$parent_stylesheet_header[$key]}\n";

		$stylesheet_header = "/**\n". $stylesheet_header . " */\n";

		return $stylesheet_header;
	}

	/**
	 * Create a child theme - create a directory, create style.css, create blank functions.php, move screenshot.png from parent theme
	 *
	 * @uses WP_Filesystem
	 *
	 * @since     1.0.0
	 *
	 * @return    Boolean    True if child theme was created successfully or false otherwise
	 */
	function create_child_theme( $directory, $stylesheet_header, $theme_root ) {
		global $wp_filesystem;

		$parent_directory = trailingslashit( $theme_root . $this->current_parent_theme->get_stylesheet() );

		// Set up the WordPress filesystem
		WP_Filesystem(); 

		// Create child theme style.css
		if ( $wp_filesystem->mkdir( $directory ) === false )
			return false;

		// Create child theme style.css
		if ( $wp_filesystem->put_contents( $directory . 'style.css', $stylesheet_header ) === false )
			return false;

		// Create blank functions.php
		$wp_filesystem->touch( $directory . 'functions.php' );

		// Move the screenshot from the parent theme
		if ( $wp_filesystem->exists( $parent_directory . 'screenshot.png' ) )
			$wp_filesystem->copy( $parent_directory . 'screenshot.png', $directory . 'screenshot.png' );

		return true;
	}

	/**
	 * Enable a child theme across the site network (Multisite)
	 *
	 * @since     1.0.0
	 *
	 * @return    Boolean    True if child theme was enabled successfully or false otherwise
	 */
	function network_enable_child_theme( $theme_name ) {
		// Bail if current user cannot manage network themes
		if ( ! current_user_can( 'manage_network_themes' ) )
			return false;

		$allowed_themes = get_site_option( 'allowedthemes' );
		$allowed_themes[$theme_name] = true;

		// Update was successful
		if( update_site_option( 'allowedthemes', $allowed_themes ) )
			return true;

		return false;
	}

	/**
	 * This function activates a child theme.
	 *
	 * @uses switch_theme()
	 *
	 * @since     1.0.0
	 *
	 * @return    null
	 */
	function activate_child_theme( $theme, $preserve_settings = true ) {
		// Bail if current user cannot switch themes
		if ( !current_user_can( 'switch_themes' ) )
			return false;

		// Preserve their widget and menu locations
		if ( $preserve_settings ) {
			$sidebar_widgets = wp_get_sidebars_widgets();
			$menu_locations = get_nav_menu_locations();

			switch_theme( $theme );

			// Carry over widgets and menus to child theme
			set_theme_mod( 'nav_menu_locations', $menu_locations );
			wp_set_sidebars_widgets( $sidebar_widgets );
		}
		else
			switch_theme( $theme );


		return true;
	}

	/**
	 * This function checks to see if a child theme was just activated through the options panel.
	 *
	 * @uses get_settings_errors()
	 *
	 * @since     1.0.0
	 *
	 * @return    Boolean     True if child theme was just activated or false otherwise
	 */
	function get_child_theme_activation_status() {
		$settings_errors = get_settings_errors();

		// If a settings error exists, child theme was just activated
		if ( ! empty( $settings_errors ) )
			foreach( $settings_errors as $error )
				if ( $error['code'] == 'child-theme-activation-success' )
					return true;

		return false;
	}
}
