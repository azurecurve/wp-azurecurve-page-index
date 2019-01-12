<?php
/*
Plugin Name: azurecurve Page Index
Plugin URI: http://development.azurecurve.co.uk/plugins/page-index

Description: Displays Index of Pages using page-index Shortcode; uses the Parent Page field to determine content of index or one of supplied pageid or slug parameters. This plugin is multi-site compatible.
Version: 2.0.2

Author: azurecurve
Author URI: http://development.azurecurve.co.uk

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
				"color" => ""
				,"background" => ""
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
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=azc-pi">Settings</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}
add_filter('plugin_action_links', 'azc_pi_plugin_action_links', 10, 2);

/*
function azc_pi_settings_menu() {
	add_options_page( 'azurecurve Page Index',
	'azurecurve Page Index', 'manage_options',
	'azurecurve-page-index', 'azc_pi_config_page' );
}
add_action( 'admin_menu', 'azc_pi_settings_menu' );
*/

function azc_pi_settings() {
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
				<tr><th scope="row"><label for="width"><?php _e('Width', 'azc_pi'); ?></label></th><td>
					<input type="text" name="width" value="<?php echo esc_html( stripslashes($options['width']) ); ?>" class="large-text" />
					<p class="description"><?php _e('Set default width (e.g. 48.4%)', 'azc_pi'); ?></p>
				</td></tr>
				<tr><th scope="row"><label for="height"><?php _e('Height', 'azc_pi'); ?></label></th><td>
					<input type="text" name="height" value="<?php echo esc_html( stripslashes($options['height']) ); ?>" class="large-text" />
					<p class="description"><?php _e('Set default height (e.g. 100px)', 'azc_pi'); ?></p>
				</td></tr>
				<tr><th scope="row"><label for="lineheight"><?php _e('Line Height', 'azc_pi'); ?></label></th><td>
					<input type="text" name="lineheight" value="<?php echo esc_html( stripslashes($options['lineheight']) ); ?>" class="large-text" />
					<p class="description"><?php _e('Set default line height (e.g. 100px)', 'azc_pi'); ?></p>
				</td></tr>
				<tr><th scope="row"><label for="margin"><?php _e('Margin', 'azc_pi'); ?></label></th><td>
					<input type="text" name="margin" value="<?php echo esc_html( stripslashes($options['margin']) ); ?>" class="large-text" />
					<p class="description"><?php _e('Set default margin (e.g. 4px)', 'azc_pi'); ?></p>
				</td></tr>
				<tr><th scope="row"><label for="padding"><?php _e('Padding', 'azc_pi'); ?></label></th><td>
					<input type="text" name="padding" value="<?php echo esc_html( stripslashes($options['padding']) ); ?>" class="large-text" />
					<p class="description"><?php _e('Set default padding (e.g. 3px 2px 3px 2px)', 'azc_pi'); ?></p>
				</td></tr>
				<tr><th scope="row"><label for="textalign"><?php _e('Text Align', 'azc_pi'); ?></label></th><td>
					<input type="text" name="textalign" value="<?php echo esc_html( stripslashes($options['textalign']) ); ?>" class="large-text" />
					<p class="description"><?php _e('Set default textalign (e.g. center or left)', 'azc_pi'); ?></p>
				</td></tr>
				<tr><th scope="row"><label for="fontweight"><?php _e('Font-Weight', 'azc_pi'); ?></label></th><td>
					<input type="text" name="fontweight" value="<?php echo esc_html( stripslashes($options['fontweight']) ); ?>" class="large-text" />
					<p class="description"><?php _e('Set default fontweight (e.g. 700)', 'azc_pi'); ?></p>
				</td></tr>
				</table>
				<input type="submit" value="Submit" class="button-primary"/>
			</form>
		</fieldset>
		azc_pi_color and azi_pi_background custom fields can be applied to a page to change the color of that pages appearence in the page index.
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
	
	$option_name = 'width';
	if ( isset( $_POST[$option_name] ) ) {
		$options[$option_name] = ($_POST[$option_name]);
	}
	
	$option_name = 'height';
	if ( isset( $_POST[$option_name] ) ) {
		$options[$option_name] = ($_POST[$option_name]);
	}
	
	$option_name = 'lineheight';
	if ( isset( $_POST[$option_name] ) ) {
		$options[$option_name] = ($_POST[$option_name]);
	}
	
	$option_name = 'margin';
	if ( isset( $_POST[$option_name] ) ) {
		$options[$option_name] = ($_POST[$option_name]);
	}
	
	$option_name = 'textalign';
	if ( isset( $_POST[$option_name] ) ) {
		$options[$option_name] = ($_POST[$option_name]);
	}
	
	$option_name = 'padding';
	if ( isset( $_POST[$option_name] ) ) {
		$options[$option_name] = ($_POST[$option_name]);
	}
	
	$option_name = 'fontweight';
	if ( isset( $_POST[$option_name] ) ) {
		$options[$option_name] = ($_POST[$option_name]);
	}
	
	// Store updated options array to database
	update_option( 'azc_pi', $options );
	
	// Redirect the page to the configuration form that was processed
	wp_redirect( add_query_arg( 'page', 'azc-pi&settings-updated', admin_url( 'admin.php' ) ) );
	exit;
}

function azc_display_page_index($atts, $content = null) {
	$options = get_option('azc_pi');
	if (!$options['color']){ $color = ''; }else{ $color = $options['color']; }
	if (!$options['background']){ $background = ''; }else{ $background = $options['background']; }
	if (!$options['width']){ $width = ''; }else{ $width = $options['width']; }
	if (!$options['height']){ $height = ''; }else{ $height = $options['height']; }
	if (!$options['lineheight']){ $lineheight = ''; }else{ $lineheight = $options['lineheight']; }
	if (!$options['margin']){ $margin = ''; }else{ $margin = $options['margin']; }
	if (!$options['textalign']){ $textalign = ''; }else{ $textalign = $options['textalign']; }
	if (!$options['padding']){ $padding = ''; }else{ $padding = $options['padding']; }
	if (!$options['fontweight']){ $fontweight = ''; }else{ $fontweight = $options['fontweight']; }
	extract(shortcode_atts(array(
		'pageid' => ''
		,'slug' => ''
		,'color' => $color
		,'background' => $background
		,'width' => $width
		,'height' => $height
		,'lineheight' => $lineheight
		,'margin' => $margin
		,'textalign' => $textalign
		,'padding' => $padding
		,'fontweight' => $fontweight
	), $atts));
	
	if (strlen($color) > 0){ $color = "color: $color;"; }
	if (strlen($background) > 0 ){ $background = "background: $background;"; }
	if (strlen($width) > 0 ){ $width = "width: $width;"; }
	if (strlen($height) > 0 ){ $height = "height: $height;"; }
	if (strlen($lineheight) > 0 ){ $lineheight = "line-height: $lineheight;"; }
	if (strlen($margin) > 0 ){ $margin = "margin: $margin;"; }
	if (strlen($textalign) > 0 ){ $textalign = "text-align: $textalign;"; }
	if (strlen($padding) > 0 ){ $padding = "padding: $padding;"; }
	if (strlen($fontweight) > 0 ){ $fontweight = "font-weight: $fontweight;"; }
	
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

	$sql = $wpdb->prepare("SELECT ID, post_title, post_name FROM ".$wpdb->prefix."posts WHERE post_status = 'publish' AND post_type = 'page' AND post_parent=%s ORDER BY menu_order, post_title ASC", $pageid);
	
	$output = '';
	$myrows = $wpdb->get_results( $sql );
	foreach ($myrows as $myrow){
		$overridecolor = '';
		$page_color = get_post_meta( $myrow->ID, 'azc_pi_color', true );
		if (strlen($page_color) > 0){
			$overridecolor = "color: $page_color;";
		}else{
			$overridecolor = $color;
		}
		$overridebackground = '';
		$page_background = get_post_meta( $myrow->ID, 'azc_pi_background', true );
		if (strlen($page_background) > 0){
			$overridebackground = "background: $page_background;";
		}else{
			$overridebackground = $background;
		}
		$output .= "<a href='".$page_url."/".$myrow->post_name."/' class='azc_pi' style='$overridecolor $overridebackground $width $height $lineheight $margin $textalign $padding $fontweight'>".$myrow->post_title."</a>";
	}
	
	return "<span class='azc_pi'>".$output."</span>";
}


// azurecurve menu
if (!function_exists('azc_create_plugin_menu')){
	function azc_create_plugin_menu() {
		global $admin_page_hooks;
		
		if ( empty ( $admin_page_hooks['azc-menu-test'] ) ){
			add_menu_page( "azurecurve Plugins"
							,"azurecurve"
							,'manage_options'
							,"azc-plugin-menus"
							,"azc_plugin_menus"
							,plugins_url( '/images/Favicon-16x16.png', __FILE__ ) );
			add_submenu_page( "azc-plugin-menus"
								,"Plugins"
								,"Plugins"
								,'manage_options'
								,"azc-plugin-menus"
								,"azc_plugin_menus" );
		}
	}
	add_action("admin_menu", "azc_create_plugin_menu");
}

function azc_create_pi_plugin_menu() {
	global $admin_page_hooks;
    
	add_submenu_page( "azc-plugin-menus"
						,"Page Index"
						,"Page Index"
						,'manage_options'
						,"azc-pi"
						,"azc_pi_settings" );
}
add_action("admin_menu", "azc_create_pi_plugin_menu");

if (!function_exists('azc_plugin_index_load_css')){
	function azc_plugin_index_load_css(){
		wp_enqueue_style( 'azurecurve_plugin_index', plugins_url( 'pluginstyle.css', __FILE__ ) );
	}
	add_action('admin_head', 'azc_plugin_index_load_css');
}

if (!function_exists('azc_plugin_menus')){
	function azc_plugin_menus() {
		echo "<h3>azurecurve Plugins";
		
		echo "<div style='display: block;'><h4>Active</h4>";
		echo "<span class='azc_plugin_index'>";
		if ( is_plugin_active( 'azurecurve-bbcode/azurecurve-bbcode.php' ) ) {
			echo "<a href='admin.php?page=azc-bbcode' class='azc_plugin_index'>BBCode</a>";
		}
		if ( is_plugin_active( 'azurecurve-comment-validator/azurecurve-comment-validator.php' ) ) {
			echo "<a href='admin.php?page=azc-cv' class='azc_plugin_index'>Comment Validator</a>";
		}
		if ( is_plugin_active( 'azurecurve-conditional-links/azurecurve-conditional-links.php' ) ) {
			echo "<a href='admin.php?page=azc-cl' class='azc_plugin_index'>Conditional Links</a>";
		}
		if ( is_plugin_active( 'azurecurve-display-after-post-content/azurecurve-display-after-post-content.php' ) ) {
			echo "<a href='admin.php?page=azc-dapc' class='azc_plugin_index'>Display After Post Content</a>";
		}
		if ( is_plugin_active( 'azurecurve-filtered-categories/azurecurve-filtered-categories.php' ) ) {
			echo "<a href='admin.php?page=azc-fc' class='azc_plugin_index'>Filtered Categories</a>";
		}
		if ( is_plugin_active( 'azurecurve-flags/azurecurve-flags.php' ) ) {
			echo "<a href='admin.php?page=azc-f' class='azc_plugin_index'>Flags</a>";
		}
		if ( is_plugin_active( 'azurecurve-floating-featured-image/azurecurve-floating-featured-image.php' ) ) {
			echo "<a href='admin.php?page=azc-ffi' class='azc_plugin_index'>Floating Featured Image</a>";
		}
		if ( is_plugin_active( 'azurecurve-get-plugin-info/azurecurve-get-plugin-info.php' ) ) {
			echo "<a href='admin.php?page=azc-gpi' class='azc_plugin_index'>Get Plugin Info</a>";
		}
		if ( is_plugin_active( 'azurecurve-insult-generator/azurecurve-insult-generator.php' ) ) {
			echo "<a href='admin.php?page=azc-ig' class='azc_plugin_index'>Insult Generator</a>";
		}
		if ( is_plugin_active( 'azurecurve-mobile-detection/azurecurve-mobile-detection.php' ) ) {
			echo "<a href='admin.php?page=azc-md' class='azc_plugin_index'>Mobile Detection</a>";
		}
		if ( is_plugin_active( 'azurecurve-multisite-favicon/azurecurve-multisite-favicon.php' ) ) {
			echo "<a href='admin.php?page=azc-msf' class='azc_plugin_index'>Multisite Favicon</a>";
		}
		if ( is_plugin_active( 'azurecurve-page-index/azurecurve-page-index.php' ) ) {
			echo "<a href='admin.php?page=azc-pi' class='azc_plugin_index'>Page Index</a>";
		}
		if ( is_plugin_active( 'azurecurve-posts-archive/azurecurve-posts-archive.php' ) ) {
			echo "<a href='admin.php?page=azc-pa' class='azc_plugin_index'>Posts Archive</a>";
		}
		if ( is_plugin_active( 'azurecurve-rss-feed/azurecurve-rss-feed.php' ) ) {
			echo "<a href='admin.php?page=azc-rssf' class='azc_plugin_index'>RSS Feed</a>";
		}
		if ( is_plugin_active( 'azurecurve-rss-suffix/azurecurve-rss-suffix.php' ) ) {
			echo "<a href='admin.php?page=azc-rsss' class='azc_plugin_index'>RSS Suffix</a>";
		}
		if ( is_plugin_active( 'azurecurve-series-index/azurecurve-series-index.php' ) ) {
			echo "<a href='admin.php?page=azc-si' class='azc_plugin_index'>Series Index</a>";
		}
		if ( is_plugin_active( 'azurecurve-shortcodes-in-comments/azurecurve-shortcodes-in-comments.php' ) ) {
			echo "<a href='admin.php?page=azc-sic' class='azc_plugin_index'>Shortcodes in Comments</a>";
		}
		if ( is_plugin_active( 'azurecurve-shortcodes-in-widgets/azurecurve-shortcodes-in-widgets.php' ) ) {
			echo "<a href='admin.php?page=azc-siw' class='azc_plugin_index'>Shortcodes in Widgets</a>";
		}
		if ( is_plugin_active( 'azurecurve-tag-cloud/azurecurve-tag-cloud.php' ) ) {
			echo "<a href='admin.php?page=azc-tc' class='azc_plugin_index'>Tag Cloud</a>";
		}
		if ( is_plugin_active( 'azurecurve-taxonomy-index/azurecurve-taxonomy-index.php' ) ) {
			echo "<a href='admin.php?page=azc-ti' class='azc_plugin_index'>Taxonomy Index</a>";
		}
		if ( is_plugin_active( 'azurecurve-theme-switcher/azurecurve-theme-switcher.php' ) ) {
			echo "<a href='admin.php?page=azc-ts' class='azc_plugin_index'>Theme Switcher</a>";
		}
		if ( is_plugin_active( 'azurecurve-timelines/azurecurve-timelines.php' ) ) {
			echo "<a href='admin.php?page=azc-t' class='azc_plugin_index'>Timelines</a>";
		}
		if ( is_plugin_active( 'azurecurve-toggle-showhide/azurecurve-toggle-showhide.php' ) ) {
			echo "<a href='admin.php?page=azc-tsh' class='azc_plugin_index'>Toggle Show/Hide</a>";
		}
		echo "</span></div>";
		echo "<p style='clear: both' />";
		
		echo "<div style='display: block;'><h4>Other Available Plugins</h4>";
		echo "<span class='azc_plugin_index'>";
		if ( !is_plugin_active( 'azurecurve-bbcode/azurecurve-bbcode.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-bbcode/' class='azc_plugin_index'>BBCode</a>";
		}
		if ( !is_plugin_active( 'azurecurve-comment-validator/azurecurve-comment-validator.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-comment-validator/' class='azc_plugin_index'>Comment Validator</a>";
		}
		if ( !is_plugin_active( 'azurecurve-conditional-links/azurecurve-conditional-links.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-conditional-links/' class='azc_plugin_index'>Conditional Links</a>";
		}
		if ( !is_plugin_active( 'azurecurve-display-after-post-content/azurecurve-display-after-post-content.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-display-after-post-content/' class='azc_plugin_index'>Display After Post Content</a>";
		}
		if ( !is_plugin_active( 'azurecurve-filtered-categories/azurecurve-filtered-categories.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-filtered-categories/' class='azc_plugin_index'>Filtered Categories</a>";
		}
		if ( !is_plugin_active( 'azurecurve-flags/azurecurve-flags.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-flags/' class='azc_plugin_index'>Flags</a>";
		}
		if ( !is_plugin_active( 'azurecurve-floating-featured-image/azurecurve-floating-featured-image.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-floating-featured-image/' class='azc_plugin_index'>Floating Featured Image</a>";
		}
		if ( !is_plugin_active( 'azurecurve-get-plugin-info/azurecurve-get-plugin-info.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-get-plugin-info/' class='azc_plugin_index'>Get Plugin Info</a>";
		}
		if ( !is_plugin_active( 'azurecurve-insult-generator/azurecurve-insult-generator.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-insult-generator/' class='azc_plugin_index'>Insult Generator</a>";
		}
		if ( !is_plugin_active( 'azurecurve-mobile-detection/azurecurve-mobile-detection.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-mobile-detection/' class='azc_plugin_index'>Mobile Detection</a>";
		}
		if ( !is_plugin_active( 'azurecurve-multisite-favicon/azurecurve-multisite-favicon.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-multisite-favicon/' class='azc_plugin_index'>Multisite Favicon</a>";
		}
		if ( !is_plugin_active( 'azurecurve-page-index/azurecurve-page-index.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-page-index/' class='azc_plugin_index'>Page Index</a>";
		}
		if ( !is_plugin_active( 'azurecurve-posts-archive/azurecurve-posts-archive.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-posts-archive/' class='azc_plugin_index'>Posts Archive</a>";
		}
		if ( !is_plugin_active( 'azurecurve-rss-feed/azurecurve-rss-feed.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-rss-feed/' class='azc_plugin_index'>RSS Feed</a>";
		}
		if ( !is_plugin_active( 'azurecurve-rss-suffix/azurecurve-rss-suffix.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-rss-suffix/' class='azc_plugin_index'>RSS Suffix</a>";
		}
		if ( !is_plugin_active( 'azurecurve-series-index/azurecurve-series-index.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-series-index/' class='azc_plugin_index'>Series Index</a>";
		}
		if ( !is_plugin_active( 'azurecurve-shortcodes-in-comments/azurecurve-shortcodes-in-comments.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-shortcodes-in-comments/' class='azc_plugin_index'>Shortcodes in Comments</a>";
		}
		if ( !is_plugin_active( 'azurecurve-shortcodes-in-widgets/azurecurve-shortcodes-in-widgets.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-shortcodes-in-widgets/' class='azc_plugin_index'>Shortcodes in Widgets</a>";
		}
		if ( !is_plugin_active( 'azurecurve-tag-cloud/azurecurve-tag-cloud.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-tag-cloud/' class='azc_plugin_index'>Tag Cloud</a>";
		}
		if ( !is_plugin_active( 'azurecurve-taxonomy-index/azurecurve-taxonomy-index.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-taxonomy-index/' class='azc_plugin_index'>Taxonomy Index</a>";
		}
		if ( !is_plugin_active( 'azurecurve-theme-switcher/azurecurve-theme-switcher.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-theme-switcher/' class='azc_plugin_index'>Theme Switcher</a>";
		}
		if ( !is_plugin_active( 'azurecurve-timelines/azurecurve-timelines.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-timelines/' class='azc_plugin_index'>Timelines</a>";
		}
		if ( !is_plugin_active( 'azurecurve-toggle-showhide/azurecurve-toggle-showhide.php' ) ) {
			echo "<a href='https://wordpress.org/plugins/azurecurve-toggle-showhide/' class='azc_plugin_index'>Toggle Show/Hide</a>";
		}
		echo "</span></div>";
	}
}

?>