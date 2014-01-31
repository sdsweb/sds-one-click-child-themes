<?php
/**
 * Represents the view for the settings on the Theme Options panel.
 *
 * @package   SDS_One_Click_Child_Themes
 * @author    Slocum Studio
 * @license   GPL-2.0+
 * @link      http://slocumstudio.com/
 * @copyright 2014 Slocum Studio
 */

$sds_themes = SDS_Themes::get_instance();
?>

<?php // One-Click Child Themes Settings ?>
<div id="one-click-child-themes-tab-content" class="sds-theme-options-tab-content">
	<h3><?php _e( '"One-Click" Child Themes', $this->plugin_slug ); ?></h3>

	<?php if ( $this->is_multisite ) : // Multisite is active ?>
		<!--div class="message error settings-error sds-child-themes-message" style="border-left: 4px solid #ffba00; display: none !important;">
			<p><strong><?php _e( 'Please Note: It looks like Multisite is active. Contact your network administrator to enable child themes on the network if you are having trouble.', $this->plugin_slug ); ?></strong></p>
		</div-->
	<?php endif; ?>

	<?php if ( ! $sds_themes->get_child_theme_activation_status() && is_child_theme() ) : // Child theme is currently active ?>
		<div class="message error settings-error sds-child-themes-message" style="border-left: 4px solid #ffba00; display: none !important;">
			<p><strong><?php _e( 'Please Note: It looks like you\'re already using a child theme.', $this->plugin_slug ); ?></strong></p>
		</div>
	<?php endif; ?>

	<div class="form-table">
		<p><?php printf( __( 'Child themes are an essential part to <a href="%1$s" target="_blank">WordPress theme modification</a>. If you\'re looking to enhance <strong>%2$s</strong> beyond the Theme Options we\'ve provided, you\'ll find the tools to easily create your very own child theme below. It couldn\'t be more simple.', $this->plugin_slug ), 'http://slocumthemes.com/2013/12/how-to-create-a-child-theme/', $sds_themes->current_parent_theme->get( 'Name' ) ); ?></p>

		<?php if ( ! empty( $sds_themes->installed_child_themes ) ) : // Child themes exist ?>
			<h4><?php printf( __( 'The following %1$s child themes already exist:', $this->plugin_slug ), $sds_themes->current_parent_theme->get( 'Name' ) ); ?></h4>
			<ul class="sds-child-themes">
				<?php
					foreach( $sds_themes->installed_child_themes as $child_theme ) :
						// Is this child theme currently active?
						if ( is_a( $sds_themes->current_child_theme, 'WP_Theme' ) && $sds_themes->current_child_theme->get_stylesheet() === $child_theme->get_stylesheet() ) :
				?>
							<li><?php printf( _x( '<strong>%1$s (Active)</strong> - <a href="%2$s">Edit</a>', 'Child theme name and edit link', $this->plugin_slug ), $child_theme->get( 'Name' ), admin_url( 'theme-editor.php?theme=' . urlencode( $child_theme->get_stylesheet() ) ) ); ?></li>
				<?php
						// Multisite
						elseif ( $this->is_multisite ):
				?>
							<li><?php printf( _x( '%1$s', 'Child theme name', $this->plugin_slug ), $child_theme->get( 'Name' ) ); ?></li>
				<?php
						// Non-multisite
						else:
				?>
							<li><?php printf( _x( '%1$s - <a href="%2$s">Activate</a>', 'Child theme name and activation link', $this->plugin_slug ), $child_theme->get( 'Name' ), wp_nonce_url( admin_url( 'themes.php?action=activate&amp;stylesheet=' . urlencode( $child_theme->get_stylesheet() ) ), 'switch-theme_' . $child_theme->get_stylesheet() ) ); ?></li>
				<?php
						endif;
					endforeach;
				?>
			</ul>
		<?php endif; ?>
	</div>

	<h3><?php _e( '1. Name Your Child Theme', $this->plugin_slug ); ?> <span class="description"><?php _ex( '(required)', 'This field is required', $this->plugin_slug ); ?></span></h3>

	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e( 'Child Theme Name:', $this->plugin_slug ); ?></th>
			<td>
				<input type="text" id="sds_theme_options_one_click_child_theme_name" name="sds_theme_options[child_theme][name]" class="large-text" value="" placeholder="e.g. <?php echo $sds_themes->current_parent_theme->get( 'Name' ); ?> Child Theme" />
			</td>
		</tr>
	</table>

	<h3><?php _e( '2. Advanced Settings', $this->plugin_slug ); ?> <span class="description"><?php _ex( '(optional)', 'This field is optional', $this->plugin_slug ); ?></span></h3>

	<table class="form-table">
		<?php if ( $this->is_multisite && current_user_can( 'manage_network_themes' ) ) : // Multsite ?>
			<tr valign="top">
				<th scope="row"><?php _e( 'Enable on Network:', $this->plugin_slug ); ?></th>
				<td>
					<div class="checkbox sds-themes-child-themes-checkbox">
						<input type="checkbox" id="sds_theme_options_one_click_child_theme_network_enable" name="sds_theme_options[child_theme][network_enable]" />
						<label for="sds_theme_options_one_click_child_theme_network_enable"><?php _e( 'Enable child theme on network', $this->plugin_slug ); ?></label>
					</div>
					<span class="description"><?php _e( 'This option will enable the new child theme across <strong>all</strong> sites in the network.', $this->plugin_slug ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Activate Child Theme:', $this->plugin_slug ); ?></th>
				<td>
					<div class="checkbox sds-themes-child-themes-checkbox">
						<input type="checkbox" id="sds_theme_options_one_click_child_theme_activate" name="sds_theme_options[child_theme][activate]" />
						<label for="sds_theme_options_one_click_child_theme_activate"><?php _e( 'Activate child theme on <strong>this site</strong> once it has been created', $this->plugin_slug ); ?></label>
					</div>
					<span class="description"><?php printf( __( '<strong>Requires child theme to be enabled on network.</strong> This option will also keep navigation menus and widgets from %1$s.', $this->plugin_slug ), $sds_themes->current_parent_theme->get( 'Name' ) ); ?></span>
				</td>
			</tr>
		<?php else: // Non-multisite ?>
			<tr valign="top">
				<th scope="row"><?php _e( 'Activate Child Theme:', $this->plugin_slug ); ?></th>
				<td>
					<div class="checkbox sds-themes-child-themes-checkbox">
						<input type="checkbox" id="sds_theme_options_one_click_child_theme_activate" name="sds_theme_options[child_theme][activate]" />
						<label for="sds_theme_options_one_click_child_theme_activate"><?php _e( 'Activate child theme once it has been created', $this->plugin_slug ); ?></label>
					</div>
					<span class="description"><?php printf( __( 'This option will also keep navigation menus and widgets from %1$s.', $this->plugin_slug ), $sds_themes->current_parent_theme->get( 'Name' ) ); ?></span>
				</td>
			</tr>
		<?php endif; ?>
	</table>

	<p class="one-click-child-theme-submit">
		<?php submit_button( __( 'Create Child Theme', $this->plugin_slug ), 'primary', 'sds_theme_options[create_child_theme]', false ); ?>
	</p>
</div>