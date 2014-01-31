<?php
/**
 * @package   SDS_One_Click_Child_Themes
 * @author    Slocum Studio
 * @license   GPL-2.0+
 * @link      http://slocumstudio.com/
 * @copyright 2014 Slocum Studio
 */

/**
 * SDS_One_Click_Child_Themes_Updates Class File
 *
 * @package SDS_One_Click_Child_Themes
 * @author Slocum Studio
 */
class SDS_One_Click_Child_Themes_Updates {
	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Plugin Update Checker Instance.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $puc_instance = null;

	/**
	 * Initialize the plugin
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		/**
		 * Call $plugin_slug from public plugin class.
		 */
		$plugin = SDS_One_Click_Child_Themes::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		add_filter( 'puc_request_info_query_args-sds-one-click-child-themes', array( $this, 'sds_occt_update_query_args' ) );
		add_filter( 'puc_request_info_result-sds-one-click-child-themes', array( $this, 'sds_occt_request_info_result' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'admin_init' ) ); // Remove update notices if the versions are synced
		add_action( 'wp_dashboard_setup', array( $this, 'wp_dashboard_setup' ) ); // Create dashboard notification for updates
		add_action( 'wp_ajax_dismiss_sds_occt_update_notification', array( $this, 'wp_ajax_dismiss_sds_occt_update_notification' ) ); // Handle AJAX request for dismissing notifications
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
	 * Return an Plugin Update Checker instance.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_puc_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$puc_instance ) {
			self::$puc_instance = new PluginUpdateChecker(
				'http://theme-api.slocumstudio.com/sds-one-click-child-themes/info.php',
				SDS_OCCT_PLUGIN_FILE, //plugin_dir_path( __FILE__ ),
				'sds-one-click-child-themes'
			);
		}

		return self::$puc_instance;
	}

	function sds_occt_update_query_args( $args ) {
		$args['tt'] = time();
		$args['uid'] = md5( uniqid( rand(), true ) );
		return $args;
	}

	function sds_occt_request_info_result( $plugin_info, $result ) {
		// Update is available (store option)
		if( version_compare( SDS_One_Click_Child_Themes::VERSION, $plugin_info->version, '<' ) )
			update_option( 'sds_occt_update_available', $plugin_info->version );

		return $plugin_info;
	}

	/*
	 * This function creates a dashboard widget which displays an update notification if updates are available.
	 */
	function wp_dashboard_setup() {
		// Only display the message to administrators
		if ( current_user_can( 'update_plugins' ) ) {
			$sds_occt_update_available = get_option( 'sds_occt_update_available' );
			$sds_occt_update_message_dismissed = get_option( 'sds_occt_update_message_dismissed' );

			// If the user has not already dismissed the message for this version
			if ( version_compare( $sds_occt_update_message_dismissed, $sds_occt_update_available, '<' ) ) {
	?>
				<div class="updated" style="padding: 15px; position: relative;" id="sds_occt_dashboard_message" data-version="<?php echo $sds_occt_update_available; ?>">
					<strong><?php printf( __( 'There is a new update for "One-Click" Child Themes for Slocum Themes. You\'re currently using version %1$s. The current version is %2$s. <a href="plugins.php">Download/Install Update</a>.', $this->plugin_slug ), SDS_One_Click_Child_Themes::VERSION, $sds_occt_update_available ); ?></strong>
					<a href="javascript:void(0);" onclick="sds_occtDismissUpgradeMessage();" style="float: right;"><?php _e( 'Dismiss this notice' ); ?></a>
				</div>
				<script type="text/javascript">
					<?php  $ajax_nonce = wp_create_nonce( 'dismiss_sds_occt_update_notification' ); ?>
					function sds_occtDismissUpgradeMessage() {
						var sds_occt_data = {
							action: 'dismiss_sds_occt_update_notification',
							_wpnonce: '<?php echo $ajax_nonce; ?>',
							version: jQuery( '#sds_occt_dashboard_message' ).attr( 'data-version' )
						};

						jQuery.post( ajaxurl, sds_occt_data, function( response ) {
							jQuery( '#sds_occt_dashboard_message').fadeOut();
						} );
					}
				</script>
	<?php
			}
		}
	}

	function wp_ajax_dismiss_sds_occt_update_notification() {
		check_ajax_referer( 'dismiss_sds_occt_update_notification' );

		if ( isset( $_POST['version'] ) && ! empty( $_POST['version'] ) ) {
			update_option( 'sds_occt_update_message_dismissed', sanitize_text_field( $_POST['version'] ) );
			echo 'true';
		}
		else
			echo 'false';
		exit;
	}

	function admin_init() {
		$sds_occt_update_available = get_option( 'sds_occt_update_available' );
		if ( version_compare( SDS_One_Click_Child_Themes::VERSION, $sds_occt_update_available, '=' ) )
			update_option( 'sds_occt_update_message_dismissed', SDS_One_Click_Child_Themes::VERSION );
	}
}