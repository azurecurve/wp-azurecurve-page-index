<?php
/*
Plugin Name: azurecurve Page Index
Plugin URI: http://wordpress.azurecurve.co.uk/plugins/page-index

Description: Displays Index of Pages using page-index Shortcode; uses the Parent Page field to determine content of index or one of supplied pageid or slug parameters. This plugin is multi-site compatible.
Version: 1.0.0

Author: Ian Grieve
Author URI: http://wordpress.azurecurve.co.uk

Text Domain: azurecurve-page-index
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

function azc_display_page_index($atts, $content = null) {
	extract(shortcode_atts(array(
		'pageid' => '',
		'slug' => ''
	), $atts));
	
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

	$sql = "SELECT post_title, post_name FROM ".$wpdb->prefix."posts WHERE post_status = 'publish' AND post_type = 'page' AND post_parent=$pageid ORDER BY menu_order, post_title ASC";
	
	$output = '';
	$myrows = $wpdb->get_results( $sql );
	foreach ($myrows as $myrow){
		$output .= "<a href='".$page_url."/".$myrow->post_name."/' class='azc_pi'>".$myrow->post_title."</a>";
	}
	
	return "<span class='azc_pi'>".$output."</span>";
}


?>