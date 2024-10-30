<?php
/*
Plugin Name: Max Grid
Plugin URI: https://www.mustaphafersaoui.fr/wp-plugins/max-grid
Description: Drag and drop grid builder, that helps you quickly and easily build responsive grid for your WordPress posts – no programming knowledge required.
Version: 1.1.2
Author: Mustapha FERSAOUI
Author URI: https://www.mustaphafersaoui.fr
Text Domain: max-grid
Domain Path: /languages/

License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License or (at your option) any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with this program. If not, see http://www.gnu.org/licenses/

You can contact us at mfersaoui@yahoo.fr

Max Grid incorporates code from:
    Nicescroll Plugin https://nicescroll.areaaperta.com, Copyright 2011-17 InuYaksa, distributed under the terms of the MIT License, https://opensource.org/licenses/MIT
    SweetAlert Plugin https://sweetalert.js.org, Copyright 2014-present Tristan Edwards, distributed under the terms of the MIT License, https://github.com/t4t5/sweetalert/blob/master/LICENSE.md
    Simple Iconpicker Plugin https://sweetalert.js.org, Copyright 2016-17 Aumkar Thakur, distributed under the terms of the MIT License, https://github.com/aumkarthakur/simple-fontawesome-iconpicker/blob/master/LICENSE
    Fontselect jQuery Plugin https://www.npmjs.com/package/fontselect-jquery-plugin, Copyright 2011 Tom Moor, distributed under the terms of the MIT License, https://github.com/tommoor/fontselect-jquery-plugin
    Chosen Plugin https://harvesthq.github.io/chosen/, Copyright 2011-2018 Harvest, distributed under the terms of the MIT License, https://github.com/harvesthq/chosen/blob/master/LICENSE.md
    ACE Plugin https://github.com/ajaxorg/ace, Copyright 2010, Ajax.org B.V., distributed under the BSD license, https://github.com/ajaxorg/ace/blob/master/CONTRIBUTING.md
    Masonry Plugin https://masonry.desandro.com, Copyright 2010 David DeSandro, distributed under the MIT License, https://github.com/desandro/masonry
    Wp Color Picker Alpha Plugin https://github.com/kallookoo/wp-color-picker-alpha, distributed under the GPLv2 License, https://github.com/desandro/masonry
    Toggle Switch Plugin http://www.cssflow.com/snippets/simple-toggle-switch, Copyright 2013 Thibaut Courouble, distributed under the MIT License, https://github.com/sass/sass/blob/stable/MIT-LICENSE
	
Max Grid bundles the following third-party resources:
    FontAwesome icon font https://fontawesome.com, distributed under the terms of the MIT License, https://fontawesome.com/license/free
*/

if (!defined('ABSPATH')) {
    exit;
}

// Define MAXGRID_PLUGIN_FILE.
if ( ! defined( 'MAXGRID_PLUGIN_FILE' ) ) {
	define( 'MAXGRID_PLUGIN_FILE', __FILE__ );
}

// Include the main Max_Grid class.
if ( ! class_exists( 'Max_Grid' ) ) {
	include_once dirname( __FILE__ ) . '/assets/admin/class-max-grid.php';
}

/**
 * Main instance of Max Grid Builder.
 *
 * Returns the main instance of MaxGrid
 *
 * @return Max_Grid
 */
function maxgrid() {
	return Max_Grid::instance();
}

// Global for backwards compatibility.
$GLOBALS['maxgrid'] = maxgrid();

// Register activation / deactivation / uninstall hooks
$Max_Grid_Install = new Max_Grid_Install;

register_activation_hook( __FILE__, array( &$Max_Grid_Install, 'activate' ) );

$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';

/**
 * Before VC Init
 */
if ( is_maxgrid_premium_activated() ) {
	add_action( 'vc_before_init', 'maxgrid_vc_custom_field' ); 

	function maxgrid_vc_custom_field() {
		require_once( MAXGRID_PREMIUM_ABSPATH . '/visual-composer/vc_custom_fields.php' );
	}
}