=== azurecurve Page Index ===
Contributors: azurecurve
Donate link: http://development.azurecurve.co.uk/support-development/
Author URI: http://development.azurecurve.co.uk/
Plugin URI: http://development.azurecurve.co.uk/plugins/page-index/
Tags: page, index
Requires at least: 3.5
Tested up to: 5.0.0
Stable tag: 2.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Shortcode which displays a simple tile based page index showing the child pages of the loaded page or of the supplied pageid or slug. This plugin is multisite compatible.

== Description ==
Shortcode which displays a simple tile based page index showing the child pages of the loaded page or of the supplied pageid or slug. This plugin is multisite compatible.

== Installation ==
To install the plugin copy the <em>azurcurve-page-index</em> folder into your plug-in directory and activate it.

To use simply place the [page-index] shortcode on a page or in a post. Tiled page index based on child pages of the page the shortcode is used on.

If a different page index is required, or the shortcode is used in a post use one of the following parameters:
* pageid
* slug
e.g. [page-index pageid='32'] or [page-index slug='mythology/celtic-fairy-tales']']

If both parameters are supplied, then pageid will take precedence and slug will be ignored.

== Changelog ==
Changes and feature additions for the Page Index plugin:
= 2.0.3 =
* Move menu to includes folder for easier maintenance
= 2.0.2 =
* Change css from float: left to display: inline-block
= 2.0.1 =
* Correct issue with if exists azurecurve menu
= 2.0.0 =
* add azurecurve menu
= 1.3.0 =
* Added options to allow setting of default colors to override CSS; width, height, lineheight, fontweight, margin, padding and textalign
= 1.2.0 =
* Added color (azc_pi_color) and background (azc_pi_background) custom fields to allow setting of colors using custom fields on pages to override CSS and options
= 1.1.0 =
* Added color and background options to allow setting of default colors to override CSS
* Added color and background parameters to shortcode to allow override of options or CSS
= 1.0.1 =
* Fix security issues
= 1.0.0 =
* First version

== Screenshots ==
1. Sample page index.

== Frequently Asked Questions ==
= Is this plugin compatible with both WordPress and ClassicPress? =
* Yes, this plugin will work with both.
= Can I translate this plugin? =
* Yes, the .pot file is in the plugin's languages folder and can also be downloaded from the plugin page on http://development.azurecurve.co.uk; if you do translate this plugin please sent the .po and .mo files to wordpress.translations@azurecurve.co.uk for inclusion in the next version (full credit will be given).