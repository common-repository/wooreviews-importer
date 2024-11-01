<?php
/*
    "WordPress Plugin Template" Copyright (C) 2020 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This file is part of WordPress Plugin Template for WordPress.

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

function loadScripts()
{ }



function WooreviewsImporter_init($file)
{

  require_once('WooreviewsImporter_Plugin.php');
  $aPlugin = new WooreviewsImporter_Plugin();

  // Install the plugin
  // NOTE: this file gets run each time you *activate* the plugin.
  // So in WP when you "install" the plugin, all that does it dump its files in the plugin-templates directory
  // but it does not call any of its code.
  // So here, the plugin tracks whether or not it has run its install operation, and we ensure it is run only once
  // on the first activation
  if (!$aPlugin->isInstalled()) {
    $aPlugin->install();
  } else {
    // Perform any version-upgrade activities prior to activation (e.g. database changes)
    $aPlugin->upgrade();
  }

  // Add callbacks to hooks
  $aPlugin->addActionsAndFilters();

  if (!$file) {
    $file = __FILE__;
  }
  // Register the Plugin Activation Hook
  register_activation_hook($file, array(&$aPlugin, 'activate'));


  // Register the Plugin Deactivation Hook
  register_deactivation_hook($file, array(&$aPlugin, 'deactivate'));

  // function enqueue_single_product_reviews()
  // {
  //     if (is_product()) {

  //         wp_enqueue_style('bootstrapCss', plugin_dir_url(__FILE__) . 'css/bootstrap.min.css');
  //         wp_enqueue_script('bootstrap', plugin_dir_url(__FILE__) . 'js/bootstrap.min.js', array('jquery'), NULL, false);
  //         wp_enqueue_script('toast', plugin_dir_url(__FILE__) . 'js/jquery.toast.min.js', array('jquery'), NULL, false);
  //         wp_enqueue_script('reviews', plugin_dir_url(__FILE__) . 'js/reviews.js', array('jquery'), NULL, false);
  //         wp_enqueue_style('toastCss', plugin_dir_url(__FILE__) . 'css/jquery.toast.min.css');
  //         wp_enqueue_style('custom', plugin_dir_url(__FILE__) . 'css/main.css');
  //     }
  // }
  // add_action('wp_enqueue_scripts', 'enqueue_single_product_reviews');
}


// add_filter( 'woocommerce_product_tabs', 'my_custom_description_tab', 98 ); 
// function my_custom_description_tab( $tabs ) { 

//  $tabs['reviews']['callback'] = 'my_custom_description_tab_content'; 
//  return $tabs; 
// } 

// function my_custom_description_tab_content() { 
//  echo '<h2>Custom Description</h2>'; 
//  echo '<p>Here\'s a custom description</p>'; 
// } 



// add_filter('woocommerce_reviews_title', 'misha_reviews_heading', 10, 3);
// function misha_reviews_heading($heading, $count, $product)
// {
//   return 'What customers think about this product';
// }



// add_filter('woocommerce_get_star_rating_html', 'replace_star_ratings', 10, 2);
// function replace_star_ratings($html, $rating) {
//     $html = ""; // Erase default HTML
//     for($i = 0; $i < 5; $i++) {
//         $html .= $i < $rating ? '<i class="fa fa-star"></i>' : '<i class="fa fa-star-o"></i>';
//     }
//     return $html;
// }





function insertReviewsIntoProductRM()
{
  $post_id = isset($_POST[base64_decode('cG9zdF9pZA==')]) ? sanitize_text_field($_POST[base64_decode('cG9zdF9pZA==')]) : '';
  $r = isset($_POST[base64_decode('cmV2aWV3cw==')]) ? ($_POST[base64_decode('cmV2aWV3cw==')]) : array();
  if (null != get_option('maxReachedNumber')) {
    $maxReachedNumber = (int) get_option('maxReachedNumber');
  } else {
    add_option('maxReachedNumber', count($r));
  }
  if (get_option('maxReachedNumber') < 400) {
    $insertedSuccessfully = array();
    if (isset($post_id) && isset($r) && count($r)) {
      foreach ($r as $review) {
        $comment_id = wp_insert_comment(array(base64_decode('Y29tbWVudF9wb3N0X0lE') => sanitize_text_field($post_id), base64_decode('Y29tbWVudF9hdXRob3I=') => sanitize_text_field($review[base64_decode('dXNlcm5hbWU=')]), base64_decode('Y29tbWVudF9hdXRob3JfZW1haWw=') => sanitize_text_field($review[base64_decode('ZW1haWw=')]), base64_decode('Y29tbWVudF9hdXRob3JfdXJs') => '', base64_decode('Y29tbWVudF9jb250ZW50') => $review[base64_decode('cmV2aWV3')], base64_decode('Y29tbWVudF90eXBl') => '', base64_decode('Y29tbWVudF9wYXJlbnQ=') => 0, base64_decode('dXNlcl9pZA==') => 5, base64_decode('Y29tbWVudF9hdXRob3JfSVA=') => '', base64_decode('Y29tbWVudF9hZ2VudA==') => '', base64_decode('Y29tbWVudF9kYXRl') => $review[base64_decode('ZGF0ZWNyZWF0aW9u')], base64_decode('Y29tbWVudF9hcHByb3ZlZA==') => 1,));
        $response = update_comment_meta($comment_id, base64_decode('cmF0aW5n'), sanitize_text_field($review[base64_decode('cmF0aW5n')]));
        if ($response != false && isset($response)) {
          array_push($insertedSuccessfully, $comment_id);
        }
      }
      if (is_numeric($maxReachedNumber)) {
        $newc = $maxReachedNumber + count($r);
        update_option('maxReachedNumber', $newc);
      }
      update_post_meta($post_id, '_wc_review_count', get_comments_number($post_id));
      wp_send_json(array(base64_decode('aW5zZXJ0ZWRTdWNjZXNzZnVsbHk=') => $insertedSuccessfully));
    }
  } else {
    wp_send_json(array('error_max_reached' => get_option('maxReachedNumber')));
  }
}
add_action(base64_decode('d3BfYWpheF9pbnNlcnQtcmV2aWV3cy10by1wcm9kdWN0Uk0='), base64_decode('aW5zZXJ0UmV2aWV3c0ludG9Qcm9kdWN0Uk0='));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfaW5zZXJ0LXJldmlld3MtdG8tcHJvZHVjUk10'), base64_decode('aW5zZXJ0UmV2aWV3c0ludG9Qcm9kdWN0Uk0='));
function getProduct_FROMWP_PLUGIN_REVIEWS()
{
  $paged = isset($_POST[base64_decode('cGFnZWQ=')]) ? sanitize_text_field($_POST[base64_decode('cGFnZWQ=')]) : '';
  $args = array(base64_decode('cG9zdF90eXBl') => base64_decode('cHJvZHVjdA=='), base64_decode('cG9zdHNfcGVyX3BhZ2U=') => 200, base64_decode('cGFnZWQ=') => $paged, base64_decode('cG9zdF9zdGF0dXM=') => array(base64_decode('cHVibGlzaA=='), base64_decode('ZHJhZnQ=')));
  $products = new WP_Query($args);
  $finalList = array();
  if ($products->have_posts()) {
    while ($products->have_posts()) : $products->the_post();
      $theid = get_the_ID();
      $product = new WC_Product($theid);
      if (has_post_thumbnail()) {
        $thumbnail = get_post_thumbnail_id();
        $image = $thumbnail ? wp_get_attachment_url($thumbnail) : '';
      }
      $finalList[] = array(base64_decode('c2t1') => $product->get_sku(), base64_decode('aWQ=') => $theid, base64_decode('aW1hZ2U=') => $image, base64_decode('dGl0bGU=') => $product->get_title(), base64_decode('cHJvZHVjdFVybA==') => get_post_meta($theid, base64_decode('cHJvZHVjdFVybA=='), true));
    endwhile;
  } else {
    echo __(base64_decode('Tm8gcHJvZHVjdHMgZm91bmQ='));
  }
  wp_reset_postdata();
  wp_send_json($finalList);
}
add_action(base64_decode('d3BfYWpheF9nZXRfcHJvZHVjdHNfcmV2aWV3cw=='), base64_decode('Z2V0UHJvZHVjdF9GUk9NV1BfUExVR0lOX1JFVklFV1M='));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfZ2V0X3Byb2R1Y3RzX3Jldmlld3M='), base64_decode('Z2V0UHJvZHVjdF9GUk9NV1BfUExVR0lOX1JFVklFV1M='));
function searchProductBySkuReviews()
{
  $searchSkuValue = isset($_POST[base64_decode('c2VhcmNoU2t1VmFsdWU=')]) ? sanitize_text_field($_POST[base64_decode('c2VhcmNoU2t1VmFsdWU=')]) : '';
  if (isset($searchSkuValue)) {
    $args = array(base64_decode('cG9zdF90eXBl') => base64_decode('cHJvZHVjdA=='), base64_decode('cG9zdHNfcGVyX3BhZ2U=') => 1, base64_decode('cA==') => $searchSkuValue);
    $products = new WP_Query($args);
    $finalList = array();
    if ($products->have_posts()) {
      while ($products->have_posts()) : $products->the_post();
        $theid = get_the_ID();
        $product = new WC_Product($theid);
        if (has_post_thumbnail()) {
          $thumbnail = get_post_thumbnail_id();
          $image = $thumbnail ? wp_get_attachment_url($thumbnail) : '';
        }
        $finalList[] = array(base64_decode('c2t1') => $product->get_sku(), base64_decode('aWQ=') => $theid, base64_decode('aW1hZ2U=') => $image, base64_decode('dGl0bGU=') => $product->get_title(), base64_decode('cHJvZHVjdFVybA==') => get_post_meta($theid, base64_decode('cHJvZHVjdFVybA=='), true));
      endwhile;
    } else {
      echo __(base64_decode('Tm8gcHJvZHVjdHMgZm91bmQ='));
    }
    wp_reset_postdata();
    wp_send_json($finalList);
  } else {
    $results = array(base64_decode('ZXJyb3I=') => true, base64_decode('ZXJyb3JfbXNn') => base64_decode('Y2Fubm90IGZpbmQgcmVzdWx0IGZvciB0aGUgaW50cm9kdWNlZCBza3UgdmFsdWUsIHBsZWFzZSBtYWtlIHN1cmUgdGhlIHByb2R1Y3QgaXMgaW1wb3J0ZWQgdXNpbmcgd29vc2hhcms='), base64_decode('ZGF0YQ==') => '');
    wp_send_json($results);
  }
}
add_action(base64_decode('d3BfYWpheF9zZWFyY2gtcHJvZHVjdC1ieS1za3UtcmV2aWV3cw=='), base64_decode('c2VhcmNoUHJvZHVjdEJ5U2t1UmV2aWV3cw=='));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfc2VhcmNoLXByb2R1Y3QtYnktc2t1LXJldmlld3M='), base64_decode('c2VhcmNoUHJvZHVjdEJ5U2t1UmV2aWV3cw=='));
function saveOptionsReviewsPlugin()
{
  $isHideReviewsDisplay = isset($_POST[base64_decode('aXNIaWRlUmV2aWV3c0Rpc3BsYXk=')]) ? sanitize_text_field($_POST[base64_decode('aXNIaWRlUmV2aWV3c0Rpc3BsYXk=')]) : '';
  if (isset($isHideReviewsDisplay)) {
    update_option(base64_decode('aXNIaWRlUmV2aWV3c0Rpc3BsYXk='), $isHideReviewsDisplay);
  }
  wp_send_json(array(base64_decode('Yg==') => get_option(base64_decode('aXNIaWRlUmV2aWV3c0Rpc3BsYXk='))));
}
add_action(base64_decode('d3BfYWpheF9zYXZlLW9wdGlvbnMtcmV2aWV3cy1wbHVnaW4='), base64_decode('c2F2ZU9wdGlvbnNSZXZpZXdzUGx1Z2lu'));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfc2F2ZS1vcHRpb25zLXJldmlld3MtcGx1Z2lu'), base64_decode('c2F2ZU9wdGlvbnNSZXZpZXdzUGx1Z2lu'));
add_action(base64_decode('d3BfYWpheF9nZXRQcm9kdWN0c0NvdW50UEx1Z2luUmV2aWV3cw=='), base64_decode('ZVg1NA=='));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfZ2V0UHJvZHVjdHNDb3VudFBMdWdpblJldmlld3M='), base64_decode('ZVg1NA=='));
function eX54()
{
  $args = array(base64_decode('cG9zdF90eXBl') => base64_decode('cHJvZHVjdA=='), base64_decode('cG9zdF9zdGF0dXM=') => array(base64_decode('cHVibGlzaA=='), base64_decode('ZHJhZnQ=')));
  $query = new WP_Query($args);
  $total = $query->found_posts;
  wp_reset_postdata();
  wp_send_json($total);
}
add_filter(base64_decode('d29vY29tbWVyY2VfcHJvZHVjdF90YWJz'), base64_decode('d29vX3JlbmFtZV90YWJz'), 98);
function woo_rename_tabs($tabs)
{
  global $product;
  if (get_comments_number($product->get_id()) == 1 && get_comments_number($product->get_id()) > 0) {
    $tabs[base64_decode('cmV2aWV3cw==')][base64_decode('dGl0bGU=')] = __(base64_decode('UmV2aWV3ICgg') . get_comments_number($product->get_id()) . base64_decode('ICk='));
  } else {
    $tabs[base64_decode('cmV2aWV3cw==')][base64_decode('dGl0bGU=')] = __(base64_decode('UmV2aWV3cyAoIA==') . get_comments_number($product->get_id()) . base64_decode('ICk='));
  }
  return $tabs;
}
add_filter(base64_decode('d29vY29tbWVyY2VfbG9jYXRlX3RlbXBsYXRl'), base64_decode('aW50ZXJjZXB0X3djX3RlbXBsYXRl'), 10, 3);
/**
 * Filter the cart template path to use cart.php in this plugin instead of the one in WooCommerce.
 *
 * @param string $template      Default template file path.
 * @param string $template_name Template file slug.
 * @param string $template_path Template file name.
 *
 * @return string The new Template file path.
 */ function intercept_wc_template($template, $template_name, $template_path)
{
  if (base64_decode('cmF0aW5nLnBocA==') === basename($template)) {
    $template = trailingslashit(plugin_dir_path(__FILE__)) . base64_decode('d29vY29tbWVyY2Uvc2luZ2xlLXByb2R1Y3QvcmF0aW5nLnBocA==');
  }
  return $template;
}


function addToLibrary()
{
  $reviewContent = isset($_POST[base64_decode('cmV2aWV3Q29udGVudA==')]) ? $_POST[base64_decode('cmV2aWV3Q29udGVudA==')] : '';
  global $wpdb;
  $ptbd_table_name = $wpdb->prefix . base64_decode('d29vc2hhcmtfcmV2aWV3c19saWI=');
  $query = base64_decode('Q1JFQVRFIFRBQkxFIElGIE5PVCBFWElTVFMgIA==') . $ptbd_table_name . base64_decode('ICgNCiAgICByZXZpZXdfaWQgSU5UKDExKSBBVVRPX0lOQ1JFTUVOVCwNCiAgICByZXZpZXdDb250ZW50IFRFWFQsDQogICAgdXNlcm5hbWUgVkFSQ0hBUigyNTUpLA0KICAgIGRhdGVDcmVhdGlvbiBWQVJDSEFSKDI1NSksDQogICAgcmV2aWV3RW1haWwgVkFSQ0hBUigyNTUpLA0KICAgIFBSSU1BUlkgS0VZKHJldmlld19pZCkNCiAgICAp');
  $wpdb->query($query);
  if (isset($reviewContent[base64_decode('cmV2aWV3Q29udGVudA==')]) && isset($reviewContent[base64_decode('dXNlcm5hbWU=')])) {
    $wpdb->insert($ptbd_table_name, array(base64_decode('cmV2aWV3Q29udGVudA==') => $reviewContent[base64_decode('cmV2aWV3Q29udGVudA==')], base64_decode('dXNlcm5hbWU=') => sanitize_text_field($reviewContent[base64_decode('dXNlcm5hbWU=')]), base64_decode('ZGF0ZUNyZWF0aW9u') => sanitize_text_field($reviewContent[base64_decode('ZGF0ZUNyZWF0aW9u')]), base64_decode('cmV2aWV3RW1haWw=') => sanitize_text_field($reviewContent[base64_decode('cmV2aWV3RW1haWw=')])));
  }
  $rows = $wpdb->get_results("SELECT * FROM $ptbd_table_name ");
  wp_send_json(array(base64_decode('cmVzdWx0') => $rows));
}
add_action(base64_decode('d3BfYWpheF9hZGQtcmV2aWV3LXRvLWxpYnJhcnk='), base64_decode('YWRkVG9MaWJyYXJ5'));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfYWRkLXJldmlldy10by1saWJyYXJ5'), base64_decode('YWRkVG9MaWJyYXJ5'));
function loadReviewsLibrary()
{
  global $wpdb;
  $ptbd_table_name = $wpdb->prefix . base64_decode('d29vc2hhcmtfcmV2aWV3c19saWI=');
  $rows = $wpdb->get_results("SELECT * FROM $ptbd_table_name ");
  wp_send_json(array(base64_decode('cmV2aWV3c0xpYnJhcnk=') => $rows));
}
add_action(base64_decode('d3BfYWpheF9sb2FkLXJldmlld3MtbGlicmFyeQ=='), base64_decode('bG9hZFJldmlld3NMaWJyYXJ5'));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfbG9hZC1yZXZpZXdzLWxpYnJhcnk='), base64_decode('bG9hZFJldmlld3NMaWJyYXJ5'));
function updateFinalREviewLibrary()
{
  $finalReviews = isset($_POST[base64_decode('ZmluYWxSZXZpZXdz')]) ? $_POST[base64_decode('ZmluYWxSZXZpZXdz')] : array();
  if (isset($finalReviews) && count($finalReviews)) {
    update_option(base64_decode('cmV2aWV3TGlicmFyeQ=='), $finalReviews);
    wp_send_json(array(base64_decode('cmVzdWx0') => $finalReviews));
  }
}
add_action(base64_decode('d3BfYWpheF91cGRhdGUtcmV2aWV3cy10by1saWJyYXJ5'), base64_decode('dXBkYXRlRmluYWxSRXZpZXdMaWJyYXJ5'));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfdXBkYXRlLXJldmlld3MtdG8tbGlicmFyeQ=='), base64_decode('dXBkYXRlRmluYWxSRXZpZXdMaWJyYXJ5'));
function removeReviFromLibrary()
{
  $review_id = isset($_POST[base64_decode('cmV2aWV3X2lk')]) ? sanitize_text_field($_POST[base64_decode('cmV2aWV3X2lk')]) : '';
  if (isset($review_id)) {
    global $wpdb;
    $ptbd_table_name = $wpdb->prefix . base64_decode('d29vc2hhcmtfcmV2aWV3c19saWI=');
    $result = $wpdb->delete($ptbd_table_name, array(base64_decode('cmV2aWV3X2lk') => $review_id));
    wp_send_json(array(base64_decode('cmVzdWx0') => $result));
  }
}
add_action(base64_decode('d3BfYWpheF9yZW1vdmUtcmV2aWV3LXRvLWxpYnJhcnk='), base64_decode('cmVtb3ZlUmV2aUZyb21MaWJyYXJ5'));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfcmVtb3ZlLXJldmlldy10by1saWJyYXJ5'), base64_decode('cmVtb3ZlUmV2aUZyb21MaWJyYXJ5'));
function searchReviFromLibrary()
{
  $reviewKeyword = isset($_POST[base64_decode('cmV2aWV3S2V5d29yZA==')]) ? sanitize_text_field($_POST[base64_decode('cmV2aWV3S2V5d29yZA==')]) : '';
  if (isset($reviewKeyword)) {
    global $wpdb;
    $ptbd_table_name = $wpdb->prefix . base64_decode('d29vc2hhcmtfcmV2aWV3c19saWI=');
    $holder = base64_decode('JXM=');
    $key = $_GET[base64_decode('a2V5')];
    $array = array();
    $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $ptbd_table_name WHERE reviewContent LIKE '%%$reviewKeyword%%'"));
    wp_send_json(array(base64_decode('cmVzdWx0') => $result));
  }
}
add_action(base64_decode('d3BfYWpheF9zZWFyY2gtcmV2aWV3LXRvLWxpYnJhcnk='), base64_decode('c2VhcmNoUmV2aUZyb21MaWJyYXJ5'));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfc2VhcmNoLXJldmlldy10by1saWJyYXJ5'), base64_decode('c2VhcmNoUmV2aUZyb21MaWJyYXJ5'));
function checkMaximumReached()
{
  if (null != get_option(base64_decode('ZmVzc3VveQ==') && (int) get_option(base64_decode('ZmVzc3VveQ==') > 500))) {
    wp_send_json(array(base64_decode('cmVzdWx0') => base64_decode('aXNSZWFjaGVk')));
  } else {
    wp_send_json(array(base64_decode('cmVzdWx0') => base64_decode('aXNGcmVl')));
  }
}
add_action(base64_decode('d3BfYWpheF9jaGVjay1tYXhpbXVtLXJlYWNoZWQ='), base64_decode('Y2hlY2tNYXhpbXVtUmVhY2hlZA=='));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfY2hlY2stbWF4aW11bS1yZWFjaGVk'), base64_decode('Y2hlY2tNYXhpbXVtUmVhY2hlZA=='));
