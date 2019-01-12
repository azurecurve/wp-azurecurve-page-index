<?php
/*
Plugin Name: azurecurve Page Index
Plugin URI: http://wordpress.azurecurve.co.uk/plugins/page-index

Description: Displays Index of Pages using page-index Shortcode; uses the Parent Page field to determine content of index or one of supplied pageid or slug parameters. This plugin is multi-site compatible.
Version: 1.1.0

Author: Ian Grieve
Author URI: http://wordpress.azurecurve.co.uk

Text Domain: azurecurve-pi
Domain Path: /languages

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.

The full copy of the GNU General Public License is available here: http://www.gnu.org/licenses/gpl.txt

*/

add_shortcode( 'page-index', 'azc_display_page_index' );
add_action('wp_enqueue_scripts', 'azc_pi_load_css');

function azc_pi_load_css(){
	wp_enqueue_style( 'azurecurve-page-index', plugins_url( 'style.css', __FILE__ ), '', '1.0.0' );
}

function azc_pi_load_plugin_textdomain(){
	
	$loaded = load_plugin_textdomain( 'azc_pi', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action('plugins_loaded', 'azc_pi_load_plugin_textdomain');

function azc_pi_set_default_options($networkwide) {
	
	$new_options = array(
				"color" => "#fff"
				,"background" => "#B87333"
			);
	
	// set defaults for multi-site
	if (function_exists('is_multisite') && is_multisite()) {
		// check if it is a network activation - if so, run the activation function for each blog id
		if ($networkwide) {
			global $wpdb;

			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			$original_blog_id = get_current_blog_id();

			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );

				if ( get_option( 'azc_pi' ) === false ) {
					add_option( 'azc_pi', $new_options );
				}
			}

			switch_to_blog( $original_blog_id );
		}else{
			if ( get_option( 'azc_pi' ) === false ) {
				add_option( 'azc_pi', $new_options );
			}
		}
		if ( get_site_option( 'azc_pi' ) === false ) {
			add_site_option( 'azc_pi', $new_options );
		}
	}
	//set defaults for single site
	else{
		if ( get_option( 'azc_pi' ) === false ) {
			add_option( 'azc_pi', $new_options );
		}
	}
}
register_activation_hook( __FILE__, 'azc_pi_set_default_options' );

function azc_pi_plugin_action_links($links, $file) {
    static $this_plugin;

    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    if ($file == $this_plugin) {
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=azurecurve-page-index">Settings</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}
add_filter('plugin_action_links', 'azc_pi_plugin_action_links', 10, 2);

function azc_pi_settings_menu() {
	add_options_page( 'azurecurve Page Index',
	'azurecurve Page Index', 'manage_options',
	'azurecurve-page-index', 'azc_pi_config_page' );
}
add_action( 'admin_menu', 'azc_pi_settings_menu' );

function azc_pi_config_page() {
	if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'azc_pi'));
    }
	
	// Retrieve plugin configuration options from database
	$options = get_option( 'azc_pi' );
	?>
	<div id="azc-pi-general" class="wrap">
		<fieldset>
			<h2>azurecurve Page Index <?php _e('Settings', 'azc_pi'); ?></h2>
			<?php if( isset($_GET['settings-updated']) ) { ?>
				<div id="message" class="updated">
					<p><strong><?php _e('Settings have been saved.') ?></strong></p>
				</div>
			<?php } ?>
			<form method="post" action="admin-post.php">
				<input type="hidden" name="action" value="save_azc_pi_options" />
				<input name="page_options" type="hidden" value="azc_pi" />
				
				<!-- Adding security through hidden referrer field -->
				<?php wp_nonce_field( 'azc_pi' ); ?>
				<table class="form-table">
				<tr><td colspan=2>
					<p><?php _e('If the options are blank then the defaults in the plugin\'s CSS will be used.', 'azc_pi'); ?></p>
				</td></tr>
				<tr><th scope="row"><label for="color"><?php _e('Color', 'azc_pi'); ?></label></th><td>
					<input type="text" name="color" value="<?php echo esc_html( stripslashes($options['color']) ); ?>" class="large-text" />
					<p class="description"><?php _e('Set default color (e.g. #FFF)', 'azc_pi'); ?></p>
				</td></tr>
				<tr><th scope="row"><label for="background"><?php _e('Background Color', 'azc_pi'); ?></label></th><td>
					<input type="text" name="background" value="<?php echo esc_html( stripslashes($options['background']) ); ?>" class="large-text" />
					<p class="description"><?php _e('Set default background color (e.g. #000)', 'azc_pi'); ?></p>
				</td></tr>
				</table>
				<input type="submit" value="Submit" class="button-primary"/>
			</form>
		</fieldset>
	</div>
<?php }


function azc_pi_admin_init() {
	add_action( 'admin_post_save_azc_pi_options', 'process_azc_pi_options' );
}
add_action( 'admin_init', 'azc_pi_admin_init' );

function process_azc_pi_options() {
	// Check that user has proper security level
	if ( !current_user_can( 'manage_options' ) ){
		wp_die( __('You do not have permissions for this action', 'azc_pi'));
	}
	// Check that nonce field created in configuration form is present
	check_admin_referer( 'azc_pi' );
	settings_fields('azc_pi');
	
	// Retrieve original plugin options array
	$options = get_option( 'azc_pi' );
	
	$option_name = 'color';
	if ( isset( $_POST[$option_name] ) ) {
		$options[$option_name] = ($_POST[$option_name]);
	}
	
	$option_name = 'background';
	if ( isset( $_POST[$option_name] ) ) {
		$options[$option_name] = ($_POST[$option_name]);
	}
	
	// Store updated options array to database
	update_option( 'azc_pi', $options );
	
	// Redirect the page to the configuration form that was processed
	wp_redirect( add_query_arg( 'page', 'azurecurve-page-index&settings-updated', admin_url( 'options-general.php' ) ) );
	exit;
}

function azc_display_page_index($atts, $content = null) {
	$options = get_option('azc_pi');
	if (!$options['color']){ $color = ''; }else{ $color = $options['color']; }
	if (!$options['background']){ $background = ''; }else{ $background = $options['background']; }
	extract(shortcode_atts(array(
		'pageid' => ''
		,'slug' => ''
		,'color' => $color
		,'background' => $background
	), $atts));
	if (strlen($color) > 0){
			$color = "color: $color;";
	}
	if (strlen($background) > 0 ){
			$background = "background: $background;";
	}
	
	$pageid = intval($pageid);
	$slug = sanitize_text_field($slug);
	
	global $wpdb;
	
	$page_url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	if (substr($page_url, -1) == "/"){
		$page_url = substr($page_url, 0, -1);
	}
	
	if (strlen($postid) > 0){
		$pageid = $postid;
	}elseif (strlen($slug) > 0){
		$page = get_page_by_path($slug);
		$pageid = $page->ID;
	}else{
		$pageid = get_the_ID();
	}

	$sql = $wpdb->prepare("SELECT post_title, post_name FROM ".$wpdb->prefix."posts WHERE post_status = 'publish' AND post_type = 'page' AND post_parent=%s ORDER BY menu_order, post_title ASC", $pageid);
	
	$output = '';
	$myrows = $wpdb->get_results( $sql );
	foreach ($myrows as $myrow){
		$output .= "<a href='".$page_url."/".$myrow->post_name."/' class='azc_pi' style='$color $background'>".$myrow->post_title."</a>";
	}
	
	return "<span class='azc_pi'>".$output."</span>";
}


?>