<?php
/*
   Plugin Name: IRivYou - Import Amazon reviews and Aliexpress reviews to woocommerce
   Plugin URI: http://wordpress.org/extend/plugins/wooreviews-importer/
   Version: 2.2.1
   Author: zizou1988
   Description: Import Amazon reviews and Aliexpress reviews to woocommerce
   Text Domain: wooreviews-importer
   License: GPLv3
    Tested up to: 5.0.0
  */

/*
    "WordPress Plugin Template" Copyright (C) 2020 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This following part of this file is part of WordPress Plugin Template for WordPress.

    WordPress Plugin Template is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WordPress Plugin Template is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see http://www.gnu.org/licenses/gpl-3.0.html
*/

$WooreviewsImporter_minimalRequiredPhpVersion = '5.0';

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version
 * @return boolean true if version check passed. If false, triggers an error which WP will handle, by displaying
 * an error message on the Admin page
 */
function WooreviewsImporter_noticePhpVersionWrong()
{
    global $WooreviewsImporter_minimalRequiredPhpVersion;
    echo '<div class="updated fade">' .
        __('Error: plugin "WooReviews importer" requires a newer version of PHP to be running.',  'wooreviews-importer') .
        '<br/>' . __('Minimal version of PHP required: ', 'wooreviews-importer') . '<strong>' . $WooreviewsImporter_minimalRequiredPhpVersion . '</strong>' .
        '<br/>' . __('Your server\'s PHP version: ', 'wooreviews-importer') . '<strong>' . phpversion() . '</strong>' .
        '</div>';
}


function WooreviewsImporter_PhpVersionCheck()
{
    global $WooreviewsImporter_minimalRequiredPhpVersion;
    if (version_compare(phpversion(), $WooreviewsImporter_minimalRequiredPhpVersion) < 0) {
        add_action('admin_notices', 'WooreviewsImporter_noticePhpVersionWrong');
        return false;
    }
    return true;
}


/**
 * Initialize internationalization (i18n) for this plugin.
 * References:
 *      http://codex.wordpress.org/I18n_for_WordPress_Developers
 *      http://www.wdmac.com/how-to-create-a-po-language-translation#more-631
 * @return void
 */
function WooreviewsImporter_i18n_init()
{
    $pluginDir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('wooreviews-importer', false, $pluginDir . '/languages/');
}


//////////////////////////////////
// Run initialization
/////////////////////////////////
// add_option('your_option_name','your_option_value');







// // add_filter( 'woocommerce_get_star_rating_html', 'replace_star_ratings' );

// // function replace_star_ratings( $variable ) {
// //     echo "XXX";
// //     return $variable;
// // }





function my_admin_scripts($hook_suffix)
{

    if ('post.php' == $hook_suffix || 'post-new.php' == $hook_suffix) {

        wp_enqueue_script('bootstrap', plugin_dir_url(__FILE__) . 'js/bootstrap.min.js', array('jquery'), NULL, false);
        wp_enqueue_script('toast', plugin_dir_url(__FILE__) . 'js/jquery.toast.min.js', array('jquery'), NULL, false);
        wp_enqueue_script('reviews', plugin_dir_url(__FILE__) . 'js/reviews.js', array('jquery'), NULL, false);
        wp_enqueue_style('bootstrapCss', plugin_dir_url(__FILE__) . 'css/bootstrap_1.min.css');
        wp_enqueue_style('toastCss', plugin_dir_url(__FILE__) . 'css/jquery.toast.min.css');
        wp_enqueue_style('mdbcss', plugin_dir_url(__FILE__) . 'css/mdb.min.css');
        wp_enqueue_style('custom', plugin_dir_url(__FILE__) . 'css/main.css');

        // wp_enqueue_style('custom', plugin_dir_url(__FILE__) . 'css/main.css');
        wp_localize_script(
            'reviews',
            'wooshark_params_reviews',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ajax-nonce')
            )
        );


        // wp_enqueue_script( 'custom_js', get_template_directory_uri() . '/inc/meta/custom.js', array( 'jquery' ));
        // wp_enqueue_style( 'custom_css', get_template_directory_uri() . '/inc/meta/custom.css')
    }
}
add_action('admin_enqueue_scripts', 'my_admin_scripts');


function our_plugin_action_links_reviews_plugin($links, $file) {
    static $this_plugin;

    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    // check to make sure we are on the correct plugin

    if ($file == $this_plugin) {

        // the anchor tag and href to the URL we want. For a "Settings" link, this needs to be the url of your settings page

        $settings_link = '<a style="color:red" target="_blank" href="http://sharkdropship.com/irivyou">Go pro</a>';

        $home_link = '<a style="color:orange" href="admin.php?page=WooreviewsImporter_PluginSettings"> Plugin page</a>';


        // add the link to the list
        array_unshift($links, $home_link);
        array_unshift($links, $settings_link);
    }

    return $links;
}


add_filter('plugin_action_links', 'our_plugin_action_links_reviews_plugin', 10, 2);

// add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'our_plugin_action_links_reviews_plugin' );

// add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'WooreviewsImporter_PluginSettings' , 10, 2);


 
// Initialize i18n
add_action('plugins_loadedi', 'WooreviewsImporter_i18n_init');

// Run the version check.
// If it is successful, continue with initialization for this plugin
if (WooreviewsImporter_PhpVersionCheck()) {
    // Only load and run the init function if we know PHP version can parse it
    include_once('wooreviews-importer_init.php');
    WooreviewsImporter_init(__FILE__);
    initProductPage();
}


function initProductPage()
{



    add_action('post_submitbox_misc_actions', 'woo_add_custom_general_fields', 20);

    function woo_add_custom_general_fields()
    {

        echo ' 
        <div class="loader4" style="display:none"><div></div><div></div><div></div></div><button type="button" style="margin:10px;" id="dispayImportModal"  class="btn btn-primary" data-toggle="modal" data-target=".bd-example-modal-lg"> Import reviews to product</button><div id="modal-container"></div>';
    }
}



if (get_option('isHideReviewsDisplay') == 'Y') {
    add_filter('woocommerce_product_tabs', 'woo_remove_product_tabs_wooshark_Review_plugin', 98);
    function woo_remove_product_tabs_wooshark_Review_plugin($tabs)
    {
        unset($tabs['reviews']);  // Removes the reviews tab
        // unset( $tabs['additional_information'] );  // Removes the additional information tab
        return $tabs;
    }
    add_action('woocommerce_after_single_product_summary', 'comments_template', 50);
}

if ( ! wp_next_scheduled( 're_rev_co_nu' ) ) {
    wp_schedule_event( time(), 'monthly', 're_rev_co_nu' );
  }  
  ///Hook into that action that'll fire every six hours
  add_action( 're_rev_co_nu', 'tototititatata' );
  //create your function, that runs on cron
  function tototititatata() {
    update_option('maxReachedNumber',   0);
    //your function...
  }



// if (get_option('isChangeReviewsStars') == 'Y') {
//     add_filter('woocommerce_get_star_rating_html', 'replace_star_ratings', 10, 2);
//     function replace_star_ratings($html, $rating)
//     {
//         $html = ""; // Erase default HTML
//         for ($i = 0; $i < 5; $i++) {
//             $html .= $i < $rating ? '<i style="color:red" class="fa fa-heart"></i>' : '<i style="color:red" class="fa fa-heart-o"></i>';
//         }
//         return $html;
//     }
// }
